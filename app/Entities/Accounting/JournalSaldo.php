<?php

namespace App\Entities\Accounting;

use App\Entities\Model;

class JournalSaldo extends Model
{
    protected $fillable = ['periode_id', 'coa_id', 'position', 'saldo'];
    protected $table = 'journal_saldo';

    const POSITION = [
        'CREDIT'    => 0,
        'DEBET'     => 1,
        'BALANCE'   => 2
    ];
}
