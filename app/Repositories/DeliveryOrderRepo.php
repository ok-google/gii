<?php

namespace App\Repositories;

use App\Entities\Sale\DeliveryOrder;

class DeliveryOrderRepo
{
    public static function generateCode()
    {
        $ym = now()->format('ym');
        $findcode = DeliveryOrder::where('code', 'like', $ym.'-%')->latest()->first();
        
        if($findcode) {
            $sub = substr($findcode->code, strpos($findcode->code, "-") + 1) + 1;
            $code = $ym.'-'.sprintf('%03d', $sub);
        } else {
            $code = $ym.'-001';
        }

        return $code;
    }
}