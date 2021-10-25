<?php

namespace App\Http\Controllers\Superuser\Inventory;

use App\DataTables\Inventory\ProductConversionTable;
use App\Entities\Accounting\Hpp;
use App\Entities\Inventory\ProductConversion;
use App\Entities\Inventory\ProductConversionDetail;
use App\Entities\Master\Product;
use App\Entities\Sale\StockSalesOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ProductConversionController extends Controller
{
    public function json(Request $request, ProductConversionTable $datatable)
    {
        return $datatable->build($request);
    }

    public function search_sku(Request $request)
    {
        if ($request->warehouse == null) {
            return ['results' => ''];
        }

        if ($request->warehouse == 'all-product') {
            $products = Product::where('code', 'LIKE', '%' . $request->input('q', '') . '%')
                ->where('status', 1)
                ->get(['id', 'code as text']);
        } else {
            $products = Product::where('code', 'LIKE', '%' . $request->input('q', '') . '%')
                ->join('stock_sales_order', function ($join) use ($request) {
                    $join->on('master_products.id', '=', 'stock_sales_order.product_id')
                        ->where('stock_sales_order.warehouse_id', '=', $request->warehouse)->where('stock_sales_order.stock', '>', 0);
                })
                ->get(['master_products.id', 'master_products.code as text', 'master_products.name', 'stock_sales_order.stock']);
        }

        return ['results' => $products];
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('product conversion-manage')) {
            return abort(403);
        }

        return view('superuser.inventory.product_conversion.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('product conversion-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.product_conversion.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:product_conversion,code',
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
                DB::beginTransaction();

                try {
                    $product_conversion = new ProductConversion;

                    $product_conversion->code = $request->code;
                    $product_conversion->warehouse_id = $request->warehouse;
                    $product_conversion->status = ProductConversion::STATUS['ACTIVE'];

                    if ($product_conversion->save()) {
                        if ($request->product_from) {
                            foreach ($request->product_from as $key => $value) {
                                if ($request->product_from[$key]) {
                                    $product_conversion_detail = new ProductConversionDetail;
                                    $product_conversion_detail->product_conversion_id = $product_conversion->id;
                                    $product_conversion_detail->product_from = $request->product_from[$key];
                                    $product_conversion_detail->product_to = $request->product_to[$key];
                                    $product_conversion_detail->qty = $request->qty[$key];
                                    $product_conversion_detail->description = $request->description[$key];
                                    $product_conversion_detail->save();
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.inventory.product_conversion.index');

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
        if (!Auth::guard('superuser')->user()->can('product conversion-show')) {
            return abort(403);
        }

        $data['product_conversion'] = ProductConversion::findOrFail($id);

        return view('superuser.inventory.product_conversion.show', $data);
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('product conversion-edit')) {
            return abort(403);
        }

        $data['product_conversion'] = ProductConversion::findOrFail($id);
        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.product_conversion.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $product_conversion = ProductConversion::find($id);

            if ($product_conversion == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:product_conversion,code,' . $product_conversion->id,
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

                    if ($request->ids_delete) {
                        $pieces = explode(",", $request->ids_delete);
                        foreach ($pieces as $piece) {
                            ProductConversionDetail::where('id', $piece)->delete();
                        }
                    }

                    if ($request->product_from) {
                        foreach ($request->product_from as $key => $value) {
                            if ($request->product_from[$key]) {
                                if ($request->edit[$key]) {
                                    $product_conversion_detail = ProductConversionDetail::find($request->edit[$key]);

                                    $product_conversion_detail->product_from = $request->product_from[$key];
                                    $product_conversion_detail->product_to = $request->product_to[$key];
                                    $product_conversion_detail->qty = $request->qty[$key];
                                    $product_conversion_detail->description = $request->description[$key];
                                    $product_conversion_detail->save();
                                } else {
                                    $product_conversion_detail = new ProductConversionDetail;
                                    $product_conversion_detail->product_conversion_id = $product_conversion->id;
                                    $product_conversion_detail->product_from = $request->product_from[$key];
                                    $product_conversion_detail->product_to = $request->product_to[$key];
                                    $product_conversion_detail->qty = $request->qty[$key];
                                    $product_conversion_detail->description = $request->description[$key];
                                    $product_conversion_detail->save();
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

                    $response['redirect_to'] = route('superuser.inventory.product_conversion.index');

                    return $this->response(200, $response);
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'Internal Server Error!',
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('product conversion-acc')) {
                return abort(403);
            }

            $product_conversion = ProductConversion::findOrFail($id);

            if ($product_conversion === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $failed = '';

                $superuser = Auth::guard('superuser')->user();

                $product_conversion->status = ProductConversion::STATUS['ACC'];
                $product_conversion->acc_by = Auth::guard('superuser')->id();
                $product_conversion->acc_at = Carbon::now()->toDateTimeString();

                if ($product_conversion->save()) {
                    foreach ($product_conversion->details as $item) {
                        $stock_sales_order = StockSalesOrder::where('warehouse_id', $product_conversion->warehouse_id)->where('product_id', $item->product_from)->first();

                        if ($stock_sales_order && $stock_sales_order->stock >= $item->qty) {
                            // MENGURANGI STOCK CONVERT PRODUCT
                            $stok_akhir = $stock_sales_order->stock - $item->qty;
                            $stock_sales_order->stock = $stok_akhir;
                            $stock_sales_order->save();

                            // MENAMBAH STOCK CONVERT PRODUCT
                            $stock_sales_order_to = StockSalesOrder::where('warehouse_id', $product_conversion->warehouse_id)->where('product_id', $item->product_to)->first();
                            if ($stock_sales_order_to) {
                                $stok_akhir = $stock_sales_order_to->stock + $item->qty;

                                $stock_sales_order_to->stock = $stok_akhir;
                                $stock_sales_order_to->save();
                            } else {
                                $stock_sales_order_to = new StockSalesOrder;

                                $stock_sales_order_to->warehouse_id = $product_conversion->warehouse_id;
                                $stock_sales_order_to->product_id = $item->product_to;
                                $stock_sales_order_to->stock = $item->qty;
                                $stock_sales_order_to->save();
                            }

                            // REMOVE HPP FROM CONVERT PRODUCT
                            $hpp_total = 0;
                            for ($i = 0; $i < $item->qty; $i++) {
                                $hpp = Hpp::where('type', $superuser->type)
                                    ->where('branch_office_id', $superuser->branch_office_id)
                                    ->where('product_id', $item->product_from)
                                    ->orderBy('created_at', 'ASC')
                                    ->first();

                                $hpp_total += $hpp->price;

                                $min = $hpp->quantity - 1;
                                if ($min > 0) {
                                    $hpp->quantity = $min;
                                    $hpp->save();
                                } else {
                                    $hpp->delete();
                                }
                            }

                            // ADDED HPP TO CONVERTED PRODUCT
                            $hpp = new Hpp;
                            $hpp->type = $superuser->type;
                            $hpp->branch_office_id = $superuser->branch_office_id;
                            $hpp->product_id = $item->product_to;
                            $hpp->quantity = $item->qty;
                            $hpp->price = $hpp_total / $item->qty;
                            $hpp->save();
                        } else {
                            $failed = 'Stock ' . $item->product_from_rel->name . ' tidak mencukupi.';
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
            if (!Auth::guard('superuser')->user()->can('product conversion-delete')) {
                return abort(403);
            }

            $product_conversion = ProductConversion::find($id);

            if ($product_conversion === null) {
                abort(404);
            }

            DB::beginTransaction();

            try {
                if ($product_conversion->delete()) {
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
}
