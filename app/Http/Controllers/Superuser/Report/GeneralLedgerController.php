<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\JournalSaldo;
use App\Entities\Account\Superuser;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;
use DomPDF;

class GeneralLedgerController extends Controller
{
    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('general ledger-manage')) {
            return abort(403);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;
        
        return view('superuser.report.general_ledger.index', $data);
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('general ledger-manage')) {
            return abort(403);
        }

        $journal_periode = JournalPeriode::find($id);
        if($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $superuser = Superuser::find(Auth::guard('superuser')->id());
        
        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;

        $prev_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('id', '<', $journal_periode->id)->latest('id')->first();

        $coas = MasterRepo::coas_by_branch();
        foreach ($coas as $coa) {
            $is_skip = false;
            $total_debet = 0;
            $total_credit = 0;

            $general_ledger[$coa->id]['title'] = $coa->code.' - '.$coa->name;

            if($coa->group != 5) {
                if($prev_periode) {
                    $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $coa->id)->first();
                    if($journal_saldo) {
                        $total_debet = $journal_saldo->position == 1 ? $journal_saldo->saldo : 0;
                        $total_credit = $journal_saldo->position == 0 ? $journal_saldo->saldo : 0;
                    }
                }

                $general_ledger[$coa->id]['data'][] = [
                    'date'  => Carbon::parse($journal_periode->from_date)->format('j/m/Y'),
                    'name'  => 'Saldo Awal',
                    'debet' => 'Rp. '.number_format($total_debet, 2, ",", "."),
                    'credit'=> 'Rp. '.number_format($total_credit, 2, ",", "."),
                ];

                if($total_debet == 0 AND $total_credit == 0) {
                    $is_skip = true;
                }
            }

            $journals = Journal::where('coa_id', $coa->id)
                ->whereHas('coa', function($query) {
                    $superuser = Superuser::find(Auth::guard('superuser')->id());
                    $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id);
                })
                ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
                ->orderBy('created_at', 'ASC')
                ->get();
            if(count($journals) > 0) {
                $is_skip = false;
            } else {
                $is_skip = true;
            }

            foreach ($journals as $item) {
                $general_ledger[$coa->id]['data'][] = [
                    'date'  => Carbon::parse($item->created_at)->format('j/m/Y'),
                    'name'  => $item->name,
                    'debet' => $item->debet ? 'Rp. '.number_format($item->debet, 2, ",", ".") : '',
                    'credit'=> $item->credit ? 'Rp. '.number_format($item->credit, 2, ",", ".") : '',
                ];
                $total_debet = $total_debet + $item->debet;
                $total_credit = $total_credit + $item->credit;
            }

            $general_ledger[$coa->id]['total']['debet'] = 'Rp. '.number_format($total_debet, 2, ",", ".");
            $general_ledger[$coa->id]['total']['credit'] = 'Rp. '.number_format($total_credit, 2, ",", ".");
            $saldo_akhir = $total_debet - $total_credit;
            if($saldo_akhir > 0) {
                $general_ledger[$coa->id]['saldoakhir']['debet'] = 'Rp. '.number_format($saldo_akhir, 2, ",", ".");
                $general_ledger[$coa->id]['saldoakhir']['credit'] = 'Rp. '.number_format(0, 2, ",", ".");
            } else {
                $general_ledger[$coa->id]['saldoakhir']['debet'] = 'Rp. '.number_format(0, 2, ",", ".");
                $general_ledger[$coa->id]['saldoakhir']['credit'] = 'Rp. '.number_format(abs($saldo_akhir), 2, ",", ".");
            }

            // REMOVE EMPTY
            if($is_skip) {
                unset($general_ledger[$coa->id]);
            }
        }
        
        $data['general_ledger'] = $general_ledger;

        return view('superuser.report.general_ledger.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('general ledger-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $journal_periode = JournalPeriode::find($id);
        if($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $prev_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('id', '<', $journal_periode->id)->latest('id')->first();

        $coas = MasterRepo::coas_by_branch();
        foreach ($coas as $coa) {
            $is_skip = false;
            $total_debet = 0;
            $total_credit = 0;

            $general_ledger[$coa->id]['title'] = $coa->code.' - '.$coa->name;

            if($coa->group != 5) {
                if($prev_periode) {
                    $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $coa->id)->first();
                    if($journal_saldo) {
                        $total_debet = $journal_saldo->position == 1 ? $journal_saldo->saldo : 0;
                        $total_credit = $journal_saldo->position == 0 ? $journal_saldo->saldo : 0;
                    }
                }

                $general_ledger[$coa->id]['data'][] = [
                    'date'  => Carbon::parse($journal_periode->from_date)->format('j/m/Y'),
                    'name'  => 'Saldo Awal',
                    'debet' => 'Rp. '.number_format($total_debet, 2, ",", "."),
                    'credit'=> 'Rp. '.number_format($total_credit, 2, ",", "."),
                ];

                if($total_debet == 0 AND $total_credit == 0) {
                    $is_skip = true;
                }
            }

            $journals = Journal::where('coa_id', $coa->id)
                ->whereHas('coa', function($query) {
                    $superuser = Superuser::find(Auth::guard('superuser')->id());
                    $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id);
                })
                ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
                ->orderBy('created_at', 'ASC')
                ->get();
            if(count($journals) > 0) {
                $is_skip = false;
            } else {
                $is_skip = true;
            }

            foreach ($journals as $item) {
                $general_ledger[$coa->id]['data'][] = [
                    'date'  => Carbon::parse($item->created_at)->format('j/m/Y'),
                    'name'  => $item->name,
                    'debet' => $item->debet ? 'Rp. '.number_format($item->debet, 2, ",", ".") : '',
                    'credit'=> $item->credit ? 'Rp. '.number_format($item->credit, 2, ",", ".") : '',
                ];
                $total_debet = $total_debet + $item->debet;
                $total_credit = $total_credit + $item->credit;
            }

            $general_ledger[$coa->id]['total']['debet'] = 'Rp. '.number_format($total_debet, 2, ",", ".");
            $general_ledger[$coa->id]['total']['credit'] = 'Rp. '.number_format($total_credit, 2, ",", ".");
            $saldo_akhir = $total_debet - $total_credit;
            if($saldo_akhir > 0) {
                $general_ledger[$coa->id]['saldoakhir']['debet'] = 'Rp. '.number_format($saldo_akhir, 2, ",", ".");
                $general_ledger[$coa->id]['saldoakhir']['credit'] = 'Rp. '.number_format(0, 2, ",", ".");
            } else {
                $general_ledger[$coa->id]['saldoakhir']['debet'] = 'Rp. '.number_format(0, 2, ",", ".");
                $general_ledger[$coa->id]['saldoakhir']['credit'] = 'Rp. '.number_format(abs($saldo_akhir), 2, ",", ".");
            }

            // REMOVE EMPTY
            if($is_skip) {
                unset($general_ledger[$coa->id]);
            }
        }
        
        $data['general_ledger'] = $general_ledger;

        $pdf = DomPDF::loadView('superuser.report.general_ledger.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }
    
}
