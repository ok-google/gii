<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Repositories\BrandReferenceRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class BrandReferenceImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;
    
    public function model(array $row)
    {
        $this->validateHeader(['name'], $row);

        return new BrandReference([
            'code' => BrandReferenceRepo::generateCode(),
            'name' => $row['name'],
            'status' => BrandReference::STATUS['ACTIVE']
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
