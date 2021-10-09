<?php

namespace App\Entities\Finance;

use App\Entities\Model;

class MarketplaceReceipt extends Model
{
    protected $fillable = ['code', 'total', 'payment', 'cost_1', 'cost_2', 'cost_3', 'paid', 'status', 'created_by', 'store_name', 'kode_transaksi'];
    protected $table = 'marketplace_receipt';

    public function details()
    {
        return $this->hasMany('App\Entities\Finance\MarketplaceReceiptDetail');
    }

    public function price_format($value)
    {
        if($value == null)
            return '';
        
        return 'Rp. '.number_format($value, 2, ",", ".");
    }
    
    public function total_payment() 
    {
        return $this->details->sum('payment');
    }

    public function total_cost() 
    {
        return $this->details->sum('cost');
    }
}
