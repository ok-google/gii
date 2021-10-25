<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Entities\Accounting\CashFlowSaldo;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Account\Superuser;
use App\Exports\CashFlowReportExcel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use DomPDF;
use DB;

class CashFlowReportController extends Controller
{
    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('cash flow-manage')) {
            return abort(403);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;

        return view('superuser.report.cash_flow_report.index', $data);
    }

    public function show(Request $request, $id)
    {
        if (!Auth::guard('superuser')->user()->can('cash flow-manage')) {
            return abort(403);
        }

        $journal_periode = JournalPeriode::find($id);

        if ($journal_periode == null) {
            return abort(404);
        }

        $coa = $request->coa;
        $data['coas'] = MasterRepo::coas_by_branch();
        $data['coa'] = $coa;

        $data['journal_periode'] = $journal_periode;

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;

        $data['report'] = $this->grab_data($id,$coa);

        return view('superuser.report.cash_flow_report.show', $data);
    }

    public function grab_data($periode_id = null,$coa=null)
    {
        if ($periode_id == null) {
            abort(404);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal_periode = JournalPeriode::find($periode_id);

        $cash_flow = CashFlowSaldo::where('periode_id', $periode_id)->first();

        $exCoa = explode(",",$coa);
        $A = $cash_flow->beginning_balance;

        $data['A'] = $A;

        $B = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('coa', function ($query) use ($superuser) {
                $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)
                    ->where('group', 1)
                    ->where(function ($query2) {
                        $query2->where('code', 'like', '11.01%')->orWhere('code', 'like', '11.02%');
                    });
            })
            ->whereBetween('created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"]);
            
        if($coa != "all" && $coa != null){
            $B = $B->whereIn("coa_id",$exCoa);
        }

        $B = $B->orderBy('coa_id', 'ASC')
            ->groupBy('coa_id')
            ->get();

        $data['B'] = $B;
        
        $C = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('coa', function ($query) use ($superuser) {
                $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('group', 5);
            })
            ->whereBetween('created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"]);
             
        if($coa != "all" && $coa != null){
            $C = $C->whereIn("coa_id",$exCoa);
        }
        $C = $C->orderBy('coa_id', 'ASC')
            ->groupBy('coa_id')
            ->get();

        $data['C'] = $C;

        $D = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
            ->whereHas('coa', function ($query) use ($superuser) {
                $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('group', 7);
            })
            ->whereBetween('created_at', [$journal_periode->from_date . " 00:00:00", $journal_periode->to_date . " 23:59:59"])
            ->orderBy('coa_id', 'ASC')
            ->groupBy('coa_id')
            ->get();

        $data['D'] = $D;

        $E = $A + ($B->sum('total_debet') - $B->sum('total_credit')) - ($C->sum('total_debet') - $C->sum('total_credit')) - ($D->sum('total_debet') - $D->sum('total_credit'));
        $data['E'] = $E;

        $F = $A - $E;
        $data['F'] = $F;

        return $data;
    }

    public function pdf(Request $request, $id = null, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('cash flow-print')) {
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

        $data['report'] = $this->grab_data($id,$coa);

        $pdf = DomPDF::loadView('superuser.report.cash_flow_report.pdf', $data);
        $pdf->setOptions(['enable_php' => true]);
        $pdf->setPaper('a4', 'portrait');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }


    public function excel(Request $request, $id = null, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('cash flow-print')) {
            return abort(403);
        }

        if ($id == null) {
            abort(404);
        }

        $coa = $request->coa;
        $data['coas'] = MasterRepo::coas_by_branch();
        $data['coa'] = $coa;

        $journal_periode = JournalPeriode::find($id);
        if ($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $data['report'] = $this->grab_data($id,$coa);
        
        $fileName = "Cash Flow ".$journal_periode->from_date." sd ".$journal_periode->to_date;

        return (new CashFlowReportExcel($data))->download(str_replace("/", "", $fileName).'.xlsx');
    }
}
