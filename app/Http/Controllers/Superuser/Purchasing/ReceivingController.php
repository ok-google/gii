<?php

namespace App\Http\Controllers\Superuser\Purchasing;

use App\DataTables\Purchasing\ReceivingTable;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\Hpp;
use App\Entities\Master\Company;
use App\Entities\Purchasing\Receiving;
use App\Entities\Purchasing\ReceivingDetail;
use App\Entities\Purchasing\ReceivingDetailColly;
use App\Entities\Purchasing\PurchaseOrder;
use App\Entities\Purchasing\PurchaseOrderDetail;
use App\Entities\Finance\SettingFinance;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DomPDF;
use Validator;
use DB;

class ReceivingController extends Controller
{
    public function json(Request $request, ReceivingTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('receiving-manage')) {
            return abort(403);
        }

        return view('superuser.purchasing.receiving.index');
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('receiving-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(1);

        return view('superuser.purchasing.receiving.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:receiving,code',
                'warehouse' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                $receiving = new Receiving;

                $receiving->code = $request->code;
                $receiving->warehouse_id = $request->warehouse;
                $receiving->pbm_date = $request->pbm_date;
                $receiving->no_container = $request->no_container;
                $receiving->description = $request->note;

                $receiving->status = Receiving::STATUS['ACTIVE'];

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.purchasing.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-edit')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::find($id);

        return view('superuser.purchasing.receiving.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $receiving = Receiving::find($id);

            if ($receiving == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:receiving,code,' . $receiving->id,
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                $receiving->code = $request->code;
                $receiving->pbm_date = $request->pbm_date;
                $receiving->no_container = $request->no_container;
                $receiving->description = $request->note;

                if ($receiving->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.purchasing.receiving.step', ['id' => $receiving->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function step($id)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-edit')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.purchasing.receiving.step', $data);
    }

    public function publish(Request $request, $id)
    {
        return $this->save_acc($request, $id, 'publish');
    }

    public function acc(Request $request, $id)
    {
        return $this->save_acc($request, $id, 'acc');
    }

    private function save_acc(Request $request, $id, $button_type)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('receiving-acc')) {
                return abort(403);
            }

            $receiving = Receiving::find($id);

            if ($receiving == null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $superuser = Auth::guard('superuser')->user();
                $collect = [];

                $setting_tax = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_tax')->first();

                if ($setting_tax == null or $setting_tax->coa_id == null) {
                    $response['failed'] = 'Tax Setting is not set, please contact your Administrator!';
                    return $this->response(200, $response);
                } else {
                    $total_persediaan = 0;
                    $total_tax = 0;
                    foreach ($receiving->details as $detail) {
                        $harga_satuan = $detail->ppb_detail->total_price_idr / $detail->ppb_detail->quantity;
                        $reject_idr = 0;
                        if ($detail->total_reject_ri($detail->id)) {
                            $reject_idr = $detail->total_reject_ri($detail->id) * $harga_satuan;

                            if (!empty($collect[$detail->ppb_id])) {
                                $collect[$detail->ppb_id] += $reject_idr;
                            } else {
                                $collect[$detail->ppb_id] = $reject_idr;
                            }
                        }

                        $delivery_cost = 0;

                        if($detail->total_quantity_ri > 0) {
                            $delivery_cost = $detail->delivery_cost / $detail->total_quantity_ri;

                            $hpp                = new Hpp;
                            $hpp->type          = $superuser->type;
                            $hpp->branch_office_id = $superuser->branch_office_id;
                            $hpp->product_id    = $detail->product_id;
                            $hpp->quantity      = $detail->total_quantity_ri;
                            $hpp->price         = $harga_satuan + $delivery_cost;
                            $hpp->save();
                        }

                        // HANDLE RECEIVING COA
                        $qty_receive = ReceivingDetailColly::where('receiving_detail_id', $detail->id)->sum('quantity_ri');
                        $total_persediaan_item = $reject_idr + ($detail->total_quantity_ri * ($harga_satuan + $delivery_cost));
                        $total_persediaan = $total_persediaan + $total_persediaan_item;

                        // SUM TAX
                        $tax_satuan = 0;
                        if ($detail->ppb_detail->total_tax > 0) {
                            $tax_satuan = $detail->ppb_detail->total_tax / $detail->ppb_detail->quantity;
                        }
                        $total_tax_item = $qty_receive * $tax_satuan;
                        $total_tax = $total_tax + $total_tax_item;
                    }

                    $setting_receiving_acc = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_debet')->first();

                    $setting_finance = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'ppb_tunai_debet')->first();

                    $setting_finance_cost = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'receiving_cost')->first();

                    // ADD JOURNAL
                    $journal = new Journal;
                    $journal->coa_id = $setting_receiving_acc->coa_id;
                    $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                    $journal->debet = $total_persediaan - $total_tax;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->save();

                    $journal = new Journal;
                    $journal->coa_id = $setting_finance->coa_id;
                    $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                    $journal->credit = ($total_persediaan - ($delivery_cost * $qty_receive));
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->save();

                    $journal = new Journal;
                    $journal->coa_id = $setting_finance_cost->coa_id;
                    $journal->name = Journal::PREJOURNAL['RI_ACC'] . $receiving->code;
                    $journal->credit = $delivery_cost * $qty_receive;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->save();

                    if ($total_tax > 0) {
                        $journal = new Journal;
                        $journal->coa_id = $setting_tax->coa_id;
                        $journal->name = Journal::PREJOURNAL['RI_TAX'] . $receiving->code;
                        $journal->debet = $total_tax;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    }

                    if ($collect) {
                        foreach ($collect as $key => $value) {
                            $ppb = PurchaseOrder::find($key);

                            // ADD JOURNAL
                            $journal = new Journal;
                            $journal->coa_id = $ppb->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_REJECT'] . $ppb->code;
                            $journal->debet = $value;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();

                            $journal = new Journal;
                            $journal->coa_id = $setting_finance->coa_id;
                            $journal->name = Journal::PREJOURNAL['RI_REJECT'] . $ppb->code;
                            $journal->credit = $value;
                            $journal->status = Journal::STATUS['UNPOST'];
                            $journal->save();
                        }
                    }

                    $receiving->acc_by = Auth::guard('superuser')->id();
                    $receiving->acc_at = Carbon::now()->toDateTimeString();
                    $receiving->status = Receiving::STATUS['ACC'];

                    if ($receiving->save()) {
                        DB::commit();

                        if ($button_type == 'publish') {
                            $response['redirect_to'] = route('superuser.purchasing.receiving.index');
                        } else {
                            $response['redirect_to'] = '#datatable';
                        }

                        return $this->response(200, $response);
                    }
                }
            } catch (\Exception $e) {
                DB::rollback();

                $response['failed'] = 'Internal Server Error!';

                return $this->response(200, $response);
            }
        }
    }

    public function show($id)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-show')) {
            return abort(403);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.purchasing.receiving.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['receiving'] = Receiving::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.purchasing.receiving.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function print_barcode($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('receiving-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['receiving'] = Receiving::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.purchasing.receiving.print_barcode', $data);

        // 50mm x 70mm
        $customPaper = array(0, 0, 198.10, 141.50);
        $pdf->setPaper($customPaper, 'portrait');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('receiving-delete')) {
                return abort(403);
            }

            $receiving = Receiving::find($id);

            if ($receiving === null) {
                abort(404);
            }

            $receiving->status = Receiving::STATUS['DELETED'];

            if ($receiving->save()) {

                $response['redirect_to'] = route('superuser.purchasing.receiving.index');
                return $this->response(200, $response);
            }
        }
    }
}