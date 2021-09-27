<?php

namespace App\Http\Controllers\Superuser\Sale;

use App\DataTables\Sale\SaleReturnTable;
use App\Entities\Accounting\Hpp;
use App\Entities\Accounting\Journal;
use App\Entities\Finance\SettingFinance;
use App\Entities\Master\Product;
use App\Entities\Sale\DeliveryOrderDetail;
use App\Entities\Sale\SaleReturn;
use App\Entities\Sale\SaleReturnDetail;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DomPDF;

class SaleReturnController extends Controller
{
    public function json(Request $request, SaleReturnTable $datatable)
    {
        return $datatable->build();
    }

    public function search_do(Request $request)
    {
        $delivery_orders = DeliveryOrderDetail::where('created_at', '>=', Carbon::now()->subDays(90)->toDateTimeString())
            ->where(function ($query) use ($request) {
                $query->where('code', 'LIKE', $request->input('q', '') . '%')
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('sales_order', function ($query) use ($request) {
                            $query->where('code', 'LIKE', $request->input('q', '') . '%')
                                ->orWhere('resi', 'LIKE', $request->input('q', '') . '%')
                                ->orWhere('store_name', 'LIKE', $request->input('q', '') . '%');
                        });
                    });
            })
            ->whereHas('sales_order', function ($query) use ($request) {
                $query->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
            })
            ->whereHas('delivery_order', function ($query2) {
                $query2->where('status', 2);
            })
            ->get();

        $results = [];

        foreach ($delivery_orders as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->code . ' / ' . $item->sales_order->code . ' / ' . $item->sales_order->resi . ' / ' . $item->sales_order->store_name,
            ];
        }

        return ['results' => $results];
    }

    public function get_product(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $delivery_order_detail = DeliveryOrderDetail::find($request->id);

            foreach ($delivery_order_detail->sales_order->sales_order_details as $key => $value) {
                $data[] = [
                    'id' => $value->product_id,
                    'sku' => $value->product->code,
                    'name' => $value->product->name,
                    'quantity' => $value->quantity,
                    'hpp' => $value->hpp_total ? $value->hpp_total / $value->quantity : '',
                    'price' => $value->price,
                ];
            }

            return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('sale return-manage')) {
            return abort(403);
        }

        return view('superuser.sale.sale_return.index');
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('sale return-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(3);

        return view('superuser.sale.sale_return.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:sale_return,code',
                'delivery_order' => 'required',
                'warehouse_reparation' => 'required|integer',
                'return_date' => 'nullable',
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
                $sale_return = new SaleReturn;

                $sale_return->code = $request->code;
                $sale_return->delivery_order_id = $request->delivery_order;
                $sale_return->warehouse_reparation_id = $request->warehouse_reparation;
                $sale_return->return_date = $request->return_date;
                $sale_return->status = SaleReturn::STATUS['ACTIVE'];

                if ($sale_return->save()) {
                    if ($request->sku) {
                        foreach ($request->sku as $key => $value) {
                            if ($request->sku[$key] && $request->quantity[$key]) {
                                $sale_return_detail = new SaleReturnDetail;
                                $sale_return_detail->sale_return_id = $sale_return->id;
                                $sale_return_detail->product_id = $request->sku[$key];
                                $sale_return_detail->quantity = $request->quantity[$key];
                                $sale_return_detail->hpp = $request->hpp[$key];
                                $sale_return_detail->price = $request->price[$key];
                                $sale_return_detail->description = $request->description[$key];
                                $sale_return_detail->save();
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.sale.sale_return.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if (!Auth::guard('superuser')->user()->can('sale return-show')) {
            return abort(403);
        }

        $data['sale_return'] = SaleReturn::findOrFail($id);

        return view('superuser.sale.sale_return.show', $data);
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('sale return-acc')) {
                return abort(403);
            }

            $sale_return = SaleReturn::find($id);

            if ($sale_return === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $failed = '';
                $superuser = Auth::guard('superuser')->user();

                $return_transaction_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_transaction_debet')->first();

                $return_transaction_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_transaction_credit')->first();

                $return_hpp_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_hpp_debet')->first();

                $return_hpp_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'return_hpp_credit')->first();

                if ($return_transaction_debet == null or $return_transaction_debet->coa_id == null or $return_transaction_credit == null or $return_transaction_credit->coa_id == null or $return_hpp_debet == null or $return_hpp_debet->coa_id == null or $return_hpp_credit == null or $return_hpp_credit->coa_id == null) {
                    $failed = 'Finance Setting is not set, please contact your Administrator!';
                } else {
                    $empty_hpp = false;
                    $price_total = 0;
                    $hpp_total = 0;
                    foreach ($sale_return->sale_return_details as $detail) {
                        if($detail->product->non_stock == '1') {
                            continue;
                        }

                        if ($detail->hpp == null) {
                            $empty_hpp = true;
                            break;
                        }

                        $hpp = new Hpp;
                        $hpp->type = $superuser->type;
                        $hpp->branch_office_id = $superuser->branch_office_id;
                        $hpp->product_id = $detail->product_id;
                        $hpp->quantity = $detail->quantity;
                        $hpp->price = $detail->hpp;
                        $hpp->save();

                        $price_total = $price_total + ($detail->quantity * $detail->price);
                        $hpp_total = $hpp_total + ($detail->quantity * $detail->hpp);
                    }

                    if ($empty_hpp) {
                        $failed = 'HPP Reference invalid!';
                        DB::rollback();
                    } else {
                        // ADD JOURNAL
                        // TRANSACTION
                        $journal = new Journal;
                        $journal->coa_id = $return_transaction_debet->coa_id;
                        $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                        $journal->debet = $price_total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $return_transaction_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                        $journal->credit = $price_total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        // HPP
                        $journal = new Journal;
                        $journal->coa_id = $return_hpp_debet->coa_id;
                        $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                        $journal->debet = $hpp_total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $return_hpp_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['SALE_RETURN_ACC'] . $sale_return->delivery_order->sales_order->code;
                        $journal->credit = $hpp_total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;
                    return $this->response(200, $response);
                }

                $sale_return->status = SaleReturn::STATUS['ACC'];
                if ($sale_return->save()) {
                    DB::commit();
                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response['redirect_to'] = '#datatable';
                return $this->response(400, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('sale return-delete')) {
                return abort(403);
            }

            $sale_return = SaleReturn::find($id);

            if ($sale_return === null) {
                abort(404);
            }

            $sale_return->status = SaleReturn::STATUS['DELETED'];

            if ($sale_return->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('sale return-manage')) {
            return abort(403);
        }

        // if (is_string($data)) {
        //     $data = json_decode($data);
        // }

        if ($id == NULL) {
            abort(404);
        }

        $data['data'] = SaleReturn::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.sale.sale_return.pdf', $data);
        $pdf->setPaper('a5', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }
}
