<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class ReconditionResidual extends Model
{
    protected $fillable = ['warehouse_reparation_id', 'type_text', 'product_id', 'quantity', 'status_recondition', 'description'];
    protected $table = 'recondition_residual';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }
}
