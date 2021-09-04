<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Hpp;
use App\Entities\Accounting\Coa;
use App\Entities\Account\Superuser;
use App\Entities\Finance\SettingFinance;
use App\Entities\Master\Warehouse;
use App\Entities\Purchasing\Receiving;
use App\Entities\Sale\DeliveryOrderDetail;
use App\Entities\Inventory\Recondition;
use App\Entities\Inventory\ReconditionDisposal;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;

class SecretSettingController extends Controller
{
    public function index()
    {   
        return view('superuser.finance.secret_setting.index');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            
            DB::beginTransaction();

            try {
                Hpp::query()->delete();

                // HEAD OFFICE
                $warehouse_ids = Warehouse::where('type', Warehouse::TYPE['HEAD_OFFICE'])->where('status', Warehouse::STATUS['ACTIVE'])->pluck('id')->all();
                
                $receivings = Receiving::whereIn('warehouse_id', $warehouse_ids)->where('status', Receiving::STATUS['ACC'])->get();
                foreach ($receivings as $receive) {
                    foreach ($receive->details as $detail) {
                        $hpp                = new Hpp;
                        $hpp->type          = Warehouse::TYPE['HEAD_OFFICE'];
                        $hpp->product_id    = $detail->product_id;
                        $hpp->quantity      = $detail->total_quantity_ri;
                        $hpp->price         = $detail->ppb_detail->total_price_idr / $detail->ppb_detail->quantity;
                        $hpp->created_at    = $detail->receiving->acc_at;
                        $hpp->save();
                    }
                }

                $delivery_order_detail = DeliveryOrderDetail::where('status_validate', '1')
                                        ->whereHas('sales_order', function($query) {
                                            $warehouse_ids = Warehouse::where('type', Warehouse::TYPE['HEAD_OFFICE'])->where('status', Warehouse::STATUS['ACTIVE'])->pluck('id')->all();
                                            $query->whereIn('warehouse_id', $warehouse_ids);
                                        })->get();
                foreach ($delivery_order_detail as $do_detail) {
                    foreach ($do_detail->sales_order->sales_order_details as $detail) {
                        for ($i=0; $i < $detail->quantity ; $i++) { 
                            $hpp = Hpp::where('type', Warehouse::TYPE['HEAD_OFFICE'])->where('product_id', $detail->product_id)->orderBy('created_at', 'ASC')->first();
                            
                            $min = $hpp->quantity - 1;
                            if($min > 0) {
                                $hpp->quantity = $min;
                                $hpp->save();
                            } else {
                                $hpp->delete();
                            }
                        }
                    }
                }

                $disposal_detail = ReconditionDisposal::whereHas('recondition', function($query) {
                        $warehouse_ids = Warehouse::where('type', Warehouse::TYPE['HEAD_OFFICE'])->where('status', Warehouse::STATUS['ACTIVE'])->pluck('id')->all();
                        $query->whereIn('warehouse_id', $warehouse_ids)->where('status', Recondition::STATUS['ACC']);
                    })->get();

                foreach ($disposal_detail as $detail) {
                    for ($i=0; $i < $detail->quantity ; $i++) { 
                        $hpp = Hpp::where('type', Warehouse::TYPE['HEAD_OFFICE'])->where('product_id', $detail->product_id)->orderBy('created_at', 'ASC')->first();
                        
                        $min = $hpp->quantity - 1;
                        if($min > 0) {
                            $hpp->quantity = $min;
                            $hpp->save();
                        } else {
                            $hpp->delete();
                        }
                    }
                }

                // BRANCH OFFICE

                DB::commit();

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.finance.secret_setting.index');

                return $this->response(200, $response);
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
