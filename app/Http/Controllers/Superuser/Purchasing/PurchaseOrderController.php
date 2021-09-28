<?php

namespace App\Http\Controllers\Superuser\Purchasing;

use App\DataTables\Purchasing\PurchaseOrderTable;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Master\Company;
use App\Entities\Master\SupplierCoa;
use App\Entities\Purchasing\PurchaseOrder;
use App\Entities\Purchasing\PurchaseOrderDetail;
use App\Entities\Finance\SettingFinance;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use App\Exports\Purchasing\PurchaseOrderDetailImportTemplate;
use App\Imports\Purchasing\PurchaseOrderDetailImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DomPDF;
use Validator;
use Excel;


class PurchaseOrderController extends Controller
{
    public function json(Request $request, PurchaseOrderTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-manage')) {
            return abort(403);
        }

        return view('superuser.purchasing.purchase_order.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-create')) {
            return abort(403);
        }

        $data['suppliers'] = MasterRepo::suppliers();
        $data['warehouses'] = MasterRepo::warehouses_by_category(1);
        $data['ekspedisis'] = MasterRepo::ekspedisis();
        $data['coas'] = MasterRepo::coas_by_branch_and_group(COA::GROUP['Aktiva']);

        return view('superuser.purchasing.purchase_order.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:ppb,code',
                'supplier' => 'required|integer',
                'address' => 'nullable|string',
                'warehouse' => 'required|integer',
                'transaction_type' => 'required|integer',
                'coa' => Rule::requiredIf(function () use ($request) {
                    return $request->transaction_type == PurchaseOrder::TRANSACTION_TYPE['Tunai'];
                }),
                'kurs' => 'nullable|integer',
                'tax' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
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
                $purchase_order = new PurchaseOrder;

                $purchase_order->code = $request->code;
                $purchase_order->supplier_id = $request->supplier;
                $purchase_order->address = $request->address;
                $purchase_order->warehouse_id = $request->warehouse;
                $purchase_order->transaction_type = $request->transaction_type;
                $purchase_order->coa_id = ($purchase_order->transaction_type == PurchaseOrder::TRANSACTION_TYPE['Non Tunai']) ? null : $request->coa;
                $purchase_order->kurs = $request->kurs;
                $purchase_order->tax = $request->tax ?? '0';

                $purchase_order->status = PurchaseOrder::STATUS['DRAFT'];

                if ($purchase_order->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.purchasing.purchase_order.step', ['id' => $purchase_order->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-edit')) {
            return abort(403);
        }

        $data['purchase_order'] = PurchaseOrder::find($id);
        $data['suppliers'] = MasterRepo::suppliers();
        $data['warehouses'] = MasterRepo::warehouses_by_category(1);
        $data['ekspedisis'] = MasterRepo::ekspedisis();
        $data['coas'] = MasterRepo::coas_by_branch_and_group(COA::GROUP['Aktiva']);
        
        return view('superuser.purchasing.purchase_order.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:ppb,code,' . $purchase_order->id,
                'supplier' => 'required|integer',
                'address' => 'nullable|string',
                'warehouse' => 'required|integer',
                'transaction_type' => 'required|integer',
                'coa' => Rule::requiredIf(function () use ($request) {
                    return $request->transaction_type == PurchaseOrder::TRANSACTION_TYPE['Tunai'];
                }),
                'kurs' => 'nullable|integer',
                'tax' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
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
                $purchase_order->code = $request->code;
                $purchase_order->supplier_id = $request->supplier;
                $purchase_order->address = $request->address;
                $purchase_order->warehouse_id = $request->warehouse;
                $purchase_order->transaction_type = $request->transaction_type;
                $purchase_order->coa_id = ($purchase_order->transaction_type == PurchaseOrder::TRANSACTION_TYPE['Non Tunai']) ? null : $request->coa;
                $purchase_order->kurs = $request->kurs;
                $purchase_order->tax = $request->tax ?? '0';

                if ($purchase_order->save()) {
                    // UPDATE TAX IN DETAIL
                    foreach ($purchase_order->details as $detail) {
                        $purchase_order_detail = PurchaseOrderDetail::find($detail->id);

                        // SET TAX
                        $total_price_before_tax = ((($request->quantity * $request->unit_price) + $request->local_freight_cost + $request->komisi) * $request->kurs );
                        $tax = 0;
                        if($purchase_order->tax > 0) {
                            $tax = $total_price_before_tax * $purchase_order->tax / 100;
                        }
                        $total_price_after_tax = $total_price_before_tax + $tax;

                        $purchase_order_detail->total_tax = $tax;
                        $purchase_order_detail->total_price_idr = $total_price_after_tax;
                        $purchase_order_detail->save();
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.purchasing.purchase_order.step', ['id' => $purchase_order->id]);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function step($id)
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-edit')) {
            return abort(403);
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);

        if($data['purchase_order']->status == PurchaseOrder::STATUS['ACC'] OR $data['purchase_order']->status == PurchaseOrder::STATUS['DELETED']) {
            return abort(404);
        }

        return view('superuser.purchasing.purchase_order.step', $data);
    }

    public function publish(Request $request, $id)
    {
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order == null) {
                abort(404);
            }

            $purchase_order->grand_total_rmb = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_rmb');
            $purchase_order->grand_total_idr = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_idr');
            $purchase_order->status = PurchaseOrder::STATUS['ACTIVE'];

            if ($purchase_order->save()) {
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.purchasing.purchase_order.index');

                return $this->response(200, $response);
            }
        }
    }

    public function save_modify(Request $request, $id, $save_type)
    {
        if ($request->ajax()) {
            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order == null) {
                abort(404);
            }

            $purchase_order->grand_total_rmb = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_rmb');
            $purchase_order->grand_total_idr = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_idr');
            
            if($save_type == 'save') {
                $purchase_order->edit_counter += 1;
            } else {
                $purchase_order->acc_by = Auth::guard('superuser')->id();
                $purchase_order->acc_at = Carbon::now()->toDateTimeString();

                $failed = '';
                $superuser = Auth::guard('superuser')->user();
                if($purchase_order->transaction_type == PurchaseOrder::TRANSACTION_TYPE['Tunai']) {
                    $setting_finance = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'ppb_tunai_debet')->first();
                    if($setting_finance == null OR $setting_finance->coa_id == null) {
                        $failed = 'Finance Setting is not set, please contact your Administrator!';
                    } else {
                        // ADD JOURNAL
                        $journal = new Journal;
                        $journal->coa_id = $setting_finance->coa_id;
                        $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                        $journal->debet = $purchase_order->grand_total_idr;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $purchase_order->coa_id;
                        $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                        $journal->credit = $purchase_order->grand_total_idr;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    }
                } else {
                    $setting_finance = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'ppb_non_tunai_debet')->first();

                    if($setting_finance == null OR $setting_finance->coa_id == null) {
                        $failed = 'Finance Setting is not set, please contact your Administrator!';
                    }

                    $supplier_coa = SupplierCoa::where('supplier_id', $purchase_order->supplier_id)
                                    ->where('type', $superuser->type)
                                    ->where('branch_office_id', $superuser->branch_office_id)
                                    ->first();
                    if($supplier_coa == null OR $supplier_coa->coa_id == null) {
                        $failed = 'Supplier Setting is not set, please contact your Administrator!';
                    }

                    if(!$failed) {
                        // ADD JOURNAL
                        $journal = new Journal;
                        $journal->coa_id = $setting_finance->coa_id;
                        $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                        $journal->debet = $purchase_order->grand_total_idr;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $supplier_coa->coa_id;
                        $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                        $journal->credit = $purchase_order->grand_total_idr;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    } 
                }

                if($failed) {
                    $response['failed'] = $failed;
                    return $this->response(200, $response);
                }
            }
            
            $purchase_order->status = $save_type == 'save' ? PurchaseOrder::STATUS['ACTIVE'] : PurchaseOrder::STATUS['ACC'];

            if ($purchase_order->save()) {
                $response['redirect_to'] = route('superuser.purchasing.purchase_order.index');
                return $this->response(200, $response);
            }
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('purchase order-acc')) {
                return abort(403);
            }

            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            $purchase_order->acc_by = Auth::guard('superuser')->id();
            $purchase_order->acc_at = Carbon::now()->toDateTimeString();
            $purchase_order->status = PurchaseOrder::STATUS['ACC'];

            $failed = '';
            $superuser = Auth::guard('superuser')->user();
            if($purchase_order->transaction_type == PurchaseOrder::TRANSACTION_TYPE['Tunai']) {
                $setting_finance = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'ppb_tunai_debet')->first();
                if($setting_finance == null OR $setting_finance->coa_id == null) {
                    $failed = 'Finance Setting is not set, please contact your Administrator!';
                } else {
                    // ADD JOURNAL
                    $journal = new Journal;
                    $journal->coa_id = $setting_finance->coa_id;
                    $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                    $journal->debet = $purchase_order->grand_total_idr;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->save();

                    $journal = new Journal;
                    $journal->coa_id = $purchase_order->coa_id;
                    $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                    $journal->credit = $purchase_order->grand_total_idr;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->save();
                }
            } else {
                $setting_finance = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'ppb_non_tunai_debet')->first();

                if($setting_finance == null OR $setting_finance->coa_id == null) {
                    $failed = 'Finance Setting is not set, please contact your Administrator!';
                }

                $supplier_coa = SupplierCoa::where('supplier_id', $purchase_order->supplier_id)
                                ->where('type', $superuser->type)
                                ->where('branch_office_id', $superuser->branch_office_id)
                                ->first();
                if($supplier_coa == null OR $supplier_coa->coa_id == null) {
                    $failed = 'Supplier Setting is not set, please contact your Administrator!';
                }

                if(!$failed) {
                    // ADD JOURNAL
                    $journal = new Journal;
                    $journal->coa_id = $setting_finance->coa_id;
                    $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                    $journal->debet = $purchase_order->grand_total_idr;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->save();

                    $journal = new Journal;
                    $journal->coa_id = $supplier_coa->coa_id;
                    $journal->name = Journal::PREJOURNAL['PPB_ACC'].' '.$purchase_order->code;
                    $journal->credit = $purchase_order->grand_total_idr;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->save();
                } 
            }

            if($failed) {
                $response['failed'] = $failed;
                return $this->response(200, $response);
            }

            if ($purchase_order->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-show')) {
            return abort(403);
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);

        return view('superuser.purchasing.purchase_order.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-print')) {
            return abort(403);
        }

        // if (is_string($data)) {
        //     $data = json_decode($data);
        // }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['purchase_order'] = PurchaseOrder::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.purchasing.purchase_order.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

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
            if(!Auth::guard('superuser')->user()->can('purchase order-delete')) {
                return abort(403);
            }

            $purchase_order = PurchaseOrder::find($id);

            if ($purchase_order === null) {
                abort(404);
            }

            $purchase_order->status = PurchaseOrder::STATUS['DELETED'];

            if ($purchase_order->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'purchase-order-detail-import-template.xlsx';
        return Excel::download(new PurchaseOrderDetailImportTemplate, $filename);
    }

    public function import(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new PurchaseOrderDetailImport($id);
            Excel::import($import, $request->import_file);
            
            if($import->error) {
                return redirect()->back()->withErrors($import->error);
            }
            
            return redirect()->back()->with(['message' => 'Import success']);
        }
    }
}
