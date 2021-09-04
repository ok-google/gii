<?php

namespace App\Entities\Finance;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CBReceiptInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'type', 'branch_office_id', 'coa_id', 'customer_id', 'status', 'select_date'];
    protected $table = 'cb_receipt_invoice';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    const TYPE = [
        'HEAD_OFFICE' => 1,
        'BRANCH_OFFICE' => 2
    ];

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function branch_office()
    {
        return $this->BelongsTo('App\Entities\Master\BranchOffice');
    }

    public function coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa');
    }

    public function customer()
    {
        return $this->BelongsTo('App\Entities\Master\Customer');
    }

    public function details()
    {
        return $this->hasMany('App\Entities\Finance\CBReceiptInvoiceDetail', 'cb_receipt_invoice_id')->orderBy('id', 'DESC');
    }
}
