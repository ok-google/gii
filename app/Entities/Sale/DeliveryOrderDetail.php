<?php

namespace App\Entities\Sale;

use App\Entities\Model;

class DeliveryOrderDetail extends Model
{
    protected $fillable = [ 'code', 'delivery_order_id', 'sales_order_id', 'status_validate' ];
    protected $table = 'delivery_order_detail';

    public function delivery_order()
    {
        return $this->BelongsTo('App\Entities\Sale\DeliveryOrder');
    }

    public function sales_order()
    {
        return $this->BelongsTo('App\Entities\Sale\SalesOrder');
    }

    public function sale_return()
    {
        return $this->hasOne('App\Entities\Sale\SaleReturn', 'delivery_order_id');
    }
}
