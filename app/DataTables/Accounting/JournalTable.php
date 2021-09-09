<?php

namespace App\DataTables\Accounting;

use App\DataTables\Table;
use App\Entities\Accounting\Journal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JournalTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = Journal::selectRaw('journal.created_at as created_date, master_coa.code as code, master_coa.name as name, journal.name as transaction, journal.debet, journal.credit')
            ->join('master_coa', 'journal.coa_id', '=', 'master_coa.id')
            ->whereBetween('journal.created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"]);

        return $model;
    }

    private function getTotal(Request $request)
    {
        $journal = Journal::whereBetween('created_at', [$request->from_date . " 00:00:00", $request->to_date . " 23:59:59"])
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

        $table->editColumn('created_date', function (Journal $model) {
            return Carbon::parse($model->created_date)->format('j/m/Y');
        });

        $table->editColumn('code', function (Journal $model) {
            return $model->code . ' / ' . $model->name;
        });

        $table->editColumn('debet', function (Journal $model) {
            return $model->debet ? 'Rp. ' . number_format($model->debet, 2, ",", ".") : '';
        });

        $table->editColumn('credit', function (Journal $model) {
            return $model->credit ? 'Rp. ' . number_format($model->credit, 2, ",", ".") : '';
        });

        $getTotal = $this->getTotal($request);
        $totaldebet = $getTotal->sum('totaldebet');
        $totalcredit = $getTotal->sum('totalcredit');
        $table->with([
            'totalDebet' => 'Rp. ' . number_format($totaldebet, 2, ",", "."),
            'totalCredit' => 'Rp. ' . number_format($totalcredit, 2, ",", "."),
            'canposting' => ($totaldebet > 0 && number_format($totaldebet, 2, ",", ".") == number_format($totalcredit, 2, ",", ".")) ? 'yes' : 'no',
        ]);
        return $table->make(true);
    }
}
