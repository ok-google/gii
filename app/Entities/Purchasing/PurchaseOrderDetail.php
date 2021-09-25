<?php

namespace App\Entities\Purchasing;

use App\Entities\Model;

class PurchaseOrderDetail extends Model
{
    protected $fillable = [
        'ppb_id', 'product_id', 'quantity', 
        'unit_price', 'local_freight_cost', 'komisi', 'total_tax', 'total_price_rmb', 
        'kurs', 'total_price_idr', 'no_urut', 'order_date',
        'no_container', 'qty_container', 'colly_qty'
    ];
    protected $table = 'ppb_detail';

    public function purchase_order()
    {
        return $this->belongsTo('App\Entities\Purchasing\PurchaseOrder');
    }

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function getQuantityAttribute($value)
    {
        return floatval($value);
    }
    
}
