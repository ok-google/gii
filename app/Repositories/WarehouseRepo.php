<?php

namespace App\Repositories;

use App\Entities\Master\Warehouse;

class WarehouseRepo
{
    public static function generateCode()
    {
        $count = Warehouse::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}