<?php

namespace App\Repositories;

use App\Entities\Master\ProductType;

class ProductTypeRepo
{
    public static function generateCode()
    {
        $count = ProductType::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}