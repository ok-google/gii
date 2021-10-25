<?php

namespace App\Http\Controllers\Superuser\Report;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\JournalSaldo;
use App\Entities\Accounting\SettingProfitLoss;
use App\Repositories\MasterRepo;
use App\Entities\Account\Superuser;
use App\Exports\ProfitLossReportExcel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;
use DomPDF;

class ProfitLossReportController extends Controller
{
    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('profit loss-manage')) {
            return abort(403);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;
        
        return view('superuser.report.profit_loss_report.index', $data);
    }

    public function show(Request $request, $id)
    {
        if(!Auth::guard('superuser')->user()->can('profit loss-manage')) {
            return abort(403);
        }

        // dd($request->coa);
        $coa = $request->coa;
        $data['coas'] = MasterRepo::coas_by_branch();
        $data['coa'] = $coa;
        $journal_periode = JournalPeriode::find($id);
        $data['journal_periode'] = $journal_periode;

        $superuser = Superuser::find(Auth::guard('superuser')->id());
        
        $journal_periodes = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->orderBy('id', 'DESC')->get();
        $data['journal_periodes'] = $journal_periodes;

        $data['report'] = $this->grab_data($id,$coa);
        
        return view('superuser.report.profit_loss_report.show', $data);
    }

    public function grab_data( $periode_id = NULL,$coa=null ) {
        if ($periode_id == NULL) {
            abort(404);
        }

        $journal_periode = JournalPeriode::find($periode_id);
        if($journal_periode == null) {
            return abort(404);
        }

        $exCoa = explode(",",$coa);
        $A = Journal::whereHas('coa', function($query){
            $superuser = Superuser::find(Auth::guard('superuser')->id());

            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('code', 'like', '41.01%')->where('group', 4);
        })
        ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"]);


        $A = $A->orderBy('created_at', 'ASC')
        ->sum('credit');

        $data['A'] = $A;


        $B = Journal::whereHas('coa', function($query) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('code', 'like', '41.02%')->where('group', 4);
        })
        ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
        ->orderBy('created_at', 'ASC')
        ->sum('debet');

        $data['B'] = $B;


        $C = Journal::whereHas('coa', function($query) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('code', 'like', '41.03%')->where('group', 4);
        })
        ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
        ->orderBy('created_at', 'ASC')
        ->sum('debet');

        $data['C'] = $C;

        $data['D'] = $A - $B - $C;

        $E = Journal::whereHas('coa', function($query) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('code', 'like', '51.01%')->where('group', 6);
        })
        ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
        ->orderBy('created_at', 'ASC')
        ->get();

        $data['E'] = $E->sum('debet') - $E->sum('credit');
        
        $data['laba_kotor'] = $data['D'] - $data['E'];


        $F = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
        ->whereHas('coa', function($query) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('code', 'like', '6%')->where('group', 5);
        })
        ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"]);
        
        if($coa != "all" && $coa != null){
            $F = $F->whereIn("coa_id",$exCoa);
        }

        $F = $F->orderBy('coa_id', 'ASC')
        ->groupBy('coa_id')
        ->get();

        $data['F'] = $F;


        $G = $F->sum('total_debet');

        $data['G'] = $G;

        $H = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
        ->whereHas('coa', function($query) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('code', 'like', '7%')->where('group', 5);
        })
        ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"]);
        
        if($coa != "all" && $coa != null){
            $H = $H->whereIn("coa_id",$exCoa);
        }
        $H = $H->orderBy('coa_id', 'ASC')
        ->groupBy('coa_id')
        ->get();

        $data['H'] = $H;

        $I = $H->sum('total_debet');

        $data['I'] = $I;

        $data['J'] = $G + $I;
        $data['M'] = $data['laba_kotor'] - $data['J'];


        $K = Journal::select('coa_id', DB::raw('SUM(debet) as total_debet'), DB::raw('SUM(credit) as total_credit'))
        ->whereHas('coa', function($query) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('code', 'like', '8%')->where('group', 4);
        })
        ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"]);
        
        if($coa != "all" && $coa != null){
            $K = $K->whereIn("coa_id",$exCoa);
        }

        $K = $K->orderBy('coa_id', 'ASC')
        ->groupBy('coa_id')
        ->get();

        $data['K'] = $K;

        $L = $K->sum('total_debet');

        $data['L'] = $L;

        $data['laba_bersih'] = $data['M'] + $L;
        
        return $data;
    }

    public function pdf(Request $request, $id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('profit loss-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $coa = $request->coa;
        $journal_periode = JournalPeriode::find($id);
        if($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;

        $data['report'] = $this->grab_data($id,$coa);
        
        $pdf = DomPDF::loadView('superuser.report.profit_loss_report.pdf', $data);
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

    
    public function excel(Request $request, $id = NULL, $protect = false, $generate = false)
    {
        if(!Auth::guard('superuser')->user()->can('profit loss-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }
        $coa = $request->coa;
        $journal_periode = JournalPeriode::find($id);
        if($journal_periode == null) {
            return abort(404);
        }

        $data['journal_periode'] = $journal_periode;
        $data['report'] = $this->grab_data($id,$coa);
        
        $fileName = "Profit Loss Report ".$journal_periode->from_date." sd ".$journal_periode->to_date;
        // dd($fileName);

        // $pdf = DomPDF::loadView('superuser.report.profit_loss_report.pdf', $data);
        // $pdf->setOptions(['enable_php' => true]);
        // $pdf->setPaper('a4', 'portrait');

        return (new ProfitLossReportExcel($data))->download(str_replace("/", "", $fileName).'.xlsx');
    }

    // WITH SETTING

    // public function pdf($id = NULL, $protect = false, $generate = false)
    // {
    //     if(!Auth::guard('superuser')->user()->can('profit loss-print')) {
    //         return abort(403);
    //     }

    //     if ($id == NULL) {
    //         abort(404);
    //     }

    //     $journal_periode = JournalPeriode::find($id);
    //     if($journal_periode == null) {
    //         return abort(404);
    //     }

    //     $data['journal_periode'] = $journal_periode;

    //     $superuser = Superuser::find(Auth::guard('superuser')->id());


    //     $profit_loss = [];

    //     foreach (SettingProfitLoss::KEY as $value) {
    //         $setting_profit_loss = SettingProfitLoss::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', $value)->first();

    //         switch ($value) {
    //             case 'a':
    //                 if($setting_profit_loss != null) {
    //                     $journals = Journal::where('coa_id', $setting_profit_loss->value)
    //                     ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
    //                     ->orderBy('created_at', 'ASC')
    //                     ->get();
    //                     $total_debet = $total_credit = 0;
    //                     foreach ($journals as $item) {
    //                         $total_debet = $total_debet + $item->debet;
    //                         $total_credit = $total_credit + $item->credit;
    //                     }
    //                     $total = $total_debet - $total_credit;

    //                     $coa = Coa::find($setting_profit_loss->value);
    //                     $profit_loss['a']['name'] = $coa->name;
    //                     $profit_loss['a']['value'] = $total;
    //                 }
    //                 break;
    //             case 'b':
    //                 if($setting_profit_loss != null) {
    //                     $coa_ids = unserialize($setting_profit_loss->value);
    //                     $pendapatan_penjualan_bersih = $profit_loss['a']['value'];
    //                     foreach ($coa_ids as $coa_id) {
    //                         $journals = Journal::where('coa_id', $coa_id)
    //                         ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
    //                         ->orderBy('created_at', 'ASC')
    //                         ->get();
    //                         $total_debet = $total_credit = 0;
    //                         foreach ($journals as $item) {
    //                             $total_debet = $total_debet + $item->debet;
    //                             $total_credit = $total_credit + $item->credit;
    //                         }
    //                         $total = $total_debet - $total_credit;

    //                         $coa = Coa::find($coa_id);
    //                         $profit_loss['b'][] = ['name' => $coa->name, 'value' => $total];

    //                         $pendapatan_penjualan_bersih = $pendapatan_penjualan_bersih - $total;
    //                     }
    //                     $profit_loss['pendapatan_penjualan_bersih'] = $pendapatan_penjualan_bersih;
    //                 }
    //                 break;
    //             case 'c':
    //                 if($setting_profit_loss != null) {
    //                     $prev_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('id', '<', $journal_periode->id)->latest('id')->first();

    //                     $persediaan_awal = 0;

    //                     if($prev_periode) {
    //                         $journal_saldo = JournalSaldo::where('periode_id', $prev_periode->id)->where('coa_id', $setting_profit_loss->value)->first();

    //                         if($journal_saldo) {
    //                             if($journal_saldo->position == 1) {
    //                                 $persediaan_awal = $journal_saldo->saldo;
    //                             } else if ($journal_saldo->position == 0) {
    //                                 $persediaan_awal = -$journal_saldo->saldo;
    //                             }
    //                         }
    //                     }
    //                     $profit_loss['c']['persediaan_awal'] = $persediaan_awal;

    //                     $persediaan_akhir = 0;
    //                     $journal_saldo = JournalSaldo::where('periode_id', $journal_periode->id)->where('coa_id', $setting_profit_loss->value)->first();
    //                     if($journal_saldo) {
    //                         if($journal_saldo->position == 1) {
    //                             $persediaan_akhir = $journal_saldo->saldo;
    //                         } else if ($journal_saldo->position == 0) {
    //                             $persediaan_akhir = -$journal_saldo->saldo;
    //                         }
    //                     }
    //                     $profit_loss['c']['persediaan_akhir'] = $persediaan_akhir;
    //                 }
    //                 break;
    //             case 'd':
    //                 if($setting_profit_loss != null) {
    //                     $coa_ids = unserialize($setting_profit_loss->value);
    //                     $pendapatan_penjualan_bersih = $profit_loss['c']['persediaan_awal'];
    //                     foreach ($coa_ids as $coa_id) {
    //                         $journals = Journal::where('coa_id', $coa_id)
    //                         ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
    //                         ->orderBy('created_at', 'ASC')
    //                         ->get();
    //                         $total_debet = $total_credit = 0;
    //                         foreach ($journals as $item) {
    //                             $total_debet = $total_debet + $item->debet;
    //                             $total_credit = $total_credit + $item->credit;
    //                         }
    //                         $total = $total_debet - $total_credit;

    //                         $coa = Coa::find($coa_id);
    //                         $profit_loss['d'][] = ['name' => $coa->name, 'value' => $total];

    //                         $pendapatan_penjualan_bersih = $pendapatan_penjualan_bersih + $total;
    //                     }
    //                     $profit_loss['hpp_pendapatan_penjualan_bersih'] = $pendapatan_penjualan_bersih;

    //                     $profit_loss['hpp'] = $profit_loss['hpp_pendapatan_penjualan_bersih'] - $profit_loss['c']['persediaan_akhir'];
    //                 }
    //                 break;
    //             case 'e':
    //                 if($setting_profit_loss != null) {
    //                     $coa_ids = unserialize($setting_profit_loss->value);
    //                     $beban_penjualan = 0;
    //                     foreach ($coa_ids as $coa_id) {
    //                         $journals = Journal::where('coa_id', $coa_id)
    //                         ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
    //                         ->orderBy('created_at', 'ASC')
    //                         ->get();
    //                         $total_debet = $total_credit = 0;
    //                         foreach ($journals as $item) {
    //                             $total_debet = $total_debet + $item->debet;
    //                             $total_credit = $total_credit + $item->credit;
    //                         }
    //                         $total = $total_debet - $total_credit;

    //                         $coa = Coa::find($coa_id);
    //                         $profit_loss['e'][] = ['name' => $coa->name, 'value' => $total];

    //                         $beban_penjualan = $beban_penjualan + $total;
    //                     }
    //                     $profit_loss['beban_penjualan'] = $beban_penjualan;
    //                 }
    //                 break;
    //             case 'f':
    //                 if($setting_profit_loss != null) {
    //                     $coa_ids = unserialize($setting_profit_loss->value);
    //                     $beban_administrasi = 0;
    //                     foreach ($coa_ids as $coa_id) {
    //                         $journals = Journal::where('coa_id', $coa_id)
    //                         ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
    //                         ->orderBy('created_at', 'ASC')
    //                         ->get();
    //                         $total_debet = $total_credit = 0;
    //                         foreach ($journals as $item) {
    //                             $total_debet = $total_debet + $item->debet;
    //                             $total_credit = $total_credit + $item->credit;
    //                         }
    //                         $total = $total_debet - $total_credit;

    //                         $coa = Coa::find($coa_id);
    //                         $profit_loss['f'][] = ['name' => $coa->name, 'value' => $total];

    //                         $beban_administrasi = $beban_administrasi + $total;
    //                     }
    //                     $profit_loss['beban_administrasi'] = $beban_administrasi;

    //                     $profit_loss['total_beban_operasional'] = $profit_loss['beban_penjualan'] + $profit_loss['beban_administrasi'];

    //                     $profit_loss['laba_operasional'] = $profit_loss['pendapatan_penjualan_bersih'] - $profit_loss['hpp'] - $profit_loss['total_beban_operasional'];
    //                 }
    //                 break;
    //             case 'g':
    //                 if($setting_profit_loss != null) {
    //                     $coa_ids = unserialize($setting_profit_loss->value);
    //                     $g_total = 0;
    //                     foreach ($coa_ids as $coa_id) {
    //                         $journals = Journal::where('coa_id', $coa_id)
    //                         ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
    //                         ->orderBy('created_at', 'ASC')
    //                         ->get();
    //                         $total_debet = $total_credit = 0;
    //                         foreach ($journals as $item) {
    //                             $total_debet = $total_debet + $item->debet;
    //                             $total_credit = $total_credit + $item->credit;
    //                         }
    //                         $total = $total_debet - $total_credit;

    //                         $coa = Coa::find($coa_id);
    //                         $profit_loss['g'][] = ['name' => $coa->name, 'value' => $total];

    //                         $g_total = $g_total + $total;
    //                     }
    //                     $profit_loss['g_total'] = $g_total;
    //                 }
    //                 break;
    //             case 'h':
    //                 if($setting_profit_loss != null) {
    //                     $coa_ids = unserialize($setting_profit_loss->value);
    //                     $h_total = 0;
    //                     foreach ($coa_ids as $coa_id) {
    //                         $journals = Journal::where('coa_id', $coa_id)
    //                         ->whereBetween('created_at', [$journal_periode->from_date." 00:00:00", $journal_periode->to_date." 23:59:59"])
    //                         ->orderBy('created_at', 'ASC')
    //                         ->get();
    //                         $total_debet = $total_credit = 0;
    //                         foreach ($journals as $item) {
    //                             $total_debet = $total_debet + $item->debet;
    //                             $total_credit = $total_credit + $item->credit;
    //                         }
    //                         $total = $total_debet - $total_credit;

    //                         $coa = Coa::find($coa_id);
    //                         $profit_loss['h'][] = ['name' => $coa->name, 'value' => $total];

    //                         $h_total = $h_total + $total;
    //                     }
    //                     $profit_loss['h_total'] = $h_total;
    //                 }
    //                 break;
    //             default:
    //                 # code...
    //                 break;
    //         }
    //     }
    //     $profit_loss['laba_bersih'] = $profit_loss['laba_operasional'] - $profit_loss['g_total'] - $profit_loss['h_total'];

    //     $data['profit_loss'] = $profit_loss;
        
    //     $pdf = DomPDF::loadView('superuser.report.profit_loss_report.pdf', $data);
    //     $pdf->setPaper('a4', 'landscape');

    //     if ($protect) {
    //         $pdf->setEncryption('12345678');
    //     }

    //     if ($generate) {
    //         return $pdf;
    //     }

    //     return $pdf->stream();
    // }
    
}
