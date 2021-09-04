<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerCoaPenjualan extends Model
{
    protected $fillable = [
        'customer_id', 'type', 'branch_office_id', 'coa_id'
    ];
    protected $table = 'master_customer_coa_penjualan';

}
