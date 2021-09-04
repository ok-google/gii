<?php

namespace App\Exports\Accounting;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CoaImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['code', 'name', 'group', 'parent_level_1', 'parent_level_2', 'parent_level_3', 'saldo_awal', 'debet(1)_credit(0)']
        ];
    }
}
