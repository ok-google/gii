<?php

namespace App\Http\Controllers\Superuser\Sale;

use App\DataTables\Sale\SalesOrderTable;
use App\Entities\Sale\SalesOrder;
use App\Entities\Sale\SalesOrderDetail;
use App\Entities\Master\Company;
use App\Entities\Master\Product;
use App\Entities\Sale\StockSalesOrder;
use App\Entities\Sale\DeliveryOrder;
use App\Entities\Sale\DeliveryOrderDetail;
use App\Http\Controllers\Controller;
use App\Imports\Sale\SalesOrderImport;
use App\Repositories\MasterRepo;
use App\Repositories\DeliveryOrderRepo;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Validator;
use DomPDF;
use DB;

class SalesOrderController extends Controller
{
    public function search_sku(Request $request)
    {
        $products = Product::where('code', 'LIKE', '%'.$request->input('q', '').'%')
            ->where('status', Product::STATUS['ACTIVE'])
            ->get(['id', 'code as text', 'name']);
        return ['results' => $products];
    }

    public function json(Request $request, SalesOrderTable $datatable)
    {
        // return $datatable->build();
        return $datatable->with('show', $request->show)->build($request);
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('sales order-manage')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.sale.sales_order.index', $data);
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('sales order-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);
        $data['customers'] = MasterRepo::customers();
        $data['ekspedisis'] = MasterRepo::ekspedisis();

        return view('superuser.sale.sales_order.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:sales_order,code',
                'marketplace_order' => 'required|string',
                'warehouse' => 'required|integer',
                'customer' => 'required|integer',
                'store_name' => 'required|string',
                'store_phone' => 'required|string',
                'resi' => 'string',
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
                $sales_order = new SalesOrder;

                $sales_order->code = $request->code;
                $sales_order->marketplace_order = SalesOrder::MARKETPLACE_ORDER[$request->marketplace_order];
                $sales_order->warehouse_id = $request->warehouse;
                $sales_order->customer_id = $request->customer;
                $sales_order->ekspedisi_id = $request->ekspedisi;
                $sales_order->resi = $request->resi;
                
                $sales_order->store_name = $request->store_name;
                $sales_order->store_phone = $request->store_phone;

                $sales_order->total = $request->subtotal;
                $sales_order->tax = $request->tax;
                $sales_order->discount = $request->discount;
                $sales_order->shipping_fee = $request->shipping_fee;
                $sales_order->grand_total = $request->grand_total;

                $sales_order->status = SalesOrder::STATUS['ACTIVE'];

                if ($sales_order->save()) {
                    if($request->sku) {
                        foreach($request->sku as $key => $value){
                            if($request->sku[$key]) {
                                $sales_order_detail = new SalesOrderDetail;
                                $sales_order_detail->sales_order_id = $sales_order->id;
                                $sales_order_detail->product_id = $request->sku[$key];
                                $sales_order_detail->quantity = $request->quantity[$key];
                                $sales_order_detail->price = $request->price[$key];
                                $sales_order_detail->total = $request->total[$key];
                                $sales_order_detail->save();
                            }
                        }
                    }
                    

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.sale.sales_order.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('sales order-show')) {
            return abort(403);
        }

        $data['sales_order'] = SalesOrder::findOrFail($id);

        return view('superuser.sale.sales_order.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('sales order-edit')) {
            return abort(403);
        }

        $data['sales_order'] = SalesOrder::findOrFail($id);

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);
        $data['customers'] = MasterRepo::customers();
        $data['ekspedisis'] = MasterRepo::ekspedisis();

        return view('superuser.sale.sales_order.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $sales_order = SalesOrder::find($id);

            if ($sales_order == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:sales_order,code,' . $sales_order->id,
                'marketplace_order' => 'required|string',
                'warehouse' => 'required|integer',
                'customer' => 'required',
                'store_name' => 'required|string',
                'store_phone' => 'required|string',
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
                $sales_order->code = $request->code;
                $sales_order->marketplace_order = SalesOrder::MARKETPLACE_ORDER[$request->marketplace_order];
                $sales_order->warehouse_id = $request->warehouse;
                $sales_order->store_name = $request->store_name;
                $sales_order->store_phone = $request->store_phone;

                $sales_order->resi = $request->resi;

                if(SalesOrder::MARKETPLACE_ORDER[$request->marketplace_order] == SalesOrder::MARKETPLACE_ORDER['Non Marketplace']) {
                    $sales_order->customer_id = $request->customer;
                    $sales_order->ekspedisi_id = $request->ekspedisi;
                } else {
                    $sales_order->customer_marketplace = $request->customer;
                    $sales_order->address_marketplace = $request->address_marketplace;
                    $sales_order->ekspedisi_marketplace = $request->ekspedisi;
                }
                
                $sales_order->total = $request->subtotal;
                $sales_order->tax = $request->tax;
                $sales_order->discount = $request->discount;
                $sales_order->shipping_fee = $request->shipping_fee;
                $sales_order->grand_total = $request->grand_total;

                if ($sales_order->save()) {
                    if($request->ids_delete) {
                        $pieces = explode(",",$request->ids_delete);
                        foreach($pieces as $piece){
                            SalesOrderDetail::where('id', $piece)->delete();
                        }
                    }

                    if($request->sku) {
                        foreach($request->sku as $key => $value){
                            if($request->sku[$key]) {

                                if($request->edit[$key]) {
                                    $sales_order_detail = SalesOrderDetail::find($request->edit[$key]);

                                    $sales_order_detail->product_id = $request->sku[$key];
                                    $sales_order_detail->quantity = $request->quantity[$key];
                                    $sales_order_detail->price = $request->price[$key];
                                    $sales_order_detail->total = $request->total[$key];
                                    $sales_order_detail->save();
                                } else {
                                    $sales_order_detail = new SalesOrderDetail;
                                    $sales_order_detail->sales_order_id = $sales_order->id;
                                    $sales_order_detail->product_id = $request->sku[$key];
                                    $sales_order_detail->quantity = $request->quantity[$key];
                                    $sales_order_detail->price = $request->price[$key];
                                    $sales_order_detail->total = $request->total[$key];
                                    $sales_order_detail->save();
                                }
                                
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.sale.sales_order.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('sales order-acc')) {
                return abort(403);
            }

            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            $duplicate_product = [];
            $duplicate = false;
            $out_of_stock = false;
            foreach ($sales_order->sales_order_details as $detail) {
                if(in_array($detail->product_id, $duplicate_product)) {
                    $duplicate = true;
                    break;
                } else {
                    array_push($duplicate_product, $detail->product_id);
                }
                
                $stock_sales_order = StockSalesOrder::where('warehouse_id', $sales_order->warehouse_id)->where('product_id', $detail->product_id)->first();
                if($stock_sales_order) {
                    if($stock_sales_order->stock < $detail->quantity) {
                        $out_of_stock = true;
                        break;
                    }
                } else {
                    $out_of_stock = true;
                    break;
                }
            }

            if($duplicate) {
                $response['message'] = 'Duplicate product found!';
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            } elseif($out_of_stock) {
                $response['message'] = 'Out of stock product found!';
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            } else {
                
                foreach ($sales_order->sales_order_details as $detail) {
                    $stock_sales_order = StockSalesOrder::where('warehouse_id', $sales_order->warehouse_id)->where('product_id', $detail->product_id)->first();
                    
                    $getstock = $stock_sales_order->stock;
                    $stock_sales_order->stock = $getstock - $detail->quantity;
                    $stock_sales_order->save();
                }

                $sales_order->status = SalesOrder::STATUS['ACC'];
                if ($sales_order->save()) {
                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            }
        }
    }

    /*
    REMOVE By: danitri33
    public function bulk_acc(Request $request) 
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('sales order-acc')) {
                return abort(403);
            }

            DB::beginTransaction();
            try {
                $errors = [];

                $pecah = explode(',', $request->bulk_acc_ids);
                foreach ($pecah as $id) {
                    if($id) {
                        $sales_order = SalesOrder::find($id);

                        if ($sales_order === null) {
                            continue;
                        }

                        if($sales_order->status == SalesOrder::STATUS['ACC']) {
                            $errors[] = '<a href="'.route('superuser.sale.sales_order.show', $sales_order->id).'">'.$sales_order->code.'</a> : Data has approved!';
                            continue;
                        }

                        if( count($sales_order->sales_order_details) < 1 ) {
                            $errors[] = '<a href="'.route('superuser.sale.sales_order.show', $sales_order->id).'">'.$sales_order->code.'</a> : Empty product details!';
                            continue;
                        }

                        $sales_order_duplicate_resi = SalesOrder::where('resi', $sales_order->resi)->where('status', SalesOrder::STATUS['ACC'])->first();
                        if($sales_order_duplicate_resi) {
                            $errors[] = '<a href="'.route('superuser.sale.sales_order.show', $sales_order->id).'">'.$sales_order->code.'</a> : Duplicate AWB/Resi with <a href="'.route('superuser.sale.sales_order.show', $sales_order_duplicate_resi->id).'">'.$sales_order_duplicate_resi->code.'</a>';
                            continue;
                        }

                        $out_of_stock = false;
                        foreach ($sales_order->sales_order_details as $detail) {
                            if($detail->product->non_stock == '0') {
                                $stock_sales_order = StockSalesOrder::where('warehouse_id', $sales_order->warehouse_id)->where('product_id', $detail->product_id)->first();
                                if($stock_sales_order) {
                                    $sum_multiple_product_quantity = SalesOrderDetail::where('sales_order_id', $id)->where('product_id', $detail->product_id)->sum('quantity');
                                    
                                    if($stock_sales_order->stock < $sum_multiple_product_quantity) {
                                        $out_of_stock = true;
                                        break;
                                    }
                                } else {
                                    $out_of_stock = true;
                                    break;
                                }
                            }
                        }

                        if($out_of_stock) {
                            $errors[] = '<a href="'.route('superuser.sale.sales_order.edit', $sales_order->id).'">'.$sales_order->code.'</a> : Out of stock product found!';
                        } else {
                            
                            foreach ($sales_order->sales_order_details as $detail) {
                                
                                if($detail->product->non_stock == '0') {
                                    $stock_sales_order = StockSalesOrder::where('warehouse_id', $sales_order->warehouse_id)->where('product_id', $detail->product_id)->first();
                                
                                    $getstock = $stock_sales_order->stock;
                                    $stock_sales_order->stock = $getstock - $detail->quantity;
                                    $stock_sales_order->save();
                                }
                            }

                            $sales_order->status = SalesOrder::STATUS['ACC'];
                            $sales_order->acc_by = Auth::guard('superuser')->id();
                            $sales_order->acc_at = Carbon::now()->toDateTimeString();

                            if( $sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Non Marketplace'] ) {
                                $delivery_order = new DeliveryOrder;

                                $delivery_order->code = DeliveryOrderRepo::generateCode();
                                $delivery_order->status = DeliveryOrder::STATUS['ACTIVE'];
                                $delivery_order->save();

                                $count = DeliveryOrderDetail::whereMonth('created_at', Carbon::now()->month)->count();

                                $delivery_order_detail = new DeliveryOrderDetail;
                                $delivery_order_detail->code = 'DO-'.Carbon::now()->format('my').'-'.sprintf('%07d', $count+1);
                                $delivery_order_detail->delivery_order_id = $delivery_order->id;
                                $delivery_order_detail->sales_order_id = $sales_order->id;
                                $delivery_order_detail->save();

                                $sales_order->status_sales_order = 1;

                                if($sales_order->resi == null) {
                                    $sales_order->resi = $sales_order->code;
                                }
                            }
                            
                            $sales_order->save();
                        }
                    }
                }

                DB::commit();

                if($errors) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $errors,
                    ];

                    $response['redirect_to'] = '#datatable';
                    return $this->response(400, $response);
                } else {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
        
                    $response['redirect_to'] = '#datatable';
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
    */

    public function bulk_action(Request $request) 
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('sales order-acc')) {
                return abort(403);
            }
            
            $type = $request->bulk_type;

            DB::beginTransaction();
            try {
                $errors = [];

                $pecah = explode(',', $request->bulk_acc_ids);
                foreach ($pecah as $id) {
                    if($id) {
                        $sales_order = SalesOrder::find($id);

                        if ($sales_order === null) {
                            continue;
                        }

                        if($type == "delete"){

                            if($sales_order->status != SalesOrder::STATUS['ACTIVE']) {

                                $errors[] = '<a href="'.route('superuser.sale.sales_order.show', $sales_order->id).'">'.$sales_order->code.'</a> : Data has approved!';
                                continue;
                                
                            }else{

                                $sales_order->delete();

                            }

                        }else{

                            if($sales_order->status == SalesOrder::STATUS['ACC']) {
                                $errors[] = '<a href="'.route('superuser.sale.sales_order.show', $sales_order->id).'">'.$sales_order->code.'</a> : Data has approved!';
                                continue;
                            }
    
                            if( count($sales_order->sales_order_details) < 1 ) {
                                $errors[] = '<a href="'.route('superuser.sale.sales_order.show', $sales_order->id).'">'.$sales_order->code.'</a> : Empty product details!';
                                continue;
                            }
    
                            $sales_order_duplicate_resi = SalesOrder::where('resi', $sales_order->resi)->where('status', SalesOrder::STATUS['ACC'])->first();
                            if($sales_order_duplicate_resi) {
                                $errors[] = '<a href="'.route('superuser.sale.sales_order.show', $sales_order->id).'">'.$sales_order->code.'</a> : Duplicate AWB/Resi with <a href="'.route('superuser.sale.sales_order.show', $sales_order_duplicate_resi->id).'">'.$sales_order_duplicate_resi->code.'</a>';
                                continue;
                            }
    
                            $out_of_stock = false;
                            foreach ($sales_order->sales_order_details as $detail) {
                                if($detail->product->non_stock == '0') {
                                    $stock_sales_order = StockSalesOrder::where('warehouse_id', $sales_order->warehouse_id)->where('product_id', $detail->product_id)->first();
                                    if($stock_sales_order) {
                                        $sum_multiple_product_quantity = SalesOrderDetail::where('sales_order_id', $id)->where('product_id', $detail->product_id)->sum('quantity');
                                        
                                        if($stock_sales_order->stock < $sum_multiple_product_quantity) {
                                            $out_of_stock = true;
                                            break;
                                        }
                                    } else {
                                        $out_of_stock = true;
                                        break;
                                    }
                                }
                            }
    
                            if($out_of_stock) {
                                $errors[] = '<a href="'.route('superuser.sale.sales_order.edit', $sales_order->id).'">'.$sales_order->code.'</a> : Out of stock product found!';
                            } else {
                                
                                foreach ($sales_order->sales_order_details as $detail) {
                                    
                                    if($detail->product->non_stock == '0') {
                                        $stock_sales_order = StockSalesOrder::where('warehouse_id', $sales_order->warehouse_id)->where('product_id', $detail->product_id)->first();
                                    
                                        $getstock = $stock_sales_order->stock;
                                        $stock_sales_order->stock = $getstock - $detail->quantity;
                                        $stock_sales_order->save();
                                    }
                                }
    
                                $sales_order->status = SalesOrder::STATUS['ACC'];
                                $sales_order->acc_by = Auth::guard('superuser')->id();
                                $sales_order->acc_at = Carbon::now()->toDateTimeString();
    
                                if( $sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Non Marketplace'] ) {
                                    $delivery_order = new DeliveryOrder;
    
                                    $delivery_order->code = DeliveryOrderRepo::generateCode();
                                    $delivery_order->status = DeliveryOrder::STATUS['ACTIVE'];
                                    $delivery_order->save();
    
                                    $count = DeliveryOrderDetail::whereMonth('created_at', Carbon::now()->month)->count();
    
                                    $delivery_order_detail = new DeliveryOrderDetail;
                                    $delivery_order_detail->code = 'DO-'.Carbon::now()->format('my').'-'.sprintf('%07d', $count+1);
                                    $delivery_order_detail->delivery_order_id = $delivery_order->id;
                                    $delivery_order_detail->sales_order_id = $sales_order->id;
                                    $delivery_order_detail->save();
    
                                    $sales_order->status_sales_order = 1;
    
                                    if($sales_order->resi == null) {
                                        $sales_order->resi = $sales_order->code;
                                    }
                                }
                                
                                $sales_order->save();
                            }
                        }
                    }
                }

                DB::commit();

                if($errors) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $errors,
                    ];

                    $response['redirect_to'] = '#datatable';
                    return $this->response(400, $response);
                } else {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];
        
                    $response['redirect_to'] = '#datatable';
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

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('sales order-delete')) {
                return abort(403);
            }

            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            $sales_order->status = SalesOrder::STATUS['DELETED'];

            if($sales_order->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
            
        }
    }

    public function force_delete(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('sales order-delete')) {
                return abort(403);
            }

            $sales_order = SalesOrder::find($id);

            if ($sales_order === null) {
                abort(404);
            }

            if ($sales_order->status_sales_order == '1') {
                abort(404);
            }
            
            foreach ($sales_order->sales_order_details as $detail) {
                            
                if($detail->product->non_stock == '0') {
                    $stock_sales_order = StockSalesOrder::where('warehouse_id', $sales_order->warehouse_id)->where('product_id', $detail->product_id)->first();
                
                    $getstock = $stock_sales_order->stock;
                    $stock_sales_order->stock = $getstock + $detail->quantity;
                    $stock_sales_order->save();
                }
                $detail->delete();
            }

            $sales_order->status = SalesOrder::STATUS['DELETED'];

            if($sales_order->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
            
        }
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('sales order-print')) {
            return abort(403);
        }

        // if (is_string($data)) {
        //     $data = json_decode($data);
        // }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['sales_order'] = SalesOrder::findOrFail($id);

        $pdf = DomPDF::loadView('superuser.sale.sales_order.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function import_template()
    {
        $filename = 'master-product-import-template.xlsx';
        return Excel::download(new ProductImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new SalesOrderImport($request->warehouse, $request->marketplace, $request->store_name, $request->store_phone);
            
            Excel::import($import, $request->import_file);
            
        // dd($import);
            // if($import->error) {
            //     return redirect()->back()->withErrors($import->error);
            // }
            
            // return redirect()->back()->with(['message' => 'Import success']);

            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }

    public function export()
    {
        $filename = 'master-product-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductExport, $filename);
    }
}
