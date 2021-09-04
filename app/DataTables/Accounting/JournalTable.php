<?php

namespace App\DataTables\Accounting;

use App\DataTables\Table;
use Carbon\Carbon;
use App\Entities\Account\Superuser;
use App\Entities\Accounting\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        
        $superuser = Superuser::find(Auth::guard('superuser')->id());
        $model = Journal::whereHas('coa', function($query) use($superuser) {
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id);
        })
        ->whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
        ->orderBy('created_at', 'ASC');

        return $model;
    }

    private function getTotal(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        
        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $journal = Journal::whereHas('coa', function($query) use($superuser) {
            $query->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id);
        })
        ->whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
        ->select(\DB::raw('SUM(debet) AS totaldebet, SUM(credit) AS totalcredit'))
        ->get();

        return $journal;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        $table->addIndexColumn();

        $table->editColumn('created_at', function (Journal $model) {
            return Carbon::parse($model->created_at)->format('j/m/Y');
        });

        $table->editColumn('coa', function (Journal $model) {
            return $model->coa->code.' / '.$model->coa->name;
        });

        $table->editColumn('transaction', function (Journal $model) {
            return $model->name;
        });

        $table->editColumn('debet', function (Journal $model) {
            return $model->debet ? 'Rp. '.number_format($model->debet, 2, ",", ".") : '';
        });

        $table->editColumn('credit', function (Journal $model) {
            return $model->credit ? 'Rp. '.number_format($model->credit, 2, ",", ".") : '';
        });

        $getTotal = $this->getTotal($request);
        $totaldebet = $getTotal->sum('totaldebet');
        $totalcredit = $getTotal->sum('totalcredit');
        $table->with([
            'totalDebet' => 'Rp. '.number_format($totaldebet, 2, ",", "."),
            'totalCredit' => 'Rp. '.number_format($totalcredit, 2, ",", "."),
            'canposting'    => ($totaldebet > 0 && $totaldebet == $totalcredit) ? 'yes' : 'no'
        ]);
        return $table->make(true);
    }
}