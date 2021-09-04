<?php

namespace App\Repositories;

use App\Entities\Master\CustomerType;

class CustomerTypeRepo
{
    public static function generateCode()
    {
        $count = CustomerType::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}