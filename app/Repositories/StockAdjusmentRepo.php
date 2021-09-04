<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use App\Entities\Inventory\StockAdjusment;

class StockAdjusmentRepo
{
    public static function generateCode()
    {
        $count = StockAdjusment::count() + 1;

        $code = 'ADJ-' . sprintf('%03d', $count);

        $is_duplicate = StockAdjusment::where('code', $code)->first();
        if ($is_duplicate) {
            $code = strtoupper($code . Str::random(2));
        }

        return $code;
    }
}
