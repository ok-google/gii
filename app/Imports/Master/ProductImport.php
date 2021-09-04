<?php

namespace App\Imports\Master;

use App\Entities\Master\BrandReference;
use App\Entities\Master\Product;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Unit;
use App\Entities\Master\Warehouse;
use App\Traits\ImportValidateHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductImport implements ToModel, WithHeadingRow, WithStartRow, WithValidation
{
    use ImportValidateHeader;

    public function model(array $row)
    {
        $this->validateHeader([
            'sku',
            'product_name',
            'brand_id',
            'sub_brand_id',
            'quantity',
            'unit_id',
            'category_id',
            'type_id',
        ], $row);

        return new Product([
            'code' => $row['sku'],
            'name' => $row['product_name'],
            'brand_reference_id' => $row['brand_id'],
            'sub_brand_reference_id' => $row['sub_brand_id'],
            'quantity' => $row['quantity'],
            'unit_id' => $row['unit_id'],
            'category_id' => $row['category_id'],
            'type_id' => $row['type_id'],
            'status' => Product::STATUS['ACTIVE']
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array {
        return [
            'brand_id' => 'required|' . Rule::in(BrandReference::select('id')->pluck('id')->toArray()),
            'sub_brand_id' => 'required|' . Rule::in(SubBrandReference::select('id')->pluck('id')->toArray()),
            'category_id' => 'required|' . Rule::in(ProductCategory::select('id')->pluck('id')->toArray()),
            'type_id' => 'required|' . Rule::in(ProductType::select('id')->pluck('id')->toArray()),
            'sku' => 'required|' . Rule::unique('master_products', 'code'),
            'product_name' => 'required',
            'quantity' => 'required|numeric',
            'unit_id' => 'required|integer|' . Rule::in(Unit::select('id')->pluck('id')->toArray()),
        ];
    }
}
