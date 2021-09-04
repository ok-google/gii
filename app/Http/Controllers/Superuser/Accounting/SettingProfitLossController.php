<?php

namespace App\Http\Controllers\Superuser\Accounting;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\SettingProfitLoss;
use App\Entities\Account\Superuser;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;

class SettingProfitLossController extends Controller
{
    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('setting profit loss-manage')) {
            return abort(403);
        }

        $superuser = Auth::guard('superuser')->user();
            
        $data['coa'] = MasterRepo::coas_by_branch();
        $data['key'] = SettingProfitLoss::KEY;
        
        foreach (SettingProfitLoss::KEY as $value) {
            if($select_id = SettingProfitLoss::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', $value)->first()) {
                $in = array('b', 'd', 'e', 'f', 'g', 'h');
                if( in_array($value, $in) ) {
                    $data[$value] = unserialize($select_id->value);
                } else {
                    $data[$value] = $select_id->value;
                }
             } else {
                 $data[$value] = '';
             }
        }
        
        return view('superuser.accounting.setting_profit_loss.index', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
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
                    $superuser = Auth::guard('superuser')->user();

                    foreach (SettingProfitLoss::KEY as $value) {
                        if($setting_profit_loss = SettingProfitLoss::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', $value)->first()) {
                            $in = array('b', 'd', 'e', 'f', 'g', 'h');
                            if( in_array($value, $in) ) {
                                $arr = [];
                                if($request->$value) {
                                    foreach($request->$value as $key => $item){
                                        if($request->$value[$key]) {
                                            $arr[] = $request->$value[$key];
                                        }
                                    }
                                }
                                $setting_profit_loss->value = $arr ? serialize($arr) : null;
                                $setting_profit_loss->save();
                            } else {
                                $setting_profit_loss->value = $request->$value;
                                $setting_profit_loss->save();
                            }
                        } else {
                            $in = array('b', 'd', 'e', 'f', 'g', 'h');
                            if( in_array($value, $in) ) {
                                $arr = [];
                                if($request->$value) {
                                    foreach($request->$value as $key => $item){
                                        if($request->$value[$key]) {
                                            $arr[] = $request->$value[$key];
                                        }
                                    }
                                }
                                if($arr) {
                                    $setting_profit_loss = new SettingProfitLoss;
                                    $setting_profit_loss->type = $superuser->type;
                                    $setting_profit_loss->branch_office_id = $superuser->branch_office_id;
                                    $setting_profit_loss->key = $value;
                                    $setting_profit_loss->value = serialize($arr);
                                    $setting_profit_loss->save();
                                }
                            } else {
                                $setting_profit_loss = new SettingProfitLoss;
                                $setting_profit_loss->type = $superuser->type;
                                $setting_profit_loss->branch_office_id = $superuser->branch_office_id;
                                $setting_profit_loss->key = $value;
                                $setting_profit_loss->value = $request->$value;
                                $setting_profit_loss->save();
                            }
                        }
                    }

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.accounting.setting_profit_loss.index');

                    return $this->response(200, $response);
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $e->getMessage(),
                    ];
      
                    return $this->response(400, $response);
                }
            }
        }
    }
    
}
