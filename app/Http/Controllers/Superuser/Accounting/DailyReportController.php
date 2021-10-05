<?php

namespace App\Http\Controllers\Superuser\Accounting;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Account\Superuser;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;
use DomPDF;

class DailyReportController extends Controller
{
    public function json(Request $request)
    {
        $data = [];
        $coa = $request->coa;
        $date = $request->date;
        $from = $request->from;
        $to = $request->to;
        // dd($coa);
        if($coa && $from) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            if($superuser->type) {
                // GET SALDO AWAL
                $saldo_awal_debet = Journal::where('coa_id', $coa)
                ->where('created_at', '<', $from." 00:00:00")
                ->sum('debet');

                $saldo_awal_credit = Journal::where('coa_id', $coa)
                ->where('created_at', '<', $from." 00:00:00")
                ->sum('credit');

                $balance = $saldo_awal_debet - $saldo_awal_credit;

                $data['data'][] = [
                    Carbon::parse($from)->format('j/m/Y'),
                    'Saldo Awal',
                    $balance > 0 ? 'Rp. '.number_format($balance, 2, ",", ".") : '',
                    $balance < 0 ? 'Rp. '.number_format(abs($balance), 2, ",", ".") : '',
                    $balance ? 'Rp. '.number_format($balance, 2, ",", ".") : '',
                    $balance
                ];

                $journal = Journal::where('coa_id', $coa)
                ->whereBetween('created_at', [$from." 00:00:00", $to." 23:59:59"])
                ->orderBy('created_at', 'ASC')
                ->get();

                foreach ($journal as $key => $value) {
                    $balance = $balance + $value->debet - $value->credit;
                    $data['data'][] = [
                        Carbon::parse($value->created_at)->format('j/m/Y'),
                        $value->name,
                        $value->debet ? 'Rp. '.number_format($value->debet, 2, ",", ".") : '',
                        $value->credit ? 'Rp. '.number_format($value->credit, 2, ",", ".") : '',
                        $balance ? 'Rp. '.number_format($balance, 2, ",", ".") : '',
                        $balance
                    ];
                }
                if(empty($data['data'])) {
                    $data['data'] = '';
                }
            } else {
                $data['data'] = '';
            }

            
        } else {
            // dd("sini");
            $data['data'] = '';
        }
                
        return $data;
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('daily cash/bank report-manage')) {
            return abort(403);
        }

        $data['coas'] = MasterRepo::coas_by_branch_and_group(Coa::GROUP['Aktiva']);
        
        return view('superuser.accounting.daily_report.index', $data);
    }

    public function pdf($coa = NULL, $from = NULL, $to = NULL, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('daily cash/bank report-print')) {
            return abort(403);
        }
        if ($coa == NULL OR $from == NULL) {
            abort(404);
        }

        // if(Carbon::parse($from)->format('Y-m-d') >= Carbon::now()->format('Y-m-d')) {
        //     dd(Carbon::now()->format('Y-m-d'));
        //     abort(404);
        // }

        $find_coa = Coa::find($coa);

        $data['title'] = $find_coa->code.' - '.$find_coa->name;
        $data['date'] = $from." s/d ".$to;
        
        if($coa && $from) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            if($superuser->type) {
                // GET SALDO AWAL
                $saldo_awal_debet = Journal::where('coa_id', $coa)
                ->where('created_at', '<', $from." 00:00:00")
                ->sum('debet');

                $saldo_awal_credit = Journal::where('coa_id', $coa)
                ->where('created_at', '<', $from." 00:00:00")
                ->sum('credit');

                $balance = $saldo_awal_debet - $saldo_awal_credit;

                $data['data'][] = [
                    Carbon::parse($from)->format('j/m/Y'),
                    'Saldo Awal',
                    $balance > 0 ? 'Rp. '.number_format($balance, 2, ",", ".") : '',
                    $balance < 0 ? 'Rp. '.number_format(abs($balance), 2, ",", ".") : '',
                    $balance ? 'Rp. '.number_format($balance, 2, ",", ".") : '',
                ];

                $journal = Journal::where('coa_id', $coa)
                ->whereBetween('created_at', [$from." 00:00:00", $to." 23:59:59"])
                ->orderBy('created_at', 'ASC')
                ->get();

                foreach ($journal as $key => $value) {
                    $balance = $balance + $value->debet - $value->credit;
                    $data['data'][] = [
                        Carbon::parse($value->created_at)->format('j/m/Y'),
                        $value->name,
                        $value->debet ? 'Rp. '.number_format($value->debet, 2, ",", ".") : '',
                        $value->credit ? 'Rp. '.number_format($value->credit, 2, ",", ".") : '',
                        $balance ? 'Rp. '.number_format($balance, 2, ",", ".") : '',
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

        $pdf = DomPDF::loadView('superuser.accounting.daily_report.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        // if ($protect) {
        //     $pdf->setEncryption('12345678');
        // }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }
    
}
