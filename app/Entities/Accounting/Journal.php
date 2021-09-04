<?php

namespace App\Entities\Accounting;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use SoftDeletes;

    protected $fillable = ['coa_id', 'name', 'debet', 'credit', 'status'];
    protected $table = 'journal';
    
    const STATUS = [
        'UNPOST' => 0,
        'POST' => 1
    ];

    const PREJOURNAL = [
        'PPB_ACC' => 'PPB ACC - ',
        'RI_TAX' => 'RI TAX - ',
        'RI_ACC' => 'RECEIVING ACC - ',
        'RI_REJECT' => 'PPB REJECT - ',
        'DO_VALIDATE' => 'DO VALIDATE - ',
        'CB_PAYMENT' => 'PAYMENT - ',
        'CB_RECEIPT' => 'RECEIPT - ',
        'CB_PAYMENT_INV' => 'PAYMENT INV - ',
        'CB_RECEIPT_INV' => 'RECEIPT INV - ',
        'SALE_RETURN_ACC' => 'SALE RETURN - ',
        'DISPOSAL_ACC' => 'DISPOSAL - ',
        'MARKETPLACE_RECEIPT' => 'MARKETPLACE_RECEIPT - ',
        'BUY_BACK' => 'BUY BACK - ',
        'STOCK_ADJUSMENT' => 'STOCK ADJUSMENT - ',
    ];

    public function coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'coa_id');
    }
}
