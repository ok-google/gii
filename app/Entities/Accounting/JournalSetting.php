<?php

namespace App\Entities\Accounting;

use App\Entities\Model;

class JournalSetting extends Model
{
    protected $fillable = ['name', 'debet_coa', 'debet_note', 'credit_coa', 'credit_note', 'status'];
    protected $table = 'journal_setting';

    const STATUS = [
        'ACTIVE' => 1,
        'DEACTIVE' => 0
    ];
}
