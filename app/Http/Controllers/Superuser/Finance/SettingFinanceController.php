<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Coa;
use App\Entities\Account\Superuser;
use App\Entities\Finance\SettingFinance;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;

class SettingFinanceController extends Controller
{
    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('setting finance-manage')) {
            return abort(403);
        }

        $superuser = Auth::guard('superuser')->user();
            
        $data['coa'] = MasterRepo::coas_by_branch();
        $data['key'] = SettingFinance::KEY;
        
        foreach (SettingFinance::KEY as $value) {
            if($select_id = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', $value)->first()) {
                $data[$value] = $select_id->coa_id;
             } else {
                 $data[$value] = '';
             }
        }

        return view('superuser.finance.setting_finance.index', $data);
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

                    foreach (SettingFinance::KEY as $value) {
                        if($setting_finance = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', $value)->first()) {
                            $setting_finance->coa_id = $request->$value;
                            $setting_finance->save();
                         } else {
                            $setting_finance = new SettingFinance;
                            $setting_finance->type = $superuser->type;
                            $setting_finance->branch_office_id = $superuser->branch_office_id;
                            $setting_finance->key = $value;
                            $setting_finance->coa_id = $request->$value;
                            $setting_finance->save();
                         }
                    }

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.finance.setting_finance.index');

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
    
}
