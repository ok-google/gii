<?php

namespace App\Http\Controllers\Superuser\QualityControl;

use App\Http\Controllers\Controller;
use App\Entities\Purchasing\ReceivingDetail;
use App\Entities\Purchasing\ReceivingDetailColly;
use App\Entities\Master\Product;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;

class QualityControl1Controller extends Controller
{
    public function get_barcode(Request $request)
    {
        if ($request->ajax()) {
            $msg = '';
            $data = '';

            $receiving_detail_colly = ReceivingDetailColly::where('code', $request->code)->first();
            
            if($receiving_detail_colly) {
                if($receiving_detail_colly->status_qc == ReceivingDetailColly::STATUS_QC['NOTUSED']) {
                    $data = [
                        'id'        => $receiving_detail_colly->id, 
                        'code'      => $receiving_detail_colly->code,
                        'sku'       => Product::findOrFail(ReceivingDetail::findOrFail($receiving_detail_colly->receiving_detail_id)->product_id)->code,
                        'name'      => Product::findOrFail(ReceivingDetail::findOrFail($receiving_detail_colly->receiving_detail_id)->product_id)->name,
                        'quantity'  => $receiving_detail_colly->quantity_ri,
                    ];
                } else {
                    $msg = 'Barcode has been used.';
                }
            } else {
                $msg = 'Barcode not found.';
            }

            return response()->json(['code'=> 200, 'msg' => $msg, 'data' => $data]);
        }
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('quality control utama-manage')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(3);

        return view('superuser.quality_control.quality_control_1.index', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
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
                foreach($request->id as $key => $value){

                    $receiving_detail_colly = ReceivingDetailColly::find($request->id[$key]);
    
                    if($receiving_detail_colly) {
                        if($request->quantity[$key] == '0' || $request->quantity[$key] == '') {
                            $receiving_detail_colly->quantity_mutation =  $receiving_detail_colly->quantity_ri;
                        } else {
                            $receiving_detail_colly->quantity_mutation =  $receiving_detail_colly->quantity_ri - $request->quantity[$key];
                            $receiving_detail_colly->quantity_recondition = $request->quantity[$key];
                            $receiving_detail_colly->warehouse_reparation_id = $request->warehouse_reparation;
                        }
    
                        $receiving_detail_colly->status_qc = ReceivingDetailColly::STATUS_QC['USED'];
                        $receiving_detail_colly->description = $request->description[$key];
                        $receiving_detail_colly->date_recondition = Carbon::now()->toDateTimeString();
                        $receiving_detail_colly->save();
                    }
                    
                }
    
                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];
    
                $response['redirect_to'] = route('superuser.quality_control.quality_control_1.index');
    
                return $this->response(200, $response);
            }
        }
    }
}
