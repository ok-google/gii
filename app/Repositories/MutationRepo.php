<?php

namespace App\Repositories;

use App\Entities\Inventory\Mutation;

class MutationRepo
{
    public static function generateCode()
    {
        $count = Mutation::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}