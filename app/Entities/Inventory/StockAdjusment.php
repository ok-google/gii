<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class StockAdjusment extends Model
{
    protected $fillable = [
        'code', 'type', 'branch_office_id',
        'warehouse_id', 'minus', 'status'];
    protected $table = 'stock_adjusment';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2,
    ];

    public function details()
    {
        return $this->hasMany('App\Entities\Inventory\StockAdjusmentDetail', 'stock_adjusment_id')->orderBy('id', 'DESC');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }
}
