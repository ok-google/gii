<?php

namespace App\Repositories;

use App\Entities\Master\ProductCategory;

class ProductCategoryRepo
{
    public static function generateCode()
    {
        $count = ProductCategory::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}