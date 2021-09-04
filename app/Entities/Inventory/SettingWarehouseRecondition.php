<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class SettingWarehouseRecondition extends Model
{
    protected $fillable = ['warehouse_id'];
    protected $table = 'setting_warehouse_recondition';
}
