<?php

namespace App\Imports\Master;

use App\Entities\Master\Unit;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class UnitImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader(['code', 'unit'], $row);

        return new Unit([
            'name' => $row['code'],
            'abbreviation' => $row['unit'],
            'status' => Unit::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'code' => 'required',
            'unit' => 'required|' . Rule::unique('master_units', 'abbreviation')
        ];
    }
}
