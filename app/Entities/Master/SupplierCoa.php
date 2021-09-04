<?php

namespace App\Entities\Master;

use App\Entities\Model;

class SupplierCoa extends Model
{
    protected $fillable = [
        'supplier_id', 'type', 'branch_office_id', 'coa_id'
    ];
    protected $table = 'master_supplier_coa';

    public function coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'coa_id');
    }
}
