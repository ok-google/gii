<?php

namespace App\Entities\Finance;

use App\Entities\Model;

class CBReceiptInvoiceDetail extends Model
{
    protected $fillable = ['cb_receipt_invoice_id', 'sales_order_id', 'total', 'paid'];
    protected $table = 'cb_receipt_invoice_detail';

    public function receipt_invoice()
    {
        return $this->BelongsTo('App\Entities\Finance\CBReceiptInvoice', 'cb_receipt_invoice_id');
    }

    public function sales_order()
    {
        return $this->BelongsTo('App\Entities\Sale\SalesOrder', 'sales_order_id');
    }

    public function price_format($value)
    {
        return number_format($value, 2, ".", ",");
    }
}
