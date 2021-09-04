<?php

namespace App\Entities\Sale;

use App\Entities\Model;
use App\Entities\Account\Superuser;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'print_count', 'warehouse_id',
        'description', 'status', 'is_marketplace'];
    protected $table = 'delivery_order';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function details()
    {
        return $this->hasMany('App\Entities\Sale\DeliveryOrderDetail');
    }

    public function createdBySuperuser()
    {
        $superuser = Superuser::find($this->created_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

}
