<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Sale\SalesOrder;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HppReportTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = SalesOrder::where(function ($query) use ($request) {
            if ($request->warehouse == 'all') {
                $query->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
            } else {
                $query->where('warehouse_id', $request->warehouse);
            }
        })
            
            ->whereBetween('sales_order.created_at', [$request->start_date . " 00:00:00", $request->end_date . " 23:59:59"])
            ->join('sales_order_detail', function ($join) use ($request) {
                $join->on('sales_order.id', '=', 'sales_order_detail.sales_order_id')
                    ->where(function ($query) use ($request) {
                        if ($request->product != 'all') {
                            $query->where('product_id', $request->product)->whereNotNull('hpp_total');
                        } else {
                            $query->whereNotNull('hpp_total');
                        }
                    });
            })
            ->join('master_products', 'sales_order_detail.product_id', '=', 'master_products.id')
            ->join('master_warehouses', 'sales_order.warehouse_id', '=', 'master_warehouses.id')
            ->select('sales_order.created_at', 'store_name', 'sales_order.code', 'master_products.code as sku', 'master_products.name as product', 'sales_order_detail.quantity as qty', 'sales_order_detail.hpp_total', 'sales_order_detail.price as sale_price', 'sales_order_detail.total as sale_price_total', 'master_warehouses.name as warehouse')
            ->selectRaw('sales_order_detail.hpp_total / sales_order_detail.quantity as hpp');

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

        $table->editColumn('created_at', function (SalesOrder $model) {
            return Carbon::parse($model->created_at)->format('d/m/Y');
        });

        return $table->make(true);
    }
}
