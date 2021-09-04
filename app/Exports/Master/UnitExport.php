<?php

namespace App\Exports\Master;

use App\Entities\Master\Unit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnitExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'name', 'abbreviation', 'status'
    ];

    private $headings = [
        'id', 'code', 'unit', 'status'
    ];

    public function query()
    {
        return Unit::query()->select($this->column);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->abbreviation,
            $row->status()
        ];
    }
}
