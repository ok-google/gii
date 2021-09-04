<?php

namespace App\Imports\Master;

use App\Entities\Master\CustomerCategory;
use App\Repositories\CustomerCategoryRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class CustomerCategoryImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader(['category'], $row);

        return new CustomerCategory([
            'code' => CustomerCategoryRepo::generateCode(),
            'name' => $row['category'],
            'status' => CustomerCategory::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'category' => 'required'
        ];
    }
}
