<?php

namespace App\Exports\Inventory;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockAdjustmentImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'Code',
                'Warehouse',
                'Minus',
                'Sku',
                'Qty',
                'Price',
                'Description',
            ]
        ];
    }
}
