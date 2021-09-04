<?php

namespace App\Entities\Sale;

use App\Entities\Model;

class SaleReturnDetail extends Model
{
    protected $fillable = [
        'sale_return_id', 'product_id',
        'quantity', 'hpp', 'price', 'status_recondition', 'description'];
    protected $table = 'sale_return_detail';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function sale_return()
    {
        return $this->belongsTo('App\Entities\Sale\SaleReturn');
    }
    // public function sales_order_details()
    // {
    //     return $this->hasMany('App\Entities\Sale\SalesOrderDetail');
    // }

}
