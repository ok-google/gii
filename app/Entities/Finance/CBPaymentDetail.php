<?php

namespace App\Entities\Finance;

use App\Entities\Model;

class CBPaymentDetail extends Model
{
    protected $fillable = ['cb_payment_id', 'coa_id', 'name', 'total', 'status_transaction'];
    protected $table = 'cb_payment_detail';
    
    const STATUS_TRANSACTION = [
        'CREDIT' => 0,
        'DEBET' => 1
    ];

    public function status_transaction()
    {
        return array_search($this->status_transaction, self::STATUS_TRANSACTION);
    }

    public function coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'coa_id');
    }

    public function price_format($value)
    {
        return number_format($value, 2, ".", ",");
    }
}
