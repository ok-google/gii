<?php

namespace App\Exports\Master;

use App\Entities\Master\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id',
        'code',
        'name',
        'brand_reference_id',
        'sub_brand_reference_id',
        'quantity',
        'unit_id',
        'category_id',
        'type_id',
        'status',
    ];

    private $headings = [
        'id',
        'sku',
        'product_name',
        'brand',
        'sub_brand',
        'quantity',
        'unit',
        'category',
        'type',
        'status',
    ];

    public function query()
    {
        return Product::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->code,
            $row->name,
            $row->brand_reference->name,
            $row->sub_brand_reference->name,
            $row->quantity,
            $row->unit->name,
            $row->category->name,
            $row->type->name,
            $row->status()
        ];
    }
}
