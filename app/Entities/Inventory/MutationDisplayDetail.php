<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class MutationDisplayDetail extends Model
{
    protected $fillable = ['mutation_display_id', 'product_id', 'qty'];
    protected $table = 'mutation_display_detail';

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }
}
