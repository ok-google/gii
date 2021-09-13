<?php

namespace App\Entities\Sale;

use App\Entities\Account\Superuser as AccountSuperuser;
use App\Entities\Model;

class BuyBack extends Model
{
    protected $fillable = [
        'code', 'type', 'branch_office_id',
        'sales_order_id', 'warehouse_id', 'disposal', 'status'];
    protected $table = 'buy_back';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function details()
    {
        return $this->hasMany('App\Entities\Sale\BuyBackDetail', 'buy_back_id')->orderBy('id', 'DESC');
    }

    public function sales_order()
    {
        return $this->BelongsTo('App\Entities\Sale\SalesOrder');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }

    public function createdBySuperuser()
    {
        $superuser = AccountSuperuser::find($this->created_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }
}
