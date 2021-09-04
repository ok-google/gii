<?php

namespace App\Exports\Master;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'sku',
                'product_name',
                'brand_id',
                'sub_brand_id',
                'quantity',
                'unit_id',
                'category_id',
                'type_id',
            ]
        ];
    }
}
