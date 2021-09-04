<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class ReconditionDisposal extends Model
{
    protected $fillable = ['recondition_id', 'product_id', 'quantity'];
    protected $table = 'recondition_disposal';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function recondition()
    {
        return $this->belongsTo('App\Entities\Inventory\Recondition');
    }
}
