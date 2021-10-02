<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\JournalSaldo;
use App\Entities\Account\Superuser;
use App\Exports\BalanceSheetReportExcel;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use DomPDF;

class BalanceSheetController extends Controller
{
    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('balance sheet-manage')) {
            return abort(403);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;

        return view('superuser.report.balance_sheet.index', $data);
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('balance sheet-manage')) {
            return abort(403);
        }

        $journal_periode = JournalPeriode::find($id);
        if ($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;


        $data['collect'] = $this->grab_data($id);
        
        return view('superuser.report.balance_sheet.show', $data);
    }

    private function grab_data($id) {

        $journal_periode = JournalPeriode::find($id);

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $prev_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('id', '<', $journal_periode->id)->latest('id')->first();

        $collect = [];

        // A1
        $A1 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '11%')
            //         ->where('group', 1);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.code', 'like', '11%')
            ->where('master_coa.group', 1)
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();
        
        $collect['A1'] = [];
        foreach ($A1 as $item) {
            $collect['A1'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['A1'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['A1'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['A1_TOTAL'] = array_key_exists('A1', $collect) ? array_sum(array_column($collect['A1'], 'saldo')) : 0;

        // A2
        $A2 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '12.01%')
            //         ->orWhere('code', 'like', '12.02%')
            //         ->where('group', 1);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.group', 1)
            ->where(function ($query) {
                $query->where('master_coa.code', 'like', '12.01%')
                ->orWhere('master_coa.code', 'like', '12.02%');
            })
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();

        $collect['A2'] = [];    
        foreach ($A2 as $item) {
            $collect['A2'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['A2'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['A2'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['A2_TOTAL'] = array_key_exists('A2', $collect) ? array_sum(array_column($collect['A2'], 'saldo')) : 0;

        // A3
        $A3 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '12.03%')
            //         ->where('group', 1);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.code', 'like', '12.03%')
            ->where('master_coa.group', 1)
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();

        $collect['A3'] = [];    
        foreach ($A3 as $item) {
            $collect['A3'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['A3'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['A3'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['A3_TOTAL'] = array_key_exists('A3', $collect) ? array_sum(array_column($collect['A3'], 'saldo')) : 0;

        // A4
        $A4 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '13%')
            //         ->orWhere('code', 'like', '14%')
            //         ->where('group', 1);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.group', 1)
            ->where(function ($query) {
                $query->where('master_coa.code', 'like', '13%')
                ->orWhere('master_coa.code', 'like', '14%');
            })
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();
        
        $collect['A4'] = [];
        foreach ($A4 as $item) {
            $collect['A4'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['A4'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['A4'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['A4_TOTAL'] = array_key_exists('A4', $collect) ? array_sum(array_column($collect['A4'], 'saldo')) : 0;

        // P1
        $P1 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '21%')
            //         ->where('group', 2);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.code', 'like', '21%')
            ->where('master_coa.group', 2)
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();

        $collect['P1'] = [];    
        foreach ($P1 as $item) {
            $collect['P1'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['P1'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['P1'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['P1_TOTAL'] = array_key_exists('P1', $collect) ? array_sum(array_column($collect['P1'], 'saldo')) : 0;

        // P2
        $P2 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '22%')
            //         ->where('group', 2);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.code', 'like', '22%')
            ->where('master_coa.group', 2)
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();
        
        $collect['P2'] = [];
        foreach ($P2 as $item) {
            $collect['P2'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['P2'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['P2'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['P2_TOTAL'] = array_key_exists('P2', $collect) ? array_sum(array_column($collect['P2'], 'saldo')) : 0;

        // P3
        $P3 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '31%')
            //         ->orWhere('code', 'like', '32%')
            //         ->where('group', 3);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.group', 3)
            ->where(function($query) {
                $query->where('master_coa.code', 'like', '31%')
                ->orWhere('master_coa.code', 'like', '32%');
            })
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();
         
        $collect['P3'] = [];
        foreach ($P3 as $item) {
            $collect['P3'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['P3'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['P3'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['P3_TOTAL'] = array_key_exists('P3', $collect) ? array_sum(array_column($collect['P3'], 'saldo')) : 0;

        // P4
        $P4 = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->leftJoin('master_coa', function ($join) {
                $join->on('journal.coa_id', '=', 'master_coa.id');
            })
            // ->whereHas('coa', function ($query) use ($superuser) {
            //     $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
            //         ->where('code', 'like', '33%')
            //         ->where('group', 3);
            // })
            ->whereBetween('journal.created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->where('master_coa.type', $superuser->type)->where('master_coa.branch_office_id', $superuser->branch_office_id)
            ->where('master_coa.code', 'like', '33%')
            ->where('master_coa.group', 3)
            ->orderBy('master_coa.code', 'ASC')
            ->groupBy('coa_id')
            ->get();
            
        $collect['P4'] = [];
        foreach ($P4 as $item) {
            $collect['P4'][$item->coa_id] = [
                'name' => $item->coa->code.'/'. $item->coa->name,
                'saldo' => $item->total_debet - $item->total_credit,
            ];

            if ($prev_periode) {
                $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $item->coa_id)->first();
                if ($journal_saldo) {
                    if ($journal_saldo->position == 1) {
                        $collect['P4'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit + $journal_saldo->saldo;
                    } else {
                        $collect['P4'][$item->coa_id]['saldo'] = $item->total_debet - $item->total_credit - $journal_saldo->saldo;
                    }
                }
            }
        }

        $collect['P4_TOTAL'] = array_key_exists('P4', $collect) ? array_sum(array_column($collect['P4'], 'saldo')) : 0;

        // ADDED SPACE ROW
        $count_A_row = count($collect['A1']) + count($collect['A2']) + count($collect['A3']) + count($collect['A4']);
        $count_P_row = count($collect['P1']) + count($collect['P2']) + count($collect['P3']) + count($collect['P4']);
        if($count_A_row > $count_P_row) {
            $collect['A_SPACE'] = 0;
            $collect['P_SPACE'] = $count_A_row - $count_P_row;
        } else if($count_A_row < $count_P_row) {
            $collect['A_SPACE'] = $count_P_row - $count_A_row;
            $collect['P_SPACE'] = 0;
        } else {
            $collect['A_SPACE'] = 0;
            $collect['P_SPACE'] = 0;
        }

        
        $profit_loss = app('App\Http\Controllers\Superuser\Report\ProfitLossReportController')->grab_data($id);
        $collect['PL_NOW'] = $profit_loss['laba_bersih'];

        $collect['PL_PREV'] = 0;
        if ($prev_periode) {
            $profit_loss = app('App\Http\Controllers\Superuser\Report\ProfitLossReportController')->grab_data($prev_periode->id);
            $collect['PL_PREV'] = $profit_loss['laba_bersih'];
        }
        
        return $collect;
    }

    public function export($id = null, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('balance sheet-print')) {
            return abort(403);
        }

        if ($id == null) {
            abort(404);
        }

        $journal_periode = JournalPeriode::find($id);
        if ($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $data['collect'] = $this->grab_data($id);
        
        return Excel::download(new grab_data($id), 'BS.xlsx');
    }

    public function pdf($id = null, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('balance sheet-print')) {
            return abort(403);
        }

        if ($id == null) {
            abort(404);
        }

        $journal_periode = JournalPeriode::find($id);
        if ($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $data['collect'] = $this->grab_data($id);

        $pdf = DomPDF::loadView('superuser.report.balance_sheet.pdf', $data);
        $pdf->setOptions(['enable_javascript' => true]);
        $pdf->setPaper('a4', 'portrait');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function excel($id = null, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('balance sheet-print')) {
            return abort(403);
        }

        if ($id == null) {
            abort(404);
        }

        $journal_periode = JournalPeriode::find($id);
        if ($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $data['collect'] = $this->grab_data($id);

        $fileName = "Balance Sheet ".$journal_periode->from_date." sd ".$journal_periode->to_date;

        Excel::store($fileName, function($excel) {
            $excel->sheet('Sheetname', function($sheet) {

                $excel->sheet('Sheetname', function($sheet) {
                    $sheet->mergeCells('A1:G1');
                    $sheet->cell('A1', function($cell) {
                        $cell->setValue('Balance Sheet');
                        $cell->setFontSize(18);
                        $cell->setFontWeight('bold');
                        $cell->setAlignment('center');
                    });
                    $sheet->cell('A1', function($cell) {
                        $cell->setValue($journal_periode->from_date." s/d ".$journal_periode->to_date);
                        $cell->setFontSize(18);
                        $cell->setAlignment('center');
                        // $cells->setFontWeight('bold');
                    });
                    // Sheet manipulation
            
                });
        
            });
        
        })->export('xlsx');

        // return (new BalanceSheetReportExcel($data))->download(str_replace("/", "", $fileName).'.xlsx');
        // $pdf = DomPDF::loadView('superuser.report.balance_sheet.pdf', $data);
        // $pdf->setOptions(['enable_javascript' => true]);
        // $pdf->setPaper('a4', 'portrait');

        // if ($protect) {
        //     $pdf->setEncryption('12345678');
        // }

        // if ($generate) {
        //     return $pdf;
        // }

        // return $pdf->stream();
    }
}
