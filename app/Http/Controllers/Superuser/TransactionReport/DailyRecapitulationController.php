<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Account\Superuser;
use App\Entities\Sale\SalesOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DomPDF;
use Validator;

class DailyRecapitulationController extends Controller
{
    public function json(Request $request)
    {
        $data = [];
        $date = $request->date;

        if ($date) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            if ($superuser->type) {
                $coas = MasterRepo::coas_by_branch_and_group(Coa::GROUP['Aktiva']);

                foreach ($coas as $coa) {
                    $saldo_awal = Journal::where('coa_id', $coa->id)
                        ->where('created_at', '<', $date . " 00:00:00")
                        ->get();

                    $saldo_awal = $saldo_awal->sum('debet') - $saldo_awal->sum('credit');

                    $journal = Journal::where('coa_id', $coa->id)
                        ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                        ->get();

                    $journal_debet = $journal->sum('debet');
                    $journal_credit = $journal->sum('credit');
                    $ending_balance = $saldo_awal + $journal_debet - $journal_credit;

                    $data['data'][] = [
                        $coa->code,
                        $coa->name,
                        'Rp. ' . number_format($saldo_awal, 2, ',', '.'),
                        'Rp. ' . number_format($journal_debet, 2, ',', '.'),
                        'Rp. ' . number_format($journal_credit, 2, ',', '.'),
                        'Rp. ' . number_format($ending_balance, 2, ',', '.'),  
                    ];
                    
                }
            }
            if (empty($data['data'])) {
                $data['data'] = '';
            }
        } else {
            $data['data'] = '';
        }

        return $data;
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('daily cash/bank recapitulation-manage')) {
            return abort(403);
        }

        $data['coas'] = MasterRepo::coas_by_branch_and_group(Coa::GROUP['Aktiva']);

        return view('superuser.transaction_report.daily_recapitulation.index', $data);
    }

    public function pdf(Request $request)
    {
        if(!Auth::guard('superuser')->user()->can('daily cash/bank recapitulation-print')) {
            return abort(403);
        }

        $validator = Validator::make($request->all(), [
            'coa' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        $coa = $request->coa;
        $date = $request->date;

        if($coa == 'all') {
            $coas = MasterRepo::coas_by_branch_and_group(Coa::GROUP['Aktiva']);

            foreach ($coas as $item) {
                $saldo_awal = Journal::select('debet', 'credit')->where('coa_id', $item->id)
                    ->where('created_at', '<', $date . " 00:00:00")
                    ->get();
                    
                $saldo_awal = $saldo_awal->sum('debet') - $saldo_awal->sum('credit');

                $journal = Journal::where('coa_id', $item->id)
                    ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                    ->get();

                $journal_debet = $journal->sum('debet');
                $journal_credit = $journal->sum('credit');
                $ending_balance = $saldo_awal + $journal_debet - $journal_credit;

                $data['data'][] = [
                    $item->code,
                    $item->name,
                    $saldo_awal,
                    $journal_debet,
                    $journal_credit,
                    $ending_balance,
                ];
                
            }
        } else {
            $saldo_awal = Journal::where('coa_id', $coa)
                ->where('created_at', '<', $date . " 00:00:00")
                ->get();

            $saldo_awal = $saldo_awal->sum('debet') - $saldo_awal->sum('credit');

            $journal = Journal::where('coa_id', $coa)
                ->whereBetween('created_at', [$date . " 00:00:00", $date . " 23:59:59"])
                ->get();

            $journal_debet = $journal->sum('debet');
            $journal_credit = $journal->sum('credit');
            $ending_balance = $saldo_awal + $journal_debet - $journal_credit;

            $_coa = Coa::find($coa);

            $data['data'][] = [
                $_coa->code,
                $_coa->name,
                $saldo_awal,
                $journal_debet,
                $journal_credit,
                $ending_balance,
            ];
        }
    

        if ($coa == 'all') {
            $coa_text = 'All';
        } else {
            $_coa = Coa::find($coa);
            $coa_text = $_coa->code.' - '.$_coa->name;
        }

        $data['coa_text'] = $coa_text;

        $data['date'] = $date;

        $pdf = DomPDF::loadView('superuser.transaction_report.daily_recapitulation.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream();
    }

}
