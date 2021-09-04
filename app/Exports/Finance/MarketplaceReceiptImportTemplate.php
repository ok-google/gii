<?php

namespace App\Exports\Finance;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MarketplaceReceiptImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'invoice',
                'tgl_pencairan',
                'payment',
                'cost_1',
                'cost_2',
                'cost_3'
            ]
        ];
    }
}
