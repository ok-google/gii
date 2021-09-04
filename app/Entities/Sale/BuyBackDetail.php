<?php

namespace App\Entities\Sale;

use App\Entities\Model;

class BuyBackDetail extends Model
{
    protected $fillable = [
        'buy_back_id', 'sales_order_detail_id', 'buy_back_price',
        'buy_back_qty', 'buy_back_total'];

    protected $table = 'buy_back_detail';

    public function sales_order_detail()
    {
        return $this->BelongsTo('App\Entities\Sale\SalesOrderDetail');
    }
}
