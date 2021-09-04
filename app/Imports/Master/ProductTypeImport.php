<?php

namespace App\Imports\Master;

use App\Entities\Master\ProductType;
use App\Repositories\ProductTypeRepo;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductTypeImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader(['product_type'], $row);

        return new ProductType([
            'code' => ProductTypeRepo::generateCode(),
            'name' => $row['product_type'],
            'status' => ProductType::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            // 'code' => 'required|' . Rule::unique('master_product_types', 'code'),
            'product_type' => 'required'
        ];
    }
}
