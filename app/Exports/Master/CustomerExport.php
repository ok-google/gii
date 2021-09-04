<?php

namespace App\Exports\Master;

use App\Entities\Master\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $column = [
        'id', 'code', 'name', 'store', 'address', 'category_id', 'type_id', 'address_do',
        'email', 'phone', 'fax',
        'owner_name', 'website', 'plafon_piutang',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan', 'zipcode',
        'status'
    ];

    private $headings = [
        'id', 'code', 'name', 'store', 'address', 'category', 'type', 'address_do',
        'email', 'phone', 'fax',
        'owner_name', 'website', 'plafon_piutang',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan', 'zipcode',
        'status'
    ];

    public function query()
    {
        return Customer::query()->select($this->column);
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
            $row->store,
            $row->address,
            $row->category->name,
            $row->type->name,
            $row->address_do,
            $row->email,
            $row->phone,
            $row->fax,
            $row->owner_name,
            $row->website,
            $row->plafon_piutang,
            $row->text_provinsi,
            $row->text_kota,
            $row->text_kecamatan,
            $row->text_kelurahan,
            $row->zipcode,
            $row->status()
        ];
    }
}
