<?php

namespace App\Entities\Finance;

use App\Entities\Model;

class CBPaymentInvoiceDetail extends Model
{
    protected $fillable = ['cb_payment_invoice_id', 'ppb_id', 'total', 'paid'];
    protected $table = 'cb_payment_invoice_detail';

    public function ppb()
    {
        return $this->BelongsTo('App\Entities\Purchasing\PurchaseOrder', 'ppb_id');
    }

    public function payment_invoice()
    {
        return $this->BelongsTo('App\Entities\Finance\CBPaymentInvoice', 'cb_payment_invoice_id');
    }

    public function price_format($value)
    {
        return number_format($value, 2, ".", ",");
    }
}
