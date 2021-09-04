<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Sale\SalesOrder;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeliveryProgressTable extends Table
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
            ->where(function ($query) use ($request) {
                if ($request->status == 'sent') {
                    $query->whereNotNull('delivery_order_detail.id')->where('delivery_order_detail.status_validate', '1');
                } elseif($request->status == 'unsent'){
                    $query->whereNull('delivery_order_detail.id')->orWhere('delivery_order_detail.status_validate', '0');
                } else {
                    $query;
                }
            })
            ->whereBetween('sales_order.created_at', [$request->start_date . " 00:00:00", $request->end_date . " 23:59:59"])
            ->leftJoin('delivery_order_detail', 'sales_order.id', '=', 'delivery_order_detail.sales_order_id')
            ->leftJoin('delivery_order', 'delivery_order_detail.delivery_order_id', '=', 'delivery_order.id')
            ->leftJoin('sale_return', function ($join) {
                $join->on('delivery_order_detail.id', '=', 'sale_return.delivery_order_id')
                    ->where(function ($query) {
                        $query->where('sale_return.status', '2');
                    });
            })
            ->leftJoin('superusers', 'delivery_order_detail.scan_by', '=', 'superusers.id')
            ->selectRaw('
                sales_order.id,
                sales_order.created_at, 
                sales_order.code, 
                sales_order.store_name,
                (CASE WHEN delivery_order.code IS NULL THEN FALSE ELSE delivery_order.code END) AS no_pack,
                resi, 
                (SELECT SUM(sales_order_detail.quantity) AS quantity FROM sales_order_detail WHERE sales_order_detail.sales_order_id = sales_order.id) quantity,

                sales_order.order_date AS order_date,
                
                sales_order.acc_at as approved_date,

                (CASE WHEN delivery_order_detail.id IS NULL THEN FALSE ELSE delivery_order_detail.created_at END) AS packing_date,

                (CASE WHEN delivery_order_detail.id IS NOT NULL AND delivery_order_detail.status_validate = 1 THEN delivery_order_detail.updated_at ELSE FALSE END) AS do_validation_date,

                sale_return.updated_at AS return_date,

                (CASE WHEN delivery_order_detail.id IS NOT NULL AND delivery_order_detail.status_validate = 1 AND delivery_order_detail.scan_by IS NOT NULL THEN superusers.name ELSE "-" END) AS scan_by
            ');

        if($request->store != 'all') {
            $multiple_store = explode(',', $request->store);
            $model->whereIn('store_name', $multiple_store);
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
            $detail_html = '<table class="table table-dark" style="margin-top: -5px;margin-bottom: -5px;">
                <thead class="thead-light">
                  <tr>
                    <th class="w-20">SKU</th>
                    <th class="w-20">Product</th>
                    <th class="w-20">Qty</th>
                  </tr>
                </thead>
                <tbody>';

            if (count($model->sales_order_details)) {
                foreach ($model->sales_order_details as $detail) {

                    $detail_html .= '<tr>
                        <td>' . $detail->product->code . '</td>
                        <td>' . $detail->product->name . '</td>
                        <td>' . $detail->quantity . '</td>
                      </tr>';
                }
            } else {
                $detail_html .= '<tr>
                    <td colspan="3">No data product</td>
                  </tr>';
            }

            $detail_html .= '</tbody>
                </table>';

            return $detail_html;
        });

        $table->editColumn('no_pack', function (SalesOrder $model) {
            return $model->no_pack ? $model->no_pack : '-';
        });

        $table->editColumn('created_at', function (SalesOrder $model) {
            return Carbon::parse($model->created_at)->format('d/m/Y H:i');
        });

        $table->editColumn('order_date', function (SalesOrder $model) {
            return $model->order_date ? Carbon::parse($model->order_date)->format('d/m/Y H:i') : '-';
        });

        $table->editColumn('approved_date', function (SalesOrder $model) {
            return $model->approved_date ? Carbon::parse($model->approved_date)->format('d/m/Y H:i') : '-';
        });

        $table->editColumn('packing_date', function (SalesOrder $model) {
            return $model->packing_date ? Carbon::parse($model->packing_date)->format('d/m/Y H:i') : '-';
        });

        $table->editColumn('do_validation_date', function (SalesOrder $model) {
            return $model->do_validation_date ? Carbon::parse($model->do_validation_date)->format('d/m/Y H:i') : '-';
        });

        $table->editColumn('return_date', function (SalesOrder $model) {
            return $model->return_date ? Carbon::parse($model->return_date)->format('d/m/Y H:i') : '-';
        });

        $table->rawColumns(['detail']);

        return $table->make(true);
    }
}
