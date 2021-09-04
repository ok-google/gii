<?php

namespace App\Entities\Inventory;

use App\Entities\Model;
use App\Entities\Master\Warehouse;

class ProductConversion extends Model
{
    protected $fillable = ['code', 'warehouse_id', 'description', 'status'];
    protected $table = 'product_conversion';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    public function details()
    {
        return $this->hasMany(ProductConversionDetail::class);
    }

    public function warehouse()
    {
        return $this->BelongsTo(Warehouse::class);
    }
}
