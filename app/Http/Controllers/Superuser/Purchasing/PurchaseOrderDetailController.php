<?php

namespace App\Http\Controllers\Superuser\Purchasing;

use App\Entities\Purchasing\PurchaseOrder;
use App\Entities\Purchasing\PurchaseOrderDetail;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;

class PurchaseOrderDetailController extends Controller
{
    public function create($id)
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-create')) {
            return abort(403);
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);

        return view('superuser.purchasing.purchase_order_detail.create', $data);
    }

    public function store(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'sku' => 'required|integer',
                'quantity' => 'nullable|numeric',
                'unit_price' => 'nullable|numeric',
                'local_freight_cost' => 'nullable|numeric',
                'kurs' => 'nullable|numeric',
                'sea_freight' => 'nullable|numeric',
                'local_freight' => 'nullable|numeric',
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
                $purchase_order = PurchaseOrder::find($id);

                if ($purchase_order == null) {
                    abort(404);
                }

                $purchase_order_detail = new PurchaseOrderDetail;

                $purchase_order_detail->ppb_id = $purchase_order->id;
                $purchase_order_detail->product_id = $request->sku;
                $purchase_order_detail->quantity = $request->quantity;
                $purchase_order_detail->unit_price = $request->unit_price;
                $purchase_order_detail->local_freight_cost = $request->local_freight_cost;

                $purchase_order_detail->total_price_rmb = ($request->quantity * $request->unit_price) + $request->local_freight_cost;

                $purchase_order_detail->kurs = $request->kurs;
                $purchase_order_detail->sea_freight = $request->sea_freight;
                $purchase_order_detail->local_freight = $request->local_freight;

                $purchase_order_detail->order_date = $request->order_date;

                $purchase_order_detail->no_container = $request->no_container;
                $purchase_order_detail->qty_container = $request->container_qty;
                $purchase_order_detail->colly_qty = $request->colly_qty;

                // SET TAX
                $total_price_before_tax = ((($request->quantity * $request->unit_price) + $request->local_freight_cost) * $request->kurs ) + $request->sea_freight + $request->local_freight;

                $tax = 0;
                if($purchase_order->tax > 0) {
                    $tax = $total_price_before_tax * $purchase_order->tax / 100;
                }
                $total_price_after_tax = $total_price_before_tax + $tax;

                $purchase_order_detail->total_tax = $tax;
                $purchase_order_detail->total_price_idr = $total_price_after_tax;

                if ($purchase_order_detail->save()) {
                    $purchase_order->grand_total_rmb = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_rmb');
                    $purchase_order->grand_total_idr = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_idr');
                    $purchase_order->save();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.purchasing.purchase_order.step', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id, $detail)
    {
        if(!Auth::guard('superuser')->user()->can('purchase order-edit')) {
            return abort(403);
        }

        $data['purchase_order'] = PurchaseOrder::findOrFail($id);
        $data['purchase_order_detail'] = PurchaseOrderDetail::findOrFail($detail);

        return view('superuser.purchasing.purchase_order_detail.edit', $data);
    }

    public function update(Request $request, $id, $detail)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'sku' => 'required|integer',
                'quantity' => 'nullable|numeric',
                'unit_price' => 'nullable|numeric',
                'local_freight_cost' => 'nullable|numeric',
                'kurs' => 'nullable|numeric',
                'sea_freight' => 'nullable|numeric',
                'local_freight' => 'nullable|numeric',
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
                $purchase_order = PurchaseOrder::find($id);
                $purchase_order_detail = PurchaseOrderDetail::find($detail);

                if ($purchase_order == null OR $purchase_order_detail == null) {
                    abort(404);
                }
                
                $purchase_order_detail->product_id = $request->sku;
                $purchase_order_detail->quantity = $request->quantity;
                $purchase_order_detail->unit_price = $request->unit_price;
                $purchase_order_detail->local_freight_cost = $request->local_freight_cost;

                $purchase_order_detail->total_price_rmb = ($request->quantity * $request->unit_price) + $request->local_freight_cost;

                $purchase_order_detail->kurs = $request->kurs;
                $purchase_order_detail->sea_freight = $request->sea_freight;
                $purchase_order_detail->local_freight = $request->local_freight;
                
                $purchase_order_detail->order_date = $request->order_date;

                $purchase_order_detail->no_container = $request->no_container;
                $purchase_order_detail->qty_container = $request->container_qty;
                $purchase_order_detail->colly_qty = $request->colly_qty;

                // SET TAX
                $total_price_before_tax = ((($request->quantity * $request->unit_price) + $request->local_freight_cost) * $request->kurs ) + $request->sea_freight + $request->local_freight;

                $tax = 0;
                if($purchase_order->tax > 0) {
                    $tax = $total_price_before_tax * $purchase_order->tax / 100;
                }
                $total_price_after_tax = $total_price_before_tax + $tax;

                $purchase_order_detail->total_tax = $tax;
                $purchase_order_detail->total_price_idr = $total_price_after_tax;

                if ($purchase_order_detail->save()) {
                    $purchase_order->grand_total_rmb = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_rmb');
                    $purchase_order->grand_total_idr = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_idr');
                    $purchase_order->save();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.purchasing.purchase_order.step', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id, $detail_id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('purchase order-delete')) {
                return abort(403);
            }

            $purchase_order = PurchaseOrder::find($id);
            $purchase_order_detail = PurchaseOrderDetail::find($detail_id);

            if ($purchase_order === null OR $purchase_order_detail === null) {
                abort(404);
            }

            if ($purchase_order_detail->delete()) {
                $purchase_order->grand_total_rmb = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_rmb');
                $purchase_order->grand_total_idr = PurchaseOrderDetail::where('ppb_id', $id)->sum('total_price_idr');
                $purchase_order->save();
                
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }

    public function bulk_delete(Request $request)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('purchase order-delete')) {
                return abort(403);
            }
            $purchase_order = PurchaseOrder::find($request->purchase_order_id);

            if ($purchase_order === null) {
                abort(404);
            }

            $ids = $request->ids;

            if($ids) {
                foreach ($ids as $id) {
                    $purchase_order_detail = PurchaseOrderDetail::find($id);
                    $purchase_order_detail->delete();
                }

                $purchase_order->grand_total_rmb = PurchaseOrderDetail::where('ppb_id', $request->purchase_order_id)->sum('total_price_rmb');
                $purchase_order->grand_total_idr = PurchaseOrderDetail::where('ppb_id', $request->purchase_order_id)->sum('total_price_idr');
                $purchase_order->save();
            }
                
            $response['redirect_to'] = 'reload()';
            return $this->response(200, $response);
        }
    }
}
