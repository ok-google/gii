<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UnitImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['code', 'unit']
        ];
    }
}
