<?php

namespace App\Exports\Accounting;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Entities\Accounting\JournalPeriode;

class BalanceSheetExport implements FromCollection
{

    public function collection()
    {
        return JournalPeriode::all();
    }
}
