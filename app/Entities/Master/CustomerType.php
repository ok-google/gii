<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerType extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'grosir_address', 'description', 'status'];
    protected $table = 'master_customer_types';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function customer()
    {
        return $this->hasMany('App\Entities\Master\Customer', 'type_id')->orderBy('name');
    }

    // public function getGrosirAddressAttribute()
    // {
    //     return $this->grosir_address;
    // }
}
