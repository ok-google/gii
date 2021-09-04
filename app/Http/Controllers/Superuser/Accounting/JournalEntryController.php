<?php

namespace App\Http\Controllers\Superuser\Accounting;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\Coa;
use App\Entities\Account\Superuser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;

class JournalEntryController extends Controller
{
    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('journal-create')) {
            return abort(403);
        }

        $superuser = Auth::guard('superuser')->user();
            
        $data['coa'] = Coa::where('type', $superuser->type)
                        ->where('branch_office_id', $superuser->branch_office_id)
                        ->get();
        
        $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

        if($journal_periode == null) {
            $data['min_date']   = '';
            $data['from_date']  = Carbon::now()->toDateString();
            $data['to_date']    = Carbon::now()->toDateString();
        } else {
            $data['min_date']   = Carbon::parse( $journal_periode->to_date )->addDay()->toDateString();
            $data['from_date']  = Carbon::parse( $journal_periode->to_date )->addDay()->toDateString();
            $data['to_date']    = Carbon::now()->toDateString();
        }

        return view('superuser.accounting.journal_entry.index', $data);
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

            if(!$request->coa_id) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Empty Entry !',
                ];
                return $this->response(400, $response);
            }

            if($request->subtotal_debet != $request->subtotal_credit) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Total balance must same!',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    if($request->coa_id) {
                        foreach($request->coa_id as $key => $value){
                            if($request->coa_id[$key]) {
                                $journal = new Journal;

                                $journal->coa_id = $request->coa_id[$key];
                                $journal->name = $request->name_detail[$key];
                                
                                if($request->debet[$key]) {
                                    $journal->debet = $request->debet[$key];
                                } else if($request->credit[$key]) {
                                    $journal->credit = $request->credit[$key];
                                }

                                $journal->status = Journal::STATUS['UNPOST'];
                                $journal->created_at = $request->date_detail[$key];

                                $journal->save();
                            }
                        }
                    }

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.accounting.journal.index');

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
