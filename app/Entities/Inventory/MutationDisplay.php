<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class MutationDisplay extends Model
{
    protected $fillable = ['code', 'warehouse_from', 'warehouse_to', 'description', 'status'];
    protected $table = 'mutation_display';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function details()
    {
        return $this->hasMany('App\Entities\Inventory\MutationDisplayDetail');
    }

    public function warehouse_from_attribute()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse', 'warehouse_from', 'id');
    }

    public function warehouse_to_attribute()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse', 'warehouse_to', 'id');
    }
}
