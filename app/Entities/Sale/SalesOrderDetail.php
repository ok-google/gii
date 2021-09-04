<?php

namespace App\Entities\Sale;

use App\Entities\Model;

class SalesOrderDetail extends Model
{
    protected $fillable = ['sales_order_id', 'product_id', 'quantity', 'price', 'total', 'hpp_total'];
    protected $table = 'sales_order_detail';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function sales_order()
    {
        return $this->BelongsTo('App\Entities\Sale\SalesOrder');
    }
}
