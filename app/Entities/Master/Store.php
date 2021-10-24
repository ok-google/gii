<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchOffice extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'phone', 'address'];
    protected $table = 'master_store';

    public function warehouses()
    {
        return $this->hasMany('App\Entities\Master\Warehouse');
    }
}
