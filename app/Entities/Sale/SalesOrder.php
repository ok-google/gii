<?php

namespace App\Entities\Sale;

use App\Entities\Finance\CBReceiptInvoiceDetail;
use App\Entities\Finance\MarketplaceReceipt;
use App\Entities\Master\CustomerCoa;
use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SalesOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'kode_pelunasan', 'store_name', 'store_phone', 'marketplace_order', 'warehouse_id',
        'customer_id', 'customer_marketplace', 'address_marketplace', 'no_hp_marketplace',
        'ekspedisi_id', 'ekspedisi_marketplace', 'resi', 'batas_kirim',
        'total', 'tax', 'discount', 'shipping_fee', 'grand_total', 'weight',
        'description', 'order_date', 'status', 'status_sales_order'
    ];
    protected $table = 'sales_order';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2,
    ];

    const MARKETPLACE_ORDER = [
        'Offline' => 0,
        'Shopee' => 1,
        'Tokopedia' => 2,
        'Lazada' => 3,
        'Blibli' => 4,
        'Non Marketplace' => 5,
        'Tiktok' => 6,
    ];

    public function marketplace_order()
    {
        return array_search($this->marketplace_order, self::MARKETPLACE_ORDER);
    }

    public function sales_order_details()
    {
        return $this->hasMany('App\Entities\Sale\SalesOrderDetail');
    }

    public function warehouse()
    {
        return $this->BelongsTo('App\Entities\Master\Warehouse');
    }

    public function customer()
    {
        return $this->BelongsTo('App\Entities\Master\Customer');
    }

    public function customer_name()
    {
        if ($this->marketplace_order == 0) {
            return $this->customer->name;
        } else {
            return $this->customer_marketplace;
        }
    }

    public function ekspedisi()
    {
        return $this->BelongsTo('App\Entities\Master\Ekspedisi');
    }

    public function delivery_order_details()
    {
        return $this->hasOne('App\Entities\Sale\DeliveryOrderDetail', 'sales_order_id');
    }

    public function price_format($value)
    {
        return number_format($value, 2, ".", ",");
    }

    public function total_paid()
    {
        if ($this->marketplace_order == 0) {
            $total_paid = CBReceiptInvoiceDetail::where('sales_order_id', $this->id)
                ->whereHas('receipt_invoice', function ($query) {
                    $query->where('status', 2);
                })->sum('paid');

            return $total_paid;
        } else {
            $mr = MarketplaceReceipt::where('code', $this->code)->first();
            if ($mr == null) {
                return 0;
            } else {
                return $mr->total_payment();
            }
        }
    }

    public function total_cost()
    {
        if ($this->marketplace_order == 0) {
            return 0;
        } else {
            $mr = MarketplaceReceipt::where('code', $this->code)->first();
            if ($mr == null) {
                return 0;
            } else {
                return $mr->total_cost();
            }
        }
    }

    public function payment_history()
    {
        $data = [];
        if ($this->marketplace_order == 0) {

            $payment_history = CBReceiptInvoiceDetail::where('sales_order_id', $this->id)
                ->whereHas('receipt_invoice', function ($query) {
                    $query->where('status', 2);
                })->get();

            foreach ($payment_history as $item) {
                $data[] = [
                    'date' => $item->receipt_invoice->updated_at,
                    'coa' => $item->receipt_invoice->coa->code,
                    'account' => $item->receipt_invoice->coa->name,
                    'debet' => $item->paid,
                    'credit' => '',
                ];
                $superuser = Auth::guard('superuser')->user();
                $customer_coa = CustomerCoa::where('customer_id', $item->receipt_invoice->customer_id)
                    ->where('type', $superuser->type)
                    ->where('branch_office_id', $superuser->branch_office_id)
                    ->first();
                $data[] = [
                    'date' => $item->receipt_invoice->updated_at,
                    'coa' => $customer_coa->coa->code,
                    'account' => $customer_coa->coa->name,
                    'debet' => '',
                    'credit' => $item->paid,
                ];
            }
            return $data;
        } else {
            $mr = MarketplaceReceipt::where('code', $this->code)->first();
            if ($mr == null) {
                return $data;
            } else {
                foreach ($mr->details as $item) {
                    $data[] = [
                        'date' => $item->created_at,
                        'coa' => $item->coa_payment_coa->code ?? '-',
                        'account' => $item->coa_payment_coa->name ?? '-',
                        'debet' => $item->payment,
                        'credit' => '',
                    ];
                    if ($item->cost_1) {
                        $data[] = [
                            'date' => $item->created_at,
                            'coa' => $item->coa_cost_1_coa->code,
                            'account' => $item->coa_cost_1_coa->name,
                            'debet' => $item->cost_1,
                            'credit' => '',
                        ];
                    }
                    if ($item->cost_2) {
                        $data[] = [
                            'date' => $item->created_at,
                            'coa' => $item->coa_cost_2_coa->code,
                            'account' => $item->coa_cost_2_coa->name,
                            'debet' => $item->cost_2,
                            'credit' => '',
                        ];
                    }
                    if ($item->cost_3) {
                        $data[] = [
                            'date' => $item->created_at,
                            'coa' => $item->coa_cost_3_coa->code,
                            'account' => $item->coa_cost_3_coa->name,
                            'debet' => $item->cost_3,
                            'credit' => '',
                        ];
                    }
                    // add credit
                    $data[] = [
                        'date' => $item->created_at,
                        'coa' => $item->coa_credit_coa->code ?? '-',
                        'account' => $item->coa_credit_coa->name ?? '-',
                        'debet' => '',
                        'credit' => $item->payment + $item->cost,
                    ];
                }
                return $data;
            }
        }
    }

    public function return_history()
    {
        $data = [];

        $delivery_order_detail = DeliveryOrderDetail::where('sales_order_id', $this->id)
            ->whereHas('sale_return', function ($query) {
                $query->where('sale_return.status', 2);
            })
            ->first();

        if ($delivery_order_detail == null) {
            return $data;
        }

        foreach ($delivery_order_detail->sale_return->sale_return_details as $item) {
            $data[] = [
                'date' => $delivery_order_detail->sale_return->updated_at,
                'code' => $delivery_order_detail->sale_return->code ?? '-',
                'sku' => $item->product->code ?? '-',
                'qty' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price,
            ];
        }

        return $data;
    }
}
