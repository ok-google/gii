<?php

namespace App\Imports\Master;

use App\Entities\Master\Supplier;
use App\Repositories\SupplierRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class SupplierImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([
            'name', 'address',
            'email', 'phone', 'fax',
            'website', 'owner_name'
        ], $row);

        return new Supplier([
            'code' => SupplierRepo::generateCode(),
            'name' => $row['name'],
            'address' => $row['address'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'fax' => $row['fax'],
            'website' => $row['website'],
            'owner_name' => $row['owner_name'],
            'status' => Supplier::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'name' => 'required'
        ];
    }
}
