<?php

namespace App\Repositories;

use App\Entities\Master\BranchOffice;

class BranchOfficeRepo
{
    public static function generateCode()
    {
        $count = BranchOffice::count() + 1;
        
        $code = sprintf('%03d', $count);

        return $code;
    }
}