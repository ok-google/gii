<?php

namespace App\Entities\Inventory;

use App\Entities\Model;
use App\Entities\Inventory\ReconditionDisposal;

class ReconditionValid extends Model
{
    protected $fillable = ['recondition_id', 'product_id', 'quantity'];
    protected $table = 'recondition_valid';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function get_max_parameter($recondition_id, $product_id)
    {
        $recondition_disposal = ReconditionDisposal::where('recondition_id', $recondition_id)->where('product_id', $product_id)->first();
        $recondition_valid = ReconditionValid::where('recondition_id', $recondition_id)->where('product_id', $product_id)->first();

        $max_quantity = $recondition_disposal->quantity + $recondition_valid->quantity;
        return $max_quantity;
    }
}
