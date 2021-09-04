<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'status'];
    protected $table = 'master_customer_categories';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function customer()
    {
        return $this->hasMany('App\Entities\Master\Customer', 'category_id')->orderBy('name');
    }
}
