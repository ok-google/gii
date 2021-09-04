<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class StockAdjusmentDetail extends Model
{
    protected $fillable = ['stock_adjusment_id', 'product_id', 'qty', 'price', 'total', 'description'];

    protected $table = 'stock_adjusment_detail';

    public function product()
    {
        return $this->BelongsTo('App\Entities\Master\Product');
    }
}
