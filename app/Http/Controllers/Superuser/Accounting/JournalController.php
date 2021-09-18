<?php

namespace App\Http\Controllers\Superuser\Accounting;

use App\DataTables\Accounting\JournalTable;
use App\Http\Controllers\Controller;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\JournalSaldo;
use App\Entities\Accounting\CashFlowSaldo;
use App\Exports\Accounting\JournalExport;
use App\Entities\Accounting\SettingProfitLoss;
use App\Entities\Account\Superuser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Excel;
use Validator;
use Carbon\Carbon;
use DB;
use DomPDF;

class JournalController extends Controller
{
    public function json(Request $request, JournalTable $datatable)
    {
        return $datatable->build($request);
    }

    public function jsons(Request $request)
    {
        $data = [];
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        
        if($from_date && $to_date) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            if($superuser->type) {
                $journal = Journal::whereHas('coa', function($query) {
                    $superuser = Superuser::find(Auth::guard('superuser')->id());

                    $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id);
                })
                ->whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
                ->orderBy('created_at', 'ASC')
                ->limit(10)
                ->get();

                foreach ($journal as $key => $value) {
                    $data['data'][] = [
                        Carbon::parse($value->created_at)->format('j/m/Y'),
                        $value->coa->code.' / '.$value->coa->name,
                        $value->name,
                        $value->debet ? 'Rp. '.number_format($value->debet, 2, ",", ".") : '',
                        $value->credit ? 'Rp. '.number_format($value->credit, 2, ",", ".") : '',
                        $value->debet,
                        $value->credit
                    ];
                }
                if(empty($data['data'])) {
                    $data['data'] = '';
                }
            } else {
                $data['data'] = '';
            }

            
        } else {
            $data['data'] = '';
        }
                
        return $data;
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('journal-manage')) {
            return abort(403);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());
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

        $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periode'] = $journal_periode;
        
        return view('superuser.accounting.journal.index', $data);
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('journal-manage')) {
            return abort(403);
        }

        $journal_periode = JournalPeriode::find($id);
        $data['journal_periode'] = $journal_periode;

        $data['from_date']  = $journal_periode->from_date;
        $data['to_date']    = $journal_periode->to_date;

        $superuser = Superuser::find(Auth::guard('superuser')->id());
        
        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;

        return view('superuser.accounting.journal.show', $data);
    }

    public function unpost($id) {
        $superuser = Superuser::find(Auth::guard('superuser')->id());
        $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

        if($id == $journal_periode->id) {
            DB::beginTransaction();
            try {
                $journal_saldo = JournalSaldo::where('periode_id', $journal_periode->id)->get(['id']);
                JournalSaldo::destroy($journal_saldo->toArray());
                
                $cash_flow = CashFlowSaldo::where('periode_id', $journal_periode->id)->first();
                if($cash_flow) {
                    $cash_flow->delete();
                }

                $journal_periode->delete();
                DB::commit();

                $response['redirect_to'] = route('superuser.accounting.journal.index');
                return $this->response(200, $response);
            } catch (\Exception $e) {
                DB::rollback();
                return $this->response(400, $response);
            }
        }
        return $this->response(400, $response);
    }

    public function posting(Request $request) {
        if ($request->ajax()) {

            if(!$request->from_date OR !$request->to_date) {
                return $this->response(400, $response);
            }

            DB::beginTransaction();
            try {
                $superuser = Superuser::find(Auth::guard('superuser')->id());

                $journal_periode = new JournalPeriode;
                $journal_periode->type = $superuser->type;
                $journal_periode->branch_office_id = $superuser->branch_office_id;
                $journal_periode->from_date = $request->from_date;
                $journal_periode->to_date = $request->to_date;

                if($journal_periode->save()) {
                    $last_journal = JournalPeriode::where('id', '!=', $journal_periode->id)->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

                    // MAKE CASH FLOW BEGINNING BALANCE
                    if($last_journal) {
                        $last_cash_flow = CashFlowSaldo::where('periode_id', $last_journal->id)->first();

                        $A = $last_cash_flow->beginning_balance;

                        $B = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
                        ->whereHas('coa', function ($query) use ($superuser) {
                            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
                                ->where('group', 1)
                                ->where(function ($query2) {
                                    $query2->where('code', 'like', '11.01%')->orWhere('code', 'like', '11.02%');
                                });
                        })
                        ->whereBetween('created_at', [$last_journal->from_date." 00:00:00", $last_journal->to_date." 23:59:59"])
                        ->groupBy('coa_id')
                        ->get();

                        $C = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
                        ->whereHas('coa', function ($query) use ($superuser) {
                            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('group', 5);
                        })
                        ->whereBetween('created_at', [$last_journal->from_date." 00:00:00", $last_journal->to_date." 23:59:59"])
                        ->groupBy('coa_id')
                        ->get();

                        $D = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
                        ->whereHas('coa', function ($query) use ($superuser) {
                            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('group', 7);
                        })
                        ->whereBetween('created_at', [$last_journal->from_date." 00:00:00", $last_journal->to_date." 23:59:59"])
                        ->groupBy('coa_id')
                        ->get();

                        $beginning_balance = $A + ($B->sum('total_debet') - $B->sum('total_credit')) - ($C->sum('total_debet') - $C->sum('total_credit')) - ($D->sum('total_debet') - $D->sum('total_credit'));

                        $cash_flow = new CashFlowSaldo;
                        $cash_flow->periode_id =  $journal_periode->id;
                        $cash_flow->beginning_balance = $beginning_balance;
                        $cash_flow->save();

                    } else {
                        $cash_flow = new CashFlowSaldo;
                        $cash_flow->periode_id =  $journal_periode->id;
                        $cash_flow->beginning_balance = 0;
                        $cash_flow->save();
                    }

                    $coas = Coa::where('type', $superuser->type)
                        ->where('branch_office_id', $superuser->branch_office_id)
                        ->where('group' , '!=', 5)
                        ->where('status', Coa::STATUS['ACTIVE'])->get();

                    foreach ($coas as $coa) {
                        
                        if($last_journal) {
                            $journal_debet = Journal::where('coa_id', $coa->id)
                            ->whereBetween('created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"])
                            ->sum('debet');

                            $journal_credit = Journal::where('coa_id', $coa->id)
                            ->whereBetween('created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"])
                            ->sum('credit');

                            $new_saldo = new JournalSaldo;
                            $new_saldo->periode_id  = $journal_periode->id;
                            $new_saldo->coa_id      = $coa->id;

                            $sum_journal = $journal_debet - $journal_credit;
                            
                            $journal_saldo = JournalSaldo::where('periode_id', $last_journal->id)->where('coa_id', $coa->id)->first();
                            if($journal_saldo) {
                                if($journal_saldo->position == JournalSaldo::POSITION['DEBET']) {
                                    $sum_journal = $sum_journal + $journal_saldo->saldo;
                                } else if($journal_saldo->position == JournalSaldo::POSITION['CREDIT']) {
                                    $sum_journal = $sum_journal - $journal_saldo->saldo;
                                }
                            } 

                            if($sum_journal > 0) {
                                $new_saldo->position    = JournalSaldo::POSITION['DEBET'];
                                $new_saldo->saldo       = $sum_journal;
                            } else if($sum_journal == 0) {
                                $new_saldo->position    = JournalSaldo::POSITION['BALANCE'];
                                $new_saldo->saldo       = 0;
                            } else {
                                $new_saldo->position    = JournalSaldo::POSITION['CREDIT'];
                                $new_saldo->saldo       = abs($sum_journal);
                            }

                            $new_saldo->save();
                        } else {
                            $journal_debet = Journal::where('coa_id', $coa->id)
                            ->whereBetween('created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"])
                            ->sum('debet');

                            $journal_credit = Journal::where('coa_id', $coa->id)
                            ->whereBetween('created_at', [$request->from_date." 00:00:00", $request->to_date." 23:59:59"])
                            ->sum('credit');

                            $new_saldo = new JournalSaldo;
                            $new_saldo->periode_id  = $journal_periode->id;
                            $new_saldo->coa_id      = $coa->id;

                            $sum_journal = $journal_debet - $journal_credit;
                            if($sum_journal > 0) {
                                $new_saldo->position    = JournalSaldo::POSITION['DEBET'];
                                $new_saldo->saldo       = $sum_journal;
                            } else if($sum_journal == 0) {
                                $new_saldo->position    = JournalSaldo::POSITION['BALANCE'];
                                $new_saldo->saldo       = 0;
                            } else {
                                $new_saldo->position    = JournalSaldo::POSITION['CREDIT'];
                                $new_saldo->saldo       = abs($sum_journal);
                            }
                            $new_saldo->save();
                        }
                    }

                    DB::commit();
                    $response['redirect_to'] = route('superuser.accounting.journal.show', $journal_periode->id);

                    return $this->response(200, $response);
                }
                
            } catch (\Exception $e) {
                DB::rollback();
                return $this->response(400, $response);
            }
        }
    }

    public function excel(Request $request)
    {
        $datatable = new JournalTable();
        $model = $datatable->query($request);

        // $list = \DB::select($model->toSql(), $model->getBindings());

        $filename = 'SR-' . Carbon::parse($request->start_date)->format('dmy') . '-' . Carbon::parse($request->end_date)->format('dmy') . '.xlsx';
        $header_style = (new StyleBuilder())->setFontSize(11)->setFontBold()->build();

        $rows_style = (new StyleBuilder())
            ->setFontSize(11)
            ->build();

        return (new FastExcel($this->reportsGenerator($model)))->headerStyle($header_style)
            ->rowsStyle($rows_style)->download($filename);
    }
    public function export(Request $request)
    {
        if (!Auth::guard('superuser')->user()->can('journal-print')) {
            return abort(403);
        }

        $split = explode('-', str_replace(' ', '', $request->datesearch));
        $from_date = Carbon::createFromFormat('d/m/Y', $split[0])->format('Y-m-d');
        $to_date = Carbon::createFromFormat('d/m/Y', $split[1])->format('Y-m-d');

        // ADD request date to use in datatable query
        $request->request->add(['start_date' => $from_date, 'end_date' => $to_date]);
        if ($request->download_type == 'excel') {
            return $this->excel($request);
        }

        if ($request->download_type == 'pdf') {
            return $this->pdf($request);
        }
    }

    
}
