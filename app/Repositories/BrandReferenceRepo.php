<?php

namespace App\Repositories;

use App\Entities\Master\BrandReference;

class BrandReferenceRepo
{
    public static function generateCode()
    {
        $count = BrandReference::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}