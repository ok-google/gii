<?php

namespace App\Entities\Sale;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'delivery_order_id', 'warehouse_reparation_id',
        'description', 'status', 'return_date'];
    protected $table = 'sale_return';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function delivery_order()
    {
        return $this->belongsTo('App\Entities\Sale\DeliveryOrderDetail');
    }

    public function sale_return_details()
    {
        return $this->hasMany('App\Entities\Sale\SaleReturnDetail');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Entities\Master\Warehouse','warehouse_reparation_id');
    }

}
