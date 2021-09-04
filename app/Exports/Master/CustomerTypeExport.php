<?php

namespace App\Exports\Master;

use App\Entities\Master\CustomerType;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerTypeExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'code', 'name', 'description', 'status'
    ];

    public function query()
    {
        return CustomerType::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->column;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->code,
            $row->name,
            $row->description,
            $row->status(),
        ];
    }
}
