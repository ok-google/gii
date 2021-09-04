<?php

namespace App\Entities\Accounting;

use App\Entities\Model;

class SettingProfitLoss extends Model
{
    protected $fillable = ['type', 'branch_office_id', 'key', 'value'];
    protected $table = 'setting_profit_loss';

    const KEY = [
        'act_from',
        'act_to',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
    ];
}
