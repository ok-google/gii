<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Sale\SalesOrder;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    public function query(Request $request)
    {
        $model = SalesOrder::whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->where('sales_order.status', SalesOrder::STATUS['ACC'])
            ->where(function ($query) use ($request) {
                if ($request->marketplace != 'all') {
                    $query->where('marketplace_order', $request->marketplace);
                } else {
                    $query;
                }
            })
            ->whereBetween('sales_order.created_at', [$request->start_date . " 00:00:00", $request->end_date . " 23:59:59"])

            ->leftJoin('cb_receipt_invoice_detail', function ($join) {
                $join->on('sales_order.id', '=', 'cb_receipt_invoice_detail.sales_order_id')
                    ->whereExists(function ($query) {
                        $query->select(\DB::raw(1))
                            ->from('cb_receipt_invoice')
                            ->whereRaw('cb_receipt_invoice.id = cb_receipt_invoice_detail.cb_receipt_invoice_id')
                            ->where('cb_receipt_invoice.status', 2);
                    });
            })


            ->leftJoin('marketplace_receipt', 'sales_order.code', '=', 'marketplace_receipt.code')
            ->leftJoin('marketplace_receipt_detail', 'marketplace_receipt.id', '=', 'marketplace_receipt_detail.marketplace_receipt_id')


            ->leftJoin('delivery_order_detail', function ($join) {
                $join->on('sales_order.id', '=', 'delivery_order_detail.sales_order_id')
                    ->whereExists(function ($query) {
                        $query->select(\DB::raw(1))
                            ->from('sale_return')
                            ->whereRaw('sale_return.delivery_order_id = delivery_order_detail.id')
                            ->where('sale_return.status', 2);
                    });
            })
            ->leftJoin('sale_return', 'delivery_order_detail.id', '=', 'sale_return.delivery_order_id')
            ->leftJoin('sale_return_detail', 'sale_return.id', '=', 'sale_return_detail.sale_return_id')

            ->selectRaw('
                sales_order.id as id,
                sales_order.created_at as create_date,
                sales_order.order_date as order_date,
                sales_order.kode_pelunasan,
                marketplace_order,
                store_name,

                (
                CASE 
                    WHEN
                        marketplace_order = 0 
                    THEN 
                        (SELECT master_customers.name FROM master_customers WHERE master_customers.id = sales_order.customer_id)
                    ELSE
                        sales_order.customer_marketplace
                END
                ) AS customer_name,

                sales_order.code as code,
                sales_order.grand_total as grand_total,

                (
                CASE
                    WHEN 
                        marketplace_order = 0
                    THEN
                        IFNULL(SUM(cb_receipt_invoice_detail.paid), 0)
                    ELSE
                        IFNULL(SUM(marketplace_receipt_detail.payment), 0)
                END
                ) AS total_paid,

                (
                CASE
                    WHEN
                        marketplace_order = 0
                    THEN
                        0
                    ELSE
                        IFNULL(SUM(marketplace_receipt_detail.cost), 0)
                END
                ) AS total_cost,

                (
                    sales_order.grand_total - 
                    (
                        (
                            CASE
                                WHEN 
                                    marketplace_order = 0
                                THEN
                                    IFNULL(SUM(cb_receipt_invoice_detail.paid), 0)
                                ELSE
                                    IFNULL(SUM(marketplace_receipt_detail.payment), 0)
                            END
                        )
                            +
                        (
                            CASE
                                WHEN
                                    marketplace_order = 0
                                THEN
                                    0
                                ELSE
                                    IFNULL(SUM(marketplace_receipt_detail.cost), 0)
                            END
                        )
                    )
                ) AS unpaid,

                (
                    IFNULL(SUM(sale_return_detail.price * sale_return_detail.quantity), 0)
                ) AS retur
            ')
            ->groupBy(['sales_order.id', 'sales_order.code', 'sales_order.created_at', 'sales_order.order_date', 'sales_order.kode_pelunasan', 'marketplace_order', 'store_name', 'customer_name', 'grand_total']);

        if ($request->status == 'paid') {
            $model->having('unpaid', '<=', '0');
        }

        if ($request->status == 'debt') {
            $model->having('unpaid', '>', '0');
        }

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->editColumn('detail', function (SalesOrder $model) {
            // PAYMENT HISTORY
            $detail_html = '<table class="table table-dark" style="margin-top: -5px !important;margin-bottom: 0px;">
                <thead class="thead-light">
                    <tr>
                        <th class="w-100" colspan="5" style="text-align: left; font-weight: bold; font-size: 20px;">Payment History</th>
                    </tr>
                    <tr>
                        <th class="w-20">Date</th>
                        <th class="w-20">COA</th>
                        <th class="w-20">Account</th>
                        <th class="w-20">Debet</th>
                        <th class="w-20">Credit</th>
                    </tr>
                </thead>
                <tbody>';

            if (count($model->payment_history())) {
                foreach ($model->payment_history() as $key => $history) {
                    $debet = $history['debet'] ? 'Rp. ' . number_format($history['debet'], 2, ',', '.') : '';
                    $credit = $history['credit'] ? 'Rp. ' . number_format($history['credit'], 2, ',', '.') : '';

                    $detail_html .= '<tr>
                        <td>' . Carbon::parse($history['date'])->format('d/m/Y') . '</td>
                        <td>' . $history['coa'] . '</td>
                        <td>' . $history['account'] . '</td>
                        <td>' . $debet . '</td>
                        <td>' . $credit . '</td>
                      </tr>';
                }
            } else {
                $detail_html .= '<tr>
                    <td colspan="5">Nothing payment history</td>
                  </tr>';
            }

            $detail_html .= '</tbody>
                </table>';

            // RETURN HISTORY
            $detail_html .= '<table class="table table-dark" style="margin-bottom: -5px !important;">
                <thead class="thead-light">
                    <tr>
                        <th class="w-100" colspan="6" style="text-align: left; font-weight: bold; font-size: 20px;">Return History</th>
                    </tr>
                    <tr>
                        <th class="w-20">Date</th>
                        <th class="w-20">Code</th>
                        <th class="w-20">SKU</th>
                        <th class="w-20">Qty</th>
                        <th class="w-20">Price</th>
                        <th class="w-20">SubTotal</th>
                    </tr>
                </thead>
                <tbody>';

            if (count($model->return_history())) {
                foreach ($model->return_history() as $key => $history) {
                    $price = $history['price'] ? 'Rp. ' . number_format($history['price'], 2, ',', '.') : '';
                    $subtotal =  $history['subtotal'] ? 'Rp. ' . number_format($history['subtotal'], 2, ',', '.') : '';

                    $detail_html .= '<tr>
                        <td>' . Carbon::parse($history['date'])->format('d/m/Y') . '</td>
                        <td>' . $history['code'] . '</td>
                        <td>' . $history['sku'] . '</td>
                        <td>' . $history['qty'] . '</td>
                        <td>' . $price . '</td>
                        <td>' . $subtotal . '</td>
                      </tr>';
                }
            } else {
                $detail_html .= '<tr>
                    <td colspan="6">Nothing return history</td>
                  </tr>';
            }

            $detail_html .= '</tbody>
                </table>';

            return $detail_html;
        });


        $table->editColumn('unpaid', function ($model) {
            return $model->unpaid;
        });

        $table->editColumn('marketplace_order', function (SalesOrder $model) {
            return $model->marketplace_order();
        });

        $table->editColumn('create_date', function (SalesOrder $model) {
            return $model->create_date ? Carbon::parse($model->create_date)->format('d/m/Y H:i') : '-';
        });

        $table->editColumn('order_date', function (SalesOrder $model) {
            return $model->order_date ? Carbon::parse($model->order_date)->format('d/m/Y H:i') : '-';
        });

        $table->rawColumns(['detail']);
        return $table->make(true);
    }
}
