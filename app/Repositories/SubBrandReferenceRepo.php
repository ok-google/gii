<?php

namespace App\Repositories;

use App\Entities\Master\SubBrandReference;

class SubBrandReferenceRepo
{
    public static function generateCode()
    {
        $count = SubBrandReference::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}