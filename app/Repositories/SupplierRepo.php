<?php

namespace App\Repositories;

use App\Entities\Master\Supplier;

class SupplierRepo
{
    public static function generateCode()
    {
        $count = Supplier::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}