<?php

namespace App\Exports\Accounting;

use App\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Entities\Accounting\JournalPeriode;

class BalanceSheetExport implements FromQuery
{
    use Exportable;

    public function query()
    {
        return JournalPeriode::query();
    }
}
