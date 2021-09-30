<?php

namespace App\Exports\Purchasing;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PurchaseOrderDetailImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'sku',
                'quantity',
                'unit_price',
                'local_freight_cost',
                'komisi',
                'kurs',
                'order_date',
                'no_container',
                'qty_container',
                'colly_qty',
            ]
        ];
    }
}
