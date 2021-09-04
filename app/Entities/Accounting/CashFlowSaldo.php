<?php

namespace App\Entities\Accounting;

use App\Entities\Model;

class CashFlowSaldo extends Model
{
    protected $fillable = ['periode_id', 'beginning_balance'];
    protected $table = 'cash_flow_saldo';

}
