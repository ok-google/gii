<?php

namespace App\Http\Controllers\Superuser\Sale;

use App\DataTables\Sale\BuyBackTable;
use App\Entities\Accounting\Hpp;
use App\Entities\Accounting\Journal;
use App\Entities\Finance\SettingFinance;
use App\Entities\Sale\BuyBack;
use App\Entities\Sale\BuyBackDetail;
use App\Entities\Sale\SalesOrder;
use App\Entities\Master\CustomerCoaPenjualan;
use App\Entities\Sale\StockSalesOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DomPDF;

class BuyBackController extends Controller
{
    public function json(Request $request, BuyBackTable $datatable)
    {
        return $datatable->build();
    }

    public function search_so(Request $request)
    {
        $sales_order = SalesOrder::where('code', 'LIKE', $request->input('q', '') . '%')
            ->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->where('status', SalesOrder::STATUS['ACC'])
            ->where('created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString())
            ->get();

        $results = [];

        foreach ($sales_order as $item) {
            if ($item->grand_total - $item->total_paid() - $item->total_cost() <= 0) {
                $results[] = [
                    'id' => $item->id,
                    'text'  => $item->code,
                ];
            }
        }

        return ['results' => $results];
    }

    public function get_sku(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $sales_order = SalesOrder::find($request->id);

            foreach ($sales_order->sales_order_details as $item) {
                $data[] = [
                    'sales_order_detail_id' => $item->id,
                    'sku' => $item->product->code,
                    'product_name' => $item->product->name,
                    'sell_price' => $item->price,
                    'quantity' => $item->quantity,
                ];
            }

            return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('buy back-manage')) {
            return abort(403);
        }

        return view('superuser.sale.buy_back.index');
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('buy back-create')) {
            return abort(403);
        }

        $data['warehouses_id'] = MasterRepo::warehouses_by_branch();

        return view('superuser.sale.buy_back.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:buy_back,code',
                'sales_order' => 'required|integer',
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

            if (!$request->col_so_detail) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Please select Product at least 1 item.',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $superuser = Auth::guard('superuser')->user();

                    $buy_back = new BuyBack;

                    $buy_back->code = $request->code;
                    $buy_back->type = $superuser->type;
                    $buy_back->branch_office_id = $superuser->branch_office_id;
                    $buy_back->sales_order_id = $request->sales_order;
                    $buy_back->warehouse_id = $request->warehouse;

                    if ($request->disposal) {
                        $buy_back->disposal = 1;
                    }

                    $buy_back->status = BuyBack::STATUS['ACTIVE'];

                    if ($buy_back->save()) {

                        if ($request->col_so_detail) {
                            foreach ($request->col_so_detail as $key => $value) {
                                if ($request->col_so_detail[$key]) {
                                    $buy_back_detail = new BuyBackDetail;
                                    $buy_back_detail->buy_back_id = $buy_back->id;
                                    $buy_back_detail->sales_order_detail_id = $request->col_so_detail[$key];
                                    $buy_back_detail->buy_back_price = $request->col_buy_back_price[$key];
                                    $buy_back_detail->buy_back_qty = $request->col_buy_back_qty[$key];
                                    $buy_back_detail->buy_back_total = $request->col_buy_back_total[$key];
                                    $buy_back_detail->save();
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.sale.buy_back.index');

                        return $this->response(200, $response);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Internal Server Error!",
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if (!Auth::guard('superuser')->user()->can('buy back-show')) {
            return abort(403);
        }

        $data['buy_back'] = BuyBack::find($id);

        return view('superuser.sale.buy_back.show', $data);
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('buy back-edit')) {
            return abort(403);
        }

        $data['buy_back'] = BuyBack::find($id);

        $data['warehouses_display'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.sale.buy_back.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $buy_back = BuyBack::find($id);

            if ($buy_back == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:buy_back,code,' . $buy_back->id,
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

            if (!$request->col_so_detail) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Please select Product at least 1 item.',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $buy_back->code = $request->code;
                    $buy_back->warehouse_id = $request->warehouse;

                    if ($request->disposal) {
                        $buy_back->disposal = 1;
                    } else {
                        $buy_back->disposal = 0;
                    }

                    if ($buy_back->save()) {
                        if ($request->ids_delete) {
                            $pieces = explode(",", $request->ids_delete);
                            foreach ($pieces as $piece) {
                                BuyBackDetail::where('id', $piece)->delete();
                            }
                        }

                        if ($request->col_so_detail) {
                            foreach ($request->col_so_detail as $key => $value) {
                                if ($request->col_so_detail[$key]) {
                                    if ($request->edit[$key]) {
                                        $buy_back_detail = BuyBackDetail::find($request->edit[$key]);

                                        $buy_back_detail->buy_back_price = $request->col_buy_back_price[$key];
                                        $buy_back_detail->buy_back_qty = $request->col_buy_back_qty[$key];
                                        $buy_back_detail->buy_back_total = $request->col_buy_back_total[$key];

                                        $buy_back_detail->save();
                                    } else {
                                        $buy_back_detail = new BuyBackDetail;

                                        $buy_back_detail->buy_back_id = $buy_back->id;
                                        $buy_back_detail->sales_order_detail_id = $request->col_so_detail[$key];
                                        $buy_back_detail->buy_back_price = $request->col_buy_back_price[$key];
                                        $buy_back_detail->buy_back_qty = $request->col_buy_back_qty[$key];
                                        $buy_back_detail->buy_back_total = $request->col_buy_back_total[$key];

                                        $buy_back_detail->save();
                                    }
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.sale.buy_back.index');

                        return $this->response(200, $response);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Internal Server Error!",
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('buy back-acc')) {
                return abort(403);
            }

            $buy_back = BuyBack::find($id);

            if ($buy_back === null) {
                abort(404);
            }

            $superuser = Auth::guard('superuser')->user();

            DB::beginTransaction();
            try {
                $failed = '';

                $buy_back_valid_price_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'buy_back_valid_price_credit')->first();

                $buy_back_valid_hpp_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'buy_back_valid_hpp_debet')->first();
                $buy_back_valid_hpp_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'buy_back_valid_hpp_credit')->first();

                $buy_back_disposal_price_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'buy_back_disposal_price_credit')->first();

                $buy_back_disposal_hpp_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'buy_back_disposal_hpp_debet')->first();
                $buy_back_disposal_hpp_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'buy_back_disposal_hpp_credit')->first();

                $penjualan_coa = null;
                if ($buy_back->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Non Marketplace']) {
                    $customer_coa_penjualan = CustomerCoaPenjualan::where('customer_id', $buy_back->sales_order->customer_id)->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->first();
                    if ($customer_coa_penjualan != null and $customer_coa_penjualan->coa_id != null) {
                        $penjualan_coa = $customer_coa_penjualan->coa_id;
                    }
                } elseif ($buy_back->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Shopee']) {
                    $penjualan_shopee = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_shopee')->first();
                    if ($penjualan_shopee != null and $penjualan_shopee->coa_id != null) {
                        $penjualan_coa = $penjualan_shopee->coa_id;
                    }
                } elseif ($buy_back->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Tokopedia']) {
                    $penjualan_tokopedia = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_tokopedia')->first();
                    if ($penjualan_tokopedia != null and $penjualan_tokopedia->coa_id != null) {
                        $penjualan_coa = $penjualan_tokopedia->coa_id;
                    }
                } elseif ($buy_back->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Lazada']) {
                    $penjualan_lazada = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_lazada')->first();
                    if ($penjualan_lazada != null and $penjualan_lazada->coa_id != null) {
                        $penjualan_coa = $penjualan_lazada->coa_id;
                    }
                } elseif ($buy_back->sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Blibli']) {
                    $penjualan_blibli = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_blibli')->first();
                    if ($penjualan_blibli != null and $penjualan_blibli->coa_id != null) {
                        $penjualan_coa = $penjualan_blibli->coa_id;
                    }
                }

                if ($penjualan_coa == null or $buy_back_valid_price_credit == null or $buy_back_valid_price_credit->coa_id == null or $buy_back_valid_hpp_debet == null or $buy_back_valid_hpp_debet->coa_id == null or $buy_back_valid_hpp_credit == null or $buy_back_valid_hpp_credit->coa_id == null or $buy_back_disposal_price_credit == null or $buy_back_disposal_price_credit->coa_id == null or $buy_back_disposal_hpp_debet == null or $buy_back_disposal_hpp_debet->coa_id == null or $buy_back_disposal_hpp_credit == null or $buy_back_disposal_hpp_credit->coa_id == null) {
                    $failed = 'Finance Setting is not set, please contact your Administrator!';
                } else {
                    // FIND HPP
                    $total_hpp = 0;
                    foreach ($buy_back->details as $item) {
                        $hpp_satuan = $item->sales_order_detail->hpp_total / $item->sales_order_detail->quantity;
                        $hpp = $item->buy_back_qty * $hpp_satuan;
                        $total_hpp = $total_hpp + $hpp;

                        // SAVE STOCK SALES ORDER
                        if ($buy_back->disposal == '0') {
                            // SAVE HPP
                            $hpp = new Hpp;
                            $hpp->type = $superuser->type;
                            $hpp->branch_office_id = $superuser->branch_office_id;
                            $hpp->product_id = $item->sales_order_detail->product_id;
                            $hpp->quantity = $item->buy_back_qty;
                            $hpp->price = $hpp_satuan;
                            $hpp->save();

                            $stock_sales_order = StockSalesOrder::where('warehouse_id', $buy_back->warehouse_id)->where('product_id', $item->sales_order_detail->product_id)->first();
                            if ($stock_sales_order) {
                                $getstock = $stock_sales_order->stock;

                                $stock_sales_order->stock = $getstock + $item->buy_back_qty;
                                $stock_sales_order->save();
                            } else {
                                $stock_sales_order = new StockSalesOrder;

                                $stock_sales_order->warehouse_id = $buy_back->warehouse_id;
                                $stock_sales_order->product_id = $item->sales_order_detail->product_id;
                                $stock_sales_order->stock = $item->buy_back_qty;
                                $stock_sales_order->save();
                            }
                        }
                    }

                    if ($buy_back->disposal == '0') {
                        // ADD JOURNAL
                        $journal = new Journal;
                        $journal->coa_id = $penjualan_coa;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->debet = $buy_back->details->sum('buy_back_total');
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $buy_back_valid_price_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->credit = $buy_back->details->sum('buy_back_total');
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $buy_back_valid_hpp_debet->coa_id;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->debet = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $buy_back_valid_hpp_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->credit = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    } else {
                        $journal = new Journal;
                        $journal->coa_id = $penjualan_coa;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->debet = $buy_back->details->sum('buy_back_total');
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $buy_back_disposal_price_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->credit = $buy_back->details->sum('buy_back_total');
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $buy_back_disposal_hpp_debet->coa_id;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->debet = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $buy_back_disposal_hpp_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['BUY_BACK'] . $buy_back->sales_order->code;
                        $journal->credit = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;
                    return $this->response(200, $response);
                } else {
                    $buy_back->status = BuyBack::STATUS['ACC'];
                    $buy_back->acc_by = Auth::guard('superuser')->id();
                    $buy_back->acc_at = Carbon::now()->toDateTimeString();
                    $buy_back->save();

                    DB::commit();

                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return $this->response(400, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('buy back-delete')) {
                return abort(403);
            }

            $buy_back = BuyBack::find($id);

            if ($buy_back === null) {
                abort(404);
            }

            foreach ($buy_back->details as $item) {
                $item->delete();
            }

            if ($buy_back->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('buy back-print')) {
            return abort(403);
        }

        // if (is_string($data)) {
        //     $data = json_decode($data);
        // }

        if ($id == NULL) {
            abort(404);
        }

        $data['data'] = BuyBack::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.sale.buy_back.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }
}
