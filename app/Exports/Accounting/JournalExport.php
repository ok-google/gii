<?php

namespace App\Exports\Accounting;

use App\Entities\Accounting\Coa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use Illuminate\Support\Facades\Auth;

class JournalExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'code',
        'name',
        'kode_pelunasan',
        'group',
        'parent_level_1',
        'parent_level_2',
        'parent_level_3',
    ];

    private $headings = [
        'Date',
        'Chart of Account',
        'Transaction',
        'Debet',
        'Credit',
    ];

    public function query()
    {
        $superuser = Superuser::find(Auth::guard('superuser')->id());
        return Coa::query()->select($this->column)->where('type', $superuser->type)
        ->where('branch_office_id', $superuser->branch_office_id)->orderBy('group', 'ASC')->orderBy('code', 'ASC');
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return [
            $row->code,
            $row->name,
            $row->kode_pelunasan,
            $row->group(),
            $row->parent_level_1 ? $row->parent_level_one->name : '',
            $row->parent_level_2 ? $row->parent_level_two->name : '',
            $row->parent_level_3 ? $row->parent_level_three->name : '',
        ];
    }
}
