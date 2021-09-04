<?php

namespace App\Entities\Inventory;

use App\Entities\Master\Product;
use App\Entities\Model;

class ProductConversionDetail extends Model
{
    protected $fillable = ['product_conversion_id', 'product_from', 'product_to', 'qty', 'description'];
    protected $table = 'product_conversion_detail';

    public function product_from_rel()
    {
        return $this->belongsTo(Product::class, 'product_from', 'id');
    }

    public function product_to_rel()
    {
        return $this->belongsTo(Product::class, 'product_to', 'id');
    }
}
