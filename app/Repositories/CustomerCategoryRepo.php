<?php

namespace App\Repositories;

use App\Entities\Master\CustomerCategory;

class CustomerCategoryRepo
{
    public static function generateCode()
    {
        $count = CustomerCategory::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}