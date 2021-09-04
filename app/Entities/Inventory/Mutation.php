<?php

namespace App\Entities\Inventory;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mutation extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'warehouse_id', 'description', 'status'];
    protected $table = 'mutation';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function mutation_details()
    {
        return $this->hasMany('App\Entities\Inventory\MutationDetail');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }
}
