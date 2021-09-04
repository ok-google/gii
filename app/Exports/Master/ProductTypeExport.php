<?php

namespace App\Exports\Master;

use App\Entities\Master\ProductType;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductTypeExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'code', 'name', 'status'
    ];

    private $headings = [
        'id', 'code', 'product_type', 'status'
    ];

    public function query()
    {
        return ProductType::query()->select($this->column);
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
            $row->status(),
        ];
    }
}
