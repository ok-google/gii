<?php

namespace App\Http\Controllers\Superuser\QualityControl;

use App\Http\Controllers\Controller;
use App\Entities\QualityControl\QualityControl2;
use App\Entities\Sale\StockSalesOrder;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class QualityControl2Controller extends Controller
{
    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('quality control display-manage')) {
            return abort(403);
        }

        $data['warehouses_display'] = MasterRepo::warehouses_by_category(2);
        $data['warehouses_reparation'] = MasterRepo::warehouses_by_category(3);
        return view('superuser.quality_control.quality_control_2.index', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'warehouse' => 'required|integer',
                'warehouse_reparation' => 'required|integer',
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
                    $is_valid_stock = true;
                    if ($request->sku) {
                        foreach ($request->sku as $key => $value) {
                            if ($request->sku[$key] && $request->quantity[$key]) {
                                $quality_control_2 = new QualityControl2;
                                $quality_control_2->warehouse_id = $request->warehouse;
                                $quality_control_2->warehouse_reparation_id = $request->warehouse_reparation;
                                $quality_control_2->product_id = $request->sku[$key];
                                $quality_control_2->quantity = $request->quantity[$key];
                                $quality_control_2->description = $request->keterangan[$key];
                                $quality_control_2->save();

                                $stock_sales_order = StockSalesOrder::where('warehouse_id', $request->warehouse)->where('product_id', $request->sku[$key])->first();
                                if ($stock_sales_order && $stock_sales_order->stock >= $request->quantity[$key]) {
                                    $getstock = $stock_sales_order->stock;

                                    $stock_sales_order->stock = $getstock - $request->quantity[$key];
                                    $stock_sales_order->save();
                                } else {
                                    $is_valid_stock = false;
                                    break;
                                }
                            }
                        }
                    }

                    if ($is_valid_stock) {
                        DB::commit();
                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.quality_control.quality_control_2.index');

                        return $this->response(200, $response);
                    } else {
                        DB::rollback();
                        $response['notification'] = [
                            'alert' => 'block',
                            'type' => 'alert-danger',
                            'header' => 'Error',
                            'content' => 'Invalid stock, maybe stock has changed! Please reload your browser.',
                        ];

                        return $this->response(400, $response);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Internal Server Error",
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }
}
