<?php

namespace App\Entities\Inventory;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recondition extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'warehouse_id', 'warehouse_reparation_id', 'description', 'status'];
    protected $table = 'recondition';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function recondition_details()
    {
        return $this->hasMany('App\Entities\Inventory\ReconditionDetail');
    }

    public function recondition_valids()
    {
        return $this->hasMany('App\Entities\Inventory\ReconditionValid');
    }

    public function recondition_disposals()
    {
        return $this->hasMany('App\Entities\Inventory\ReconditionDisposal');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }
}
