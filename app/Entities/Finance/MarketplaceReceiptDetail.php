<?php

namespace App\Entities\Finance;

use App\Entities\Model;

class MarketplaceReceiptDetail extends Model
{
    protected $fillable = ['marketplace_receipt_id', 'payment', 'cost', 'cost_1', 'cost_2', 'cost_3', 'payment_coa', 'cost_1_coa', 'cost_2_coa', 'cost_3_coa', 'credit_coa'];
    protected $table = 'marketplace_receipt_detail';
    
    public function coa_payment_coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'payment_coa');
    }

    public function coa_cost_1_coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'cost_1_coa');
    }

    public function coa_cost_2_coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'cost_2_coa');
    }
    
    public function coa_cost_3_coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'cost_3_coa');
    }

    public function coa_credit_coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'credit_coa');
    }
}
