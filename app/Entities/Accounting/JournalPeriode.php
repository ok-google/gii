<?php

namespace App\Entities\Accounting;

use App\Entities\Model;

class JournalPeriode extends Model
{
    protected $fillable = ['type', 'branch_office_id', 'from_date', 'to_date', 'status'];
    protected $table = 'journal_periode';
    
    const STATUS = [
        'UNPOST' => 0,
        'POST' => 1
    ];
}
