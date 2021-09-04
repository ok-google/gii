<?php

namespace App\Repositories;

use App\Entities\Master\Customer;

class CustomerRepo
{
    public static function generateCode()
    {
        $pre = 'CUST-';
        $count = Customer::count() + 1;
        
        $code = $pre . sprintf('%04d', $count);

        return $code;
    }

    public static function generateCategoryCode($string)
    {
        $code = strtoupper(strip_vowels($string));

        return $code;
    }

    public static function generateTypeCode($string)
    {
        $code = strtoupper(strip_vowels($string));

        return $code;
    }
}