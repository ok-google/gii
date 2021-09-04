<?php

namespace App\Http\Controllers\Superuser\Inventory;

use App\DataTables\Inventory\MutationTable;
use App\Entities\Inventory\Mutation;
use App\Entities\Inventory\MutationDetail;
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

class MutationController extends Controller
{
    public function get_barcode(Request $request)
    {
        if ($request->ajax()) {
            $msg = '';
            $data = '';

            $receiving_detail_colly = ReceivingDetailColly::where('code', $request->code)->first();
            
            if($receiving_detail_colly) {
                if($receiving_detail_colly->status_qc == ReceivingDetailColly::STATUS_QC['USED']) {
                    if($receiving_detail_colly->status_mutation == ReceivingDetailColly::STATUS_MUTATION['NOTUSED']) {
                        if($receiving_detail_colly->quantity_mutation != '0') {
                            $data = [
                                'id'        => $receiving_detail_colly->id, 
                                'code'      => $receiving_detail_colly->code,
                                'sku'       => Product::findOrFail(ReceivingDetail::findOrFail($receiving_detail_colly->receiving_detail_id)->product_id)->code,
                                'name'      => Product::findOrFail(ReceivingDetail::findOrFail($receiving_detail_colly->receiving_detail_id)->product_id)->name,
                                'quantity'  => $receiving_detail_colly->quantity_mutation,
                            ];
                        } else {
                            $msg = 'Barcode has not passed Quality Control.';
                        }
                    } else {
                        $msg = 'Barcode has been used.';
                    }
                    
                } else {
                    $msg = 'Barcode has not passed Quality Control.';
                }
            } else {
                $msg = 'Barcode not found.';
            }

            return response()->json(['code'=> 200, 'msg' => $msg, 'data' => $data]);
        }
    }

    public function delete_barcode(Request $request, $id)
    {
        if ($request->ajax()) {

            $mutation_detail = MutationDetail::where('receiving_detail_colly_id', $id)->delete();

            $receiving_detail_colly = ReceivingDetailColly::find($id);
            $receiving_detail_colly->status_mutation = ReceivingDetailColly::STATUS_MUTATION['NOTUSED'];

            $receiving_detail_colly->save();

            return response()->json(['code'=> 200]);
        }
    }

    public function json(Request $request, MutationTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('mutation-manage')) {
            return abort(403);
        }

        return view('superuser.inventory.mutation.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('mutation-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.mutation.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:mutation,code',
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
                    $mutation = new Mutation;

                    $mutation->code = $request->code;
                    $mutation->warehouse_id = $request->warehouse;
                    $mutation->status = Mutation::STATUS['ACTIVE'];

                    if ($mutation->save()) {
                        $error = '';
                        if($request->id) {
                            foreach($request->id as $key => $value){
                                $receiving_detail_colly = ReceivingDetailColly::find($request->id[$key]);
                
                                if($receiving_detail_colly) {
                                    if($receiving_detail_colly->status_mutation == ReceivingDetailColly::STATUS_MUTATION['USED']) {
                                        $error = 'Barcode '.$receiving_detail_colly->code.' has been used!';
                                        break;
                                    }

                                    $mutation_detail = new MutationDetail;
                                    $mutation_detail->mutation_id = $mutation->id;
                                    $mutation_detail->receiving_detail_colly_id = $receiving_detail_colly->id;
                                    $mutation_detail->save();
        
                                    $receiving_detail_colly->status_mutation = ReceivingDetailColly::STATUS_MUTATION['USED'];
                                    $receiving_detail_colly->save();
                                }
                            }
                        }

                        if($error) {
                            DB::rollback();
                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $error,
                            ];
            
                            return $this->response(400, $response);
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.inventory.mutation.index');

                        return $this->response(200, $response);
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

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('mutation-show')) {
            return abort(403);
        }

        $data['mutation'] = Mutation::findOrFail($id);

        return view('superuser.inventory.mutation.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('mutation-edit')) {
            return abort(403);
        }

        $data['mutation'] = Mutation::findOrFail($id);
        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.inventory.mutation.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $mutation = Mutation::find($id);

            if ($mutation == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:mutation,code,' . $mutation->id,
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
                    $mutation->warehouse_id = $request->warehouse;

                    if ($mutation->save()) {
                        $error = '';
                        if($request->id) {
                            foreach($request->id as $key => $value){
                                $receiving_detail_colly = ReceivingDetailColly::find($request->id[$key]);
                
                                if($receiving_detail_colly && $request->edit[$key] == 'new') {
                                    if($receiving_detail_colly->status_mutation == ReceivingDetailColly::STATUS_MUTATION['USED']) {
                                        $error = 'Barcode '.$receiving_detail_colly->code.' has been used!';
                                        break;
                                    }

                                    $mutation_detail = new MutationDetail;
                                    $mutation_detail->mutation_id = $mutation->id;
                                    $mutation_detail->receiving_detail_colly_id = $receiving_detail_colly->id;
                                    $mutation_detail->save();
        
                                    $receiving_detail_colly->status_mutation = ReceivingDetailColly::STATUS_MUTATION['USED'];
                                    $receiving_detail_colly->save();
                                }
                            }
                        }

                        if($error) {
                            DB::rollback();
                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $error,
                            ];
            
                            return $this->response(400, $response);
                        }

                        DB::commit();
                        
                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.inventory.mutation.index');

                        return $this->response(200, $response);
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

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('mutation-acc')) {
                return abort(403);
            }

            $mutation = Mutation::find($id);

            if ($mutation === null) {
                abort(404);
            }

            $mutation->status = Mutation::STATUS['ACC'];

            if ($mutation->save()) {

                $warehouse = $mutation->warehouse_id;
                foreach ($mutation->mutation_details as $detail) {
                    $product_id = $detail->receiving_detail_colly->receiving_detail->product_id;
                    $stock = $detail->receiving_detail_colly->quantity_mutation; 

                    $stock_sales_order = StockSalesOrder::where('warehouse_id', $warehouse)->where('product_id', $product_id)->first();
                    if($stock_sales_order) {
                        $getstock = $stock_sales_order->stock;

                        $stock_sales_order->stock = $getstock + $stock;
                        $stock_sales_order->save();
                    } else {
                        $stock_sales_order = new StockSalesOrder;

                        $stock_sales_order->warehouse_id = $warehouse;
                        $stock_sales_order->product_id = $product_id;
                        $stock_sales_order->stock = $stock;
                        $stock_sales_order->save();
                    }
                }

                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('mutation-delete')) {
                return abort(403);
            }

            $mutation = Mutation::find($id);

            if ($mutation === null) {
                abort(404);
            }

            $mutation->status = Mutation::STATUS['DELETED'];

            if ($mutation->save()) {
                $mutation_detail = MutationDetail::where('mutation_id', $id)->get();
                foreach( $mutation_detail as $key => $value ) {
                    $receiving_detail_colly = ReceivingDetailColly::find($value->receiving_detail_colly_id);
                    if($receiving_detail_colly) {
                        $receiving_detail_colly->status_mutation = ReceivingDetailColly::STATUS_MUTATION['NOTUSED'];
                        $receiving_detail_colly->save();
                    }
                }
                
                $mutation_detail = MutationDetail::where('mutation_id', $id)->delete();

                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
