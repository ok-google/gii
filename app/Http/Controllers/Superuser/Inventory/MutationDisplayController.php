<?php

namespace App\Http\Controllers\Superuser\Inventory;

use App\DataTables\Inventory\MutationDisplayTable;
use App\Entities\Inventory\MutationDisplay;
use App\Entities\Inventory\MutationDisplayDetail;
use App\Entities\Purchasing\ReceivingDetail;
use App\Entities\Purchasing\ReceivingDetailColly;
use App\Entities\Sale\StockSalesOrder;
use App\Entities\Master\Product;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use Carbon\Carbon;

class MutationDisplayController extends Controller
{
    public function json(Request $request, MutationDisplayTable $datatable)
    {
        return $datatable->build();
    }

    public function search_sku(Request $request)
    {
        if ($request->warehouse == null) {
            return ['results' => ''];
        }

        $products = Product::where('code', 'LIKE', '%' . $request->input('q', '') . '%')
            ->join('stock_sales_order', function ($join) use ($request) {
                $join->on('master_products.id', '=', 'stock_sales_order.product_id')
                    ->where('stock_sales_order.warehouse_id', '=', $request->warehouse)->where('stock_sales_order.stock', '>', 0);
            })
            ->get(['master_products.id', 'master_products.code as text', 'master_products.name', 'stock_sales_order.stock']);
        return ['results' => $products];
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('mutation display-manage')) {
            return abort(403);
        }

        return view('superuser.inventory.mutation_display.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('mutation display-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.mutation_display.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:mutation_display,code',
                'warehouse_from' => 'required|integer',
                'warehouse_to' => 'required|integer|different:warehouse_from',
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
                DB::beginTransaction();

                try {
                    $mutation_display = new MutationDisplay;

                    $mutation_display->code = $request->code;
                    $mutation_display->warehouse_from = $request->warehouse_from;
                    $mutation_display->warehouse_to = $request->warehouse_to;
                    $mutation_display->status = MutationDisplay::STATUS['ACTIVE'];

                    if ($mutation_display->save()) {
                        if ($request->sku) {
                            foreach ($request->sku as $key => $value) {
                                if ($request->sku[$key]) {
                                    $mutation_display_detail = new MutationDisplayDetail;
                                    $mutation_display_detail->mutation_display_id = $mutation_display->id;
                                    $mutation_display_detail->product_id = $request->sku[$key];
                                    $mutation_display_detail->qty = $request->qty[$key];
                                    $mutation_display_detail->description = $request->description[$key];
                                    $mutation_display_detail->save();
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.inventory.mutation_display.index');

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
        if (!Auth::guard('superuser')->user()->can('mutation display-show')) {
            return abort(403);
        }

        $data['mutation_display'] = MutationDisplay::findOrFail($id);

        return view('superuser.inventory.mutation_display.show', $data);
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('mutation display-edit')) {
            return abort(403);
        }

        $data['mutation_display'] = MutationDisplay::findOrFail($id);
        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.mutation_display.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $mutation_display = MutationDisplay::find($id);

            if ($mutation_display == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:mutation_display,code,' . $mutation_display->id,
                'warehouse_to' => 'required|integer',
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

            if ($request->warehouse_to == $mutation_display->warehouse_from) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'The warehouse to and warehouse from must be different.',
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $mutation_display->warehouse_to = $request->warehouse_to;

                    if ($mutation_display->save()) {
                        if ($request->ids_delete) {
                            $pieces = explode(",", $request->ids_delete);
                            foreach ($pieces as $piece) {
                                MutationDisplayDetail::where('id', $piece)->delete();
                            }
                        }

                        if ($request->sku) {
                            foreach ($request->sku as $key => $value) {
                                if ($request->sku[$key]) {
                                    if ($request->edit[$key]) {
                                        $mutation_display_detail = MutationDisplayDetail::find($request->edit[$key]);

                                        $mutation_display_detail->product_id = $request->sku[$key];
                                        $mutation_display_detail->qty = $request->qty[$key];
                                        $mutation_display_detail->description = $request->description[$key];
                                        $mutation_display_detail->save();
                                    } else {
                                        $mutation_display_detail = new MutationDisplayDetail;
                                        $mutation_display_detail->mutation_display_id = $mutation_display->id;
                                        $mutation_display_detail->product_id = $request->sku[$key];
                                        $mutation_display_detail->qty = $request->qty[$key];
                                        $mutation_display_detail->description = $request->description[$key];
                                        $mutation_display_detail->save();
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

                        $response['redirect_to'] = route('superuser.inventory.mutation_display.index');

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
            if (!Auth::guard('superuser')->user()->can('mutation display-acc')) {
                return abort(403);
            }

            $mutation_display = MutationDisplay::findOrFail($id);

            if ($mutation_display === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $failed = '';

                $mutation_display->status = MutationDisplay::STATUS['ACC'];
                $mutation_display->acc_by = Auth::guard('superuser')->id();
                $mutation_display->acc_at = Carbon::now()->toDateTimeString();

                if ($mutation_display->save()) {
                    foreach ($mutation_display->details as $item) {
                        $stock_sales_order = StockSalesOrder::where('warehouse_id', $mutation_display->warehouse_from)->where('product_id', $item->product_id)->first();

                        if ($stock_sales_order && $stock_sales_order->stock >= $item->qty) {
                            // MENGURANGI STOCK WAREHOUSE FROM
                            $stok_akhir = $stock_sales_order->stock - $item->qty;
                            $stock_sales_order->stock = $stok_akhir;
                            $stock_sales_order->save();

                            // MENAMBAH STOCK WAREHOUSE TO
                            $stock_sales_order_to = StockSalesOrder::where('warehouse_id', $mutation_display->warehouse_to)->where('product_id', $item->product_id)->first();
                            if ($stock_sales_order_to) {
                                $stok_akhir = $stock_sales_order_to->stock + $item->qty;

                                $stock_sales_order_to->stock = $stok_akhir;
                                $stock_sales_order_to->save();
                            } else {
                                $stock_sales_order_to = new StockSalesOrder;

                                $stock_sales_order_to->warehouse_id = $mutation_display->warehouse_to;
                                $stock_sales_order_to->product_id = $item->product_id;
                                $stock_sales_order_to->stock = $item->qty;
                                $stock_sales_order_to->save();
                            }
                        } else {
                            $failed = 'Stock ' . $item->product->name . ' tidak mencukupi.';
                            break;
                        }
                    }
                }

                if ($failed) {
                    DB::rollback();
                    $response['failed'] = $failed;
                    return $this->response(200, $response);
                } else {
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
            if (!Auth::guard('superuser')->user()->can('mutation display-delete')) {
                return abort(403);
            }

            $mutation_display = MutationDisplay::find($id);

            if ($mutation_display === null) {
                abort(404);
            }

            if ($mutation_display->delete()) {

                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
