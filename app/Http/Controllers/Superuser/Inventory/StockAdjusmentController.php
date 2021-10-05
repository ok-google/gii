<?php

namespace App\Http\Controllers\Superuser\Inventory;

use App\DataTables\Inventory\StockAdjusmentTable;
use App\Entities\Accounting\Hpp;
use App\Entities\Accounting\Journal;
use App\Entities\Finance\SettingFinance;
use App\Entities\Inventory\StockAdjusment;
use App\Entities\Inventory\StockAdjusmentDetail;
use App\Entities\Sale\StockSalesOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class StockAdjusmentController extends Controller
{
    public function json(Request $request, StockAdjusmentTable $datatable)
    {
        return $datatable->build($request);
    }

    public function get_sku(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $stock_sales_order = StockSalesOrder::where('warehouse_id', $request->id)->get();

            foreach ($stock_sales_order as $item) {
                $data[] = [
                    'product_id' => $item->product_id,
                    'sku' => $item->product->code,
                    'product_name' => $item->product->name,
                ];
            }

            return response()->json(['code' => 200, 'data' => $data]);
        }
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('stock adjusment-manage')) {
            return abort(403);
        }

        return view('superuser.inventory.stock_adjusment.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('stock adjusment-create')) {
            return abort(403);
        }

        $data['warehouses_display'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.stock_adjusment.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:stock_adjusment,code',
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

            if (!$request->col_product_id) {
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

                    $stock_adjusment = new StockAdjusment;

                    $stock_adjusment->code = $request->code;
                    $stock_adjusment->type = $superuser->type;
                    $stock_adjusment->branch_office_id = $superuser->branch_office_id;
                    $stock_adjusment->warehouse_id = $request->warehouse;

                    if ($request->minus) {
                        $stock_adjusment->minus = 1;
                    }

                    $stock_adjusment->status = StockAdjusment::STATUS['ACTIVE'];

                    if ($stock_adjusment->save()) {

                        if ($request->col_product_id) {
                            foreach ($request->col_product_id as $key => $value) {
                                if ($request->col_product_id[$key]) {
                                    $stock_adjusment_detail = new StockAdjusmentDetail;
                                    $stock_adjusment_detail->stock_adjusment_id = $stock_adjusment->id;
                                    $stock_adjusment_detail->product_id = $request->col_product_id[$key];
                                    $stock_adjusment_detail->qty = $request->col_qty[$key];
                                    $stock_adjusment_detail->description = $request->col_description[$key] ?? '';
                                    $stock_adjusment_detail->price = 0;
                                    $stock_adjusment_detail->total = 0;
                                    $stock_adjusment_detail->save();
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.inventory.stock_adjusment.index');

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
        if (!Auth::guard('superuser')->user()->can('stock adjusment-show')) {
            return abort(403);
        }

        $data['stock_adjusment'] = StockAdjusment::find($id);

        return view('superuser.inventory.stock_adjusment.show', $data);
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('stock adjusment-edit')) {
            return abort(403);
        }

        $data['stock_adjusment'] = StockAdjusment::find($id);

        $data['warehouses_display'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.stock_adjusment.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $stock_adjusment = StockAdjusment::find($id);

            if ($stock_adjusment == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:stock_adjusment,code,' . $stock_adjusment->id,
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

            if (!$request->col_product_id) {
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

                    if ($request->minus) {
                        $stock_adjusment->minus = 1;
                    } else {
                        $stock_adjusment->minus = 0;
                    }

                    if ($stock_adjusment->save()) {
                        if ($request->ids_delete) {
                            $pieces = explode(",", $request->ids_delete);
                            foreach ($pieces as $piece) {
                                StockAdjusmentDetail::where('id', $piece)->delete();
                            }
                        }

                        if ($request->col_product_id) {
                            foreach ($request->col_product_id as $key => $value) {
                                if ($request->col_product_id[$key]) {
                                    if ($request->edit[$key]) {
                                        $stock_adjusment_detail = StockAdjusmentDetail::find($request->edit[$key]);
                                        $stock_adjusment_detail->qty = $request->col_qty[$key];
                                        $stock_adjusment_detail->price = $request->col_price[$key] ?? 0;
                                        $stock_adjusment_detail->description = $request->col_description[$key] ?? '';

                                        $stock_adjusment_detail->save();
                                    } else {
                                        $stock_adjusment_detail = new StockAdjusmentDetail;

                                        $stock_adjusment_detail->stock_adjusment_id = $stock_adjusment->id;
                                        $stock_adjusment_detail->product_id = $request->col_product_id[$key];
                                        $stock_adjusment_detail->qty = $request->col_qty[$key];
                                        $stock_adjusment_detail->price = $request->col_price[$key] ?? 0;
                                        $stock_adjusment_detail->total = 0;
                                        $stock_adjusment_detail->description = $request->col_description[$key] ?? '';

                                        $stock_adjusment_detail->save();
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

                        $response['redirect_to'] = route('superuser.inventory.stock_adjusment.index');

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
            if (!Auth::guard('superuser')->user()->can('stock adjusment-acc')) {
                return abort(403);
            }

            $stock_adjusment = StockAdjusment::find($id);

            if ($stock_adjusment === null) {
                abort(404);
            }

            $superuser = Auth::guard('superuser')->user();

            DB::beginTransaction();
            try {
                $failed = '';

                $stock_adjusment_plus_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'stock_adjusment_plus_debet')->first();
                $stock_adjusment_plus_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'stock_adjusment_plus_credit')->first();

                $stock_adjusment_minus_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'stock_adjusment_minus_debet')->first();
                $stock_adjusment_minus_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'stock_adjusment_minus_credit')->first();

                if ($stock_adjusment_plus_debet == null or $stock_adjusment_plus_debet->coa_id == null or $stock_adjusment_plus_credit == null or $stock_adjusment_plus_credit->coa_id == null or $stock_adjusment_minus_debet == null or $stock_adjusment_minus_debet->coa_id == null or $stock_adjusment_minus_credit == null or $stock_adjusment_minus_credit->coa_id == null) {
                    $failed = 'Finance Setting is not set, please contact your Administrator!';
                } else {
                    // FIND HPP
                    $total_hpp = 0;
                    foreach ($stock_adjusment->details as $item) {

                        // SAVE STOCK SALES ORDER
                        if ($stock_adjusment->minus == '0') {
                            $hpp = Hpp::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('product_id', $item->product_id)->orderBy('created_at', 'ASC')->first();

                            if ($hpp == null && $item->price == 0) {
                                DB::rollback();

                                $response['failed'] = $item->product->name.' HPP data not found, please enter price manually';
                                return $this->response(200, $response);
                            }

                            $price = $hpp->price ?? $item->price;
                            $total_price = $price * $item->qty;
                            $total_hpp = $total_hpp + $total_price;

                            // SAVE HPP
                            $new_hpp = new Hpp;
                            $new_hpp->type = $superuser->type;
                            $new_hpp->branch_office_id = $superuser->branch_office_id;
                            $new_hpp->product_id = $item->product_id;
                            $new_hpp->quantity = $item->qty;
                            $new_hpp->price = $price;
                            $new_hpp->save();

                            $stock_sales_order = StockSalesOrder::where('warehouse_id', $stock_adjusment->warehouse_id)->where('product_id', $item->product_id)->first();
                            if ($stock_sales_order) {
                                $getstock = $stock_sales_order->stock;

                                $stock_sales_order->stock = $getstock + $item->qty;
                                $stock_sales_order->save();
                            } else {
                                $stock_sales_order = new StockSalesOrder;

                                $stock_sales_order->warehouse_id = $stock_adjusment->warehouse_id;
                                $stock_sales_order->product_id = $item->product_id;
                                $stock_sales_order->stock = $item->qty;
                                $stock_sales_order->save();
                            }

                            $stock_adjusment_detail = StockAdjusmentDetail::find($item->id);
                            $stock_adjusment_detail->price = $price;
                            $stock_adjusment_detail->total = $total_price;
                            $stock_adjusment_detail->save();
                        } else {
                            $stock_sales_order = StockSalesOrder::where('warehouse_id', $stock_adjusment->warehouse_id)->where('product_id', $item->product_id)->first();
                            if ($stock_sales_order->stock < $item->qty) {
                                DB::rollback();

                                $response['failed'] = $item->product->name . ' maximum is ' . $stock_sales_order->stock;
                                return $this->response(200, $response);
                            }

                            $hpp_total = 0;
                            for ($i = 0; $i < $item->qty; $i++) {
                                $hpp = Hpp::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('product_id', $item->product_id)->orderBy('created_at', 'ASC')->first();

                                if( $hpp ) {
                                    $hpp_total = $hpp_total + $hpp->price;

                                    $min = $hpp->quantity - 1;
                                    if ($min > 0) {
                                        $hpp->quantity = $min;
                                        $hpp->save();
                                    } else {
                                        $hpp->delete();
                                    }
                                }
                            }

                            $total_hpp = $total_hpp + $hpp_total;

                            $getstock = $stock_sales_order->stock;
                            $stock_sales_order->stock = $getstock - $item->qty;
                            $stock_sales_order->save();

                            $stock_adjusment_detail = StockAdjusmentDetail::find($item->id);
                            $stock_adjusment_detail->price = $hpp_total / $item->qty;
                            $stock_adjusment_detail->total = $hpp_total;
                            $stock_adjusment_detail->save();
                        }

                    }

                    if ($stock_adjusment->minus == '0') {
                        // ADD JOURNAL
                        $journal = new Journal;
                        $journal->coa_id = $stock_adjusment_plus_debet->coa_id;
                        $journal->name = Journal::PREJOURNAL['STOCK_ADJUSMENT'] . $stock_adjusment->code;
                        $journal->debet = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $stock_adjusment_plus_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['STOCK_ADJUSMENT'] . $stock_adjusment->code;
                        $journal->credit = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    } else {
                        $journal = new Journal;
                        $journal->coa_id = $stock_adjusment_minus_debet->coa_id;
                        $journal->name = Journal::PREJOURNAL['STOCK_ADJUSMENT'] . $stock_adjusment->code;
                        $journal->debet = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $stock_adjusment_minus_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['STOCK_ADJUSMENT'] . $stock_adjusment->code;
                        $journal->credit = $total_hpp;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    }
                }

                if ($failed) {
                    $response['failed'] = $failed;
                    return $this->response(200, $response);
                } else {
                    $stock_adjusment->status = StockAdjusment::STATUS['ACC'];
                    $stock_adjusment->acc_by = Auth::guard('superuser')->id();
                    $stock_adjusment->acc_at = Carbon::now()->toDateTimeString();
                    $stock_adjusment->save();

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
            if (!Auth::guard('superuser')->user()->can('stock adjusment-delete')) {
                return abort(403);
            }

            $stock_adjusment = StockAdjusment::find($id);

            if ($stock_adjusment === null) {
                abort(404);
            }

            foreach ($stock_adjusment->details as $item) {
                $item->delete();
            }

            if ($stock_adjusment->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

}
