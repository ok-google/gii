<?php

namespace App\Entities\QualityControl;

use App\Entities\Model;

class QualityControl2 extends Model
{
    protected $fillable = ['warehouse_id', 'warehouse_reparation_id', 'product_id', 'quantity', 'description', 'status_recondition'];
    protected $table = 'quality_control2';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }
}
