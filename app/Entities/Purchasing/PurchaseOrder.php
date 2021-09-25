<?php

namespace App\Entities\Purchasing;

use App\Entities\Account\Superuser;
use App\Entities\Finance\CBPaymentInvoiceDetail;
use App\Entities\Master\SupplierCoa;
use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'supplier_id', 'address',
        'warehouse_id', 'transaction_type', 'coa_id', 'kurs', 'tax', 'edit_counter',
        'grand_total_rmb', 'grand_total_idr', 'status', 'acc_by', 'acc_at',
    ];
    protected $table = 'ppb';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2,
        'DRAFT' => 3,
    ];

    const TRANSACTION_TYPE = [
        'Tunai' => 1,
        'Non Tunai' => 0,
    ];

    public function transaction_type()
    {
        return array_search($this->transaction_type, self::TRANSACTION_TYPE);
    }

    public function supplier()
    {
        return $this->BelongsTo('App\Entities\Master\Supplier');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }

    public function ekspedisi_sea_freight()
    {
        return $this->BelongsTo('App\Entities\Master\Ekspedisi', 'sea_freight');
    }

    public function ekspedisi_local_freight()
    {
        return $this->BelongsTo('App\Entities\Master\Ekspedisi', 'local_freight');
    }

    public function details()
    {
        return $this->hasMany('App\Entities\Purchasing\PurchaseOrderDetail', 'ppb_id');
    }

    public function coa()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'coa_id');
    }

    public function updatedBySuperuser()
    {
        $superuser = Superuser::find($this->updated_by);

        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

    public function createdBySuperuser()
    {
        $superuser = Superuser::find($this->created_by);

        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

    public function accBySuperuser()
    {
        $superuser = Superuser::find($this->acc_by);

        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
    }

    public function price_format($value)
    {
        return number_format($value, 2, ".", ",");
    }

    public function total_paid($id)
    {
        $total_paid = CBPaymentInvoiceDetail::where('ppb_id', $id)
            ->whereHas('payment_invoice', function ($query) {
                $query->where('status', 2);
            })->sum('paid');

        return $total_paid;
    }

    public function payment_history()
    {
        $data = [];
        $payment_history = CBPaymentInvoiceDetail::where('ppb_id', $this->id)
            ->whereHas('payment_invoice', function ($query) {
                $query->where('status', 2);
            })->get();

        foreach ($payment_history as $item) {
            $data[] = [
                'date' => $item->payment_invoice->updated_at,
                'coa' => $item->payment_invoice->coa->code,
                'account' => $item->payment_invoice->coa->name,
                'debet' => $item->paid,
                'credit' => '',
            ];
            $superuser = Auth::guard('superuser')->user();
            $supplier_coa = SupplierCoa::where('supplier_id', $item->payment_invoice->supplier_id)
                ->where('type', $superuser->type)
                ->where('branch_office_id', $superuser->branch_office_id)
                ->first();
            $data[] = [
                'date' => $item->payment_invoice->updated_at,
                'coa' => $supplier_coa->coa->code,
                'account' => $supplier_coa->coa->name,
                'debet' => '',
                'credit' => $item->paid,
            ];
        }

        return $data;
    }
}
