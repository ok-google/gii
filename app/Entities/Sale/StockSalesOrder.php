<?php

namespace App\Entities\Sale;

use App\Entities\Model;

class StockSalesOrder extends Model
{
    protected $fillable = ['warehouse_id', 'product_id', 'stock'];
    protected $table = 'stock_sales_order';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }
}
