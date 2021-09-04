<?php

namespace App\Entities\Master;

use App\Entities\Model;

class CustomerCoa extends Model
{
    protected $fillable = [
        'customer_id', 'type', 'branch_office_id', 'coa_id'
    ];
    protected $table = 'master_customer_coa';

    public function coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'coa_id');
    }
}
