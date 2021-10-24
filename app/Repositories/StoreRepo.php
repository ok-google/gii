<?php

namespace App\Repositories;

use App\Entities\Master\Store;

class StoreRepo
{
    public static function generateCode()
    {
        $count = Store::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}