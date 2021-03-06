<?php

namespace App\Entities\Accounting;

use App\Entities\Model;

class Hpp extends Model
{
    protected $fillable = ['type', 'branch_office_id', 'product_id', 'warehouse_id', 'quantity', 'price'];
    protected $table = 'hpp';
}
