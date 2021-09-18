<?php

namespace App\Http\Controllers\Superuser\Sale;

use App\DataTables\Sale\DeliveryOrderTable;
use App\Entities\Sale\SalesOrder;
use App\Entities\Sale\DeliveryOrder;
use App\Entities\Sale\DeliveryOrderDetail;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Validator;
use DomPDF;

class DeliveryOrderController extends Controller
{
    public function json(Request $request, DeliveryOrderTable $datatable)
    {
        return $datatable->build();
    }

    public function get_store(Request $request)
    {
        if ($request->ajax()) {

            $sales_order = SalesOrder::select('store_name', 'ekspedisi_marketplace', DB::raw('count(*) as total'))
                ->where('status', SalesOrder::STATUS['ACC'])
                ->where('status_sales_order', '0')
                ->where('warehouse_id', $request->id)
                ->groupBy('store_name', 'ekspedisi_marketplace')
                
                ->get();

            return response()->json(['code' => 200, 'data' => $sales_order]);
        }
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('delivery order-manage')) {
            return abort(403);
        }

        return view('superuser.sale.delivery_order.index');
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('delivery order-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        return view('superuser.sale.delivery_order.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:delivery_order,code',
                'warehouse' => 'required',
                'store_name' => 'required',
                
                'order_count' => 'required|integer',
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
                    $delivery_order = new DeliveryOrder;

                    $delivery_order->code = $request->code;
                    $delivery_order->status = DeliveryOrder::STATUS['ACTIVE'];

                    if ($delivery_order->save()) {

                        $get_sales_order = SalesOrder::where('status', SalesOrder::STATUS['ACC'])
                            ->where('status_sales_order', '0')
                            ->where('store_name', $request->store_name)
                            ->where('ekspedisi_marketplace', $request->ekspedisi_marketplace)
                            ->where('warehouse_id', $request->warehouse)
                            ->limit($request->order_count)
                            ->get();

                        foreach ($get_sales_order as $value) {
                            $sales_order = SalesOrder::find($value->id);
                            $sales_order->status_sales_order = 1;

                            if ($sales_order->save()) {
                                $count = DeliveryOrderDetail::whereMonth('created_at', Carbon::now()->month)->count();

                                $delivery_order_detail = new DeliveryOrderDetail;
                                $delivery_order_detail->code = 'DO-' . Carbon::now()->format('my') . '-' . sprintf('%07d', $count + 1);
                                $delivery_order_detail->delivery_order_id = $delivery_order->id;
                                $delivery_order_detail->sales_order_id = $value->id;
                                $delivery_order_detail->save();
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];

                        $response['redirect_to'] = route('superuser.sale.delivery_order.index');

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

    public function packing_pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('delivery order-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['delivery_order'] = DeliveryOrder::findOrFail($id);

        $collect = [];
        $total_so = 0;
        $total_quantity = 0;
        $warehouse = '';

        foreach ($data['delivery_order']->details as $detail) {
            $total_so = $total_so + 1;
            foreach ($detail->sales_order->sales_order_details as $sales_order_detail) {
                if (!empty($collect[$sales_order_detail->product_id]['quantity'])) {
                    $collect[$sales_order_detail->product_id]['quantity'] += $sales_order_detail->quantity;
                } else {
                    $collect[$sales_order_detail->product_id]['quantity'] = $sales_order_detail->quantity;
                    $collect[$sales_order_detail->product_id]['sku'] = $sales_order_detail->product->code;
                    $collect[$sales_order_detail->product_id]['name'] = $sales_order_detail->product->name;
                }
                $total_quantity = $total_quantity + $sales_order_detail->quantity;
                $warehouse = $sales_order_detail->sales_order->warehouse->name;
            }
        }

        $collect = collect($collect)->sortBy('sku')->toArray();

        $data['collect'] = $collect;
        $data['total_so'] = $total_so;
        $data['total_quantity'] = $total_quantity;
        $data['warehouse'] = $warehouse;

        $pdf = DomPDF::loadView('superuser.sale.delivery_order.packing_pdf', $data);

        $customPaper = array(0, 0, 226.40, 500);
        $pdf->setPaper($customPaper);

        $GLOBALS['bodyHeight'] = 0;

        $dom_pdf = $pdf->getDomPDF();
        $dom_pdf->setCallbacks(
            array(
                'myCallbacks' => array(
                    'event' => 'end_frame', 'f' => function ($infos) {
                        $frame = $infos["frame"];
                        if (strtolower($frame->get_node()->nodeName) === "body") {
                            $padding_box = $frame->get_padding_box();
                            //   dd($padding_box); // To see the output of the padding_box 
                            $GLOBALS['bodyHeight'] += $padding_box['h'];
                        }
                    }
                )
            )
        );

        $pdf->stream();
        unset($pdf);

        $pdf = DomPDF::loadView('superuser.sale.delivery_order.packing_pdf', $data);
        // 80mm x auto+50
        $pdf->setPaper(array(0, 0, 226.40, $GLOBALS['bodyHeight'] + 50));

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function delivery_order_pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('delivery order-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['delivery_order'] = DeliveryOrder::findOrFail($id);

        $collect = [];
        $total_so = 0;
        $total_quantity = 0;
        $warehouse = '';

        foreach ($data['delivery_order']->details as $detail) {
            $total_so = $total_so + 1;
            foreach ($detail->sales_order->sales_order_details as $sales_order_detail) {
                if (!empty($collect[$sales_order_detail->product_id]['quantity'])) {
                    $collect[$sales_order_detail->product_id]['quantity'] += $sales_order_detail->quantity;
                } else {
                    $collect[$sales_order_detail->product_id]['quantity'] = $sales_order_detail->quantity;
                    $collect[$sales_order_detail->product_id]['sku'] = $sales_order_detail->product->code;
                    $collect[$sales_order_detail->product_id]['name'] = $sales_order_detail->product->name;
                }
                $total_quantity = $total_quantity + $sales_order_detail->quantity;
                $warehouse = $sales_order_detail->sales_order->warehouse->name;
            }
        }


        $data['collect'] = $collect;
        $data['total_so'] = $total_so;
        $data['total_quantity'] = $total_quantity;
        $data['warehouse'] = $warehouse;

        $pdf = DomPDF::loadView('superuser.sale.delivery_order.delivery_order_pdf', $data);


        $customPaper = array(0, 0, 283, 424.50);
        $pdf->setPaper($customPaper);

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        $data['delivery_order']->print_count = $data['delivery_order']->print_count + 1;
        $data['delivery_order']->save();

        return $pdf->stream();
    }

    public function delivery_order_pdf_non_marketplace($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('delivery order-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['delivery_order'] = DeliveryOrder::findOrFail($id);

        $collect = [];
        $total_so = 0;
        $total_quantity = 0;
        $warehouse = '';

        foreach ($data['delivery_order']->details as $detail) {
            $total_so = $total_so + 1;
            foreach ($detail->sales_order->sales_order_details as $sales_order_detail) {
                if (!empty($collect[$sales_order_detail->product_id]['quantity'])) {
                    $collect[$sales_order_detail->product_id]['quantity'] += $sales_order_detail->quantity;
                } else {
                    $collect[$sales_order_detail->product_id]['quantity'] = $sales_order_detail->quantity;
                    $collect[$sales_order_detail->product_id]['sku'] = $sales_order_detail->product->code;
                    $collect[$sales_order_detail->product_id]['name'] = $sales_order_detail->product->name;
                }
                $total_quantity = $total_quantity + $sales_order_detail->quantity;
                $warehouse = $sales_order_detail->sales_order->warehouse->name;
            }
        }


        $data['collect'] = $collect;
        $data['total_so'] = $total_so;
        $data['total_quantity'] = $total_quantity;
        $data['warehouse'] = $warehouse;

        $pdf = DomPDF::loadView('superuser.sale.delivery_order.delivery_order_pdf_non_marketplace', $data);

        // 210mm x 148mm
        $customPaper = array(0, 0, 594.30, 418.84);
        $pdf->setPaper($customPaper);

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        $data['delivery_order']->print_count = $data['delivery_order']->print_count + 1;
        $data['delivery_order']->save();

        return $pdf->stream();
    }
}
