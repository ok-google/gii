<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use App\Entities\Inventory\MutationDisplay;

class MutationDisplayRepo
{
    public static function generateCode()
    {
        $count = MutationDisplay::count() + 1;

        $code = 'MUT-D-' . sprintf('%03d', $count);

        $is_duplicate = MutationDisplay::where('code', $code)->first();
        if ($is_duplicate) {
            $code = strtoupper($code . Str::random(2));
        }

        return $code;
    }
}
