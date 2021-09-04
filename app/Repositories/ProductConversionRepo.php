<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use App\Entities\Inventory\ProductConversion;

class ProductConversionRepo
{
    public static function generateCode()
    {
        $count = ProductConversion::count() + 1;

        $code = 'CVT-' . sprintf('%03d', $count);

        $is_duplicate = ProductConversion::where('code', $code)->first();
        if ($is_duplicate) {
            $code = strtoupper($code . Str::random(2));
        }

        return $code;
    }
}
