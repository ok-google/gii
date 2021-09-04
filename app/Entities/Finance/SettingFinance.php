<?php

namespace App\Entities\Finance;

use App\Entities\Model;

class SettingFinance extends Model
{
    protected $fillable = ['type', 'branch_office_id', 'key', 'coa_id'];
    protected $table = 'setting_finance';

    const TYPE = [
        'HEAD_OFFICE' => 1,
        'BRANCH_OFFICE' => 2
    ];

    const KEY = [
        'ppb_tunai_debet',
        'ppb_non_tunai_debet',
        
        'receiving_debet',
        'receiving_tax',
        
        // 'do_transaction_credit',
        'do_hpp_debet',
        'do_hpp_credit',

        'piutang_tokopedia',
        'piutang_lazada',
        'piutang_shopee',
        'piutang_blibli',

        'penjualan_tokopedia',
        'penjualan_lazada',
        'penjualan_shopee',
        'penjualan_blibli',

        'return_transaction_debet',
        'return_transaction_credit',
        'return_hpp_debet',
        'return_hpp_credit',

        'disposal_debet',
        'disposal_credit', 

        // 'buy_back_valid_price_debet',
        'buy_back_valid_price_credit',

        'buy_back_valid_hpp_debet',
        'buy_back_valid_hpp_credit',

        // 'buy_back_disposal_price_debet',
        'buy_back_disposal_price_credit',

        'buy_back_disposal_hpp_debet',
        'buy_back_disposal_hpp_credit',

        'stock_adjusment_plus_debet',
        'stock_adjusment_plus_credit',

        'stock_adjusment_minus_debet',
        'stock_adjusment_minus_credit',
    ];

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function branch_office()
    {
        return $this->BelongsTo('App\Entities\Master\BranchOffice');
    }
}
