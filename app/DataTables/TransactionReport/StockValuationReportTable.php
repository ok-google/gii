<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Master\Product;
use Illuminate\Http\Request;

class StockValuationReportTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    public function query(Request $request)
    {
        $where_receiving = $where_sales_order = $where_sale_return = $where_ppb = '';
        if ($request->warehouse != 'all') {
            $warehouse = $request->warehouse;
            if (empty($request->warehouse)) {
                $warehouse = 0;
            }
            $where_receiving = ' AND (receiving.warehouse_id IN(' . $warehouse . '))';
            $where_sales_order = ' AND (sales_order.warehouse_id IN(' . $warehouse . '))';
            $where_sale_return = ' AND (sale_return.warehouse_reparation_id IN(' . $warehouse . '))';
            $where_ppb = ' AND (ppb.warehouse_id IN(' . $warehouse . '))';
        }

        $model = Product::where(function ($query) use ($request) {
            if ($request->category != 'all') {
                $multiple_category = explode(',', $request->category);
                $query->whereIn('category_id', $multiple_category);
            } else {
                $query;
            }
        })
            ->selectRaw('master_products.name as name, master_products.code as sku,

        (
            (SELECT SUM(receiving_detail.total_quantity_ri - (SELECT SUM(receiving_detail_colly.quantity_recondition) FROM receiving_detail_colly WHERE receiving_detail_colly.receiving_detail_id = receiving_detail.id)) AS receiving_qty FROM receiving_detail JOIN receiving ON receiving_detail.receiving_id = receiving.id WHERE master_products.id = receiving_detail.product_id AND receiving.status = 2 AND receiving.acc_at < "' . $request->start_date . ' 00:00:00"' . $where_receiving . ')
            -
            IFNULL((SELECT SUM(sales_order_detail.quantity) AS sale_qty FROM sales_order_detail JOIN sales_order ON sales_order_detail.sales_order_id = sales_order.id WHERE master_products.id = sales_order_detail.product_id AND sales_order.status = 2 AND sales_order_detail.hpp_total IS NOT NULL AND sales_order.acc_at < "' . $request->start_date . ' 00:00:00"' . $where_sales_order . '), 0)
            +
            IFNULL((SELECT SUM(sale_return_detail.quantity) AS return_qty FROM sale_return_detail JOIN sale_return ON sale_return_detail.sale_return_id = sale_return.id WHERE master_products.id = sale_return_detail.product_id AND sale_return.status = 2 AND sale_return.updated_at < "' . $request->start_date . ' 00:00:00"' . $where_sale_return . '), 0)
        ) AS opening_qty,

        (
            (SELECT SUM((receiving_detail.total_quantity_ri - (SELECT SUM(receiving_detail_colly.quantity_recondition) FROM receiving_detail_colly WHERE receiving_detail_colly.receiving_detail_id = receiving_detail.id)) * (SELECT SUM((ppb_detail.total_price_idr / ppb_detail.quantity) + (receiving_detail.delivery_cost / receiving_detail.total_quantity_ri)) FROM ppb_detail WHERE ppb_detail.id = receiving_detail.ppb_detail_id)) AS total_receiving FROM receiving_detail JOIN receiving ON receiving_detail.receiving_id = receiving.id WHERE master_products.id = receiving_detail.product_id AND receiving.status = 2 AND receiving.acc_at < "' . $request->start_date . ' 00:00:00"' . $where_receiving . ')
            -
            IFNULL((SELECT SUM(sales_order_detail.hpp_total) AS total_sale FROM sales_order_detail JOIN sales_order ON sales_order_detail.sales_order_id = sales_order.id WHERE master_products.id = sales_order_detail.product_id AND sales_order.status = 2 AND sales_order.acc_at < "' . $request->start_date . ' 00:00:00"' . $where_sales_order . '), 0)
            +
            IFNULL((SELECT SUM(sale_return_detail.quantity * sale_return_detail.hpp) AS total_return FROM sale_return_detail JOIN sale_return ON sale_return_detail.sale_return_id = sale_return.id WHERE master_products.id = sale_return_detail.product_id AND sale_return.status = 2 AND sale_return.updated_at < "' . $request->start_date . ' 00:00:00"' . $where_sale_return . '), 0)
        ) AS opening_balance,

        (SELECT SUM(ppb_detail.quantity) AS purchase_qty FROM ppb_detail JOIN ppb ON ppb_detail.ppb_id = ppb.id WHERE master_products.id = ppb_detail.product_id AND ppb.status = 2 AND ppb.acc_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_ppb . ') purchase_qty,

        (SELECT SUM(ppb_detail.total_price_idr) AS total_purchase FROM ppb_detail JOIN ppb ON ppb_detail.ppb_id = ppb.id WHERE master_products.id = ppb_detail.product_id AND ppb.status = 2 AND ppb.acc_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_ppb . ') total_purchase,

        (SELECT SUM(receiving_detail.total_quantity_ri  - (SELECT SUM(receiving_detail_colly.quantity_recondition) FROM receiving_detail_colly WHERE receiving_detail_colly.receiving_detail_id = receiving_detail.id)) AS receiving_qty FROM receiving_detail JOIN receiving ON receiving_detail.receiving_id = receiving.id WHERE master_products.id = receiving_detail.product_id AND receiving.status = 2 AND receiving.acc_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_receiving . ') receiving_qty,

        (SELECT
        SUM((receiving_detail.total_quantity_ri - (SELECT SUM(receiving_detail_colly.quantity_recondition) FROM receiving_detail_colly WHERE receiving_detail_colly.receiving_detail_id = receiving_detail.id)) * (SELECT SUM((ppb_detail.total_price_idr / ppb_detail.quantity) + (receiving_detail.delivery_cost / receiving_detail.total_quantity_ri)) FROM ppb_detail WHERE ppb_detail.id = receiving_detail.ppb_detail_id)) AS total_receiving
        FROM receiving_detail
        JOIN receiving ON receiving_detail.receiving_id = receiving.id
        WHERE master_products.id = receiving_detail.product_id AND receiving.status = 2 AND receiving.acc_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_receiving . ')
        total_receiving,

        (SELECT SUM(sales_order_detail.quantity) AS sale_qty FROM sales_order_detail JOIN sales_order ON sales_order_detail.sales_order_id = sales_order.id WHERE master_products.id = sales_order_detail.product_id AND sales_order.status = 2 AND sales_order_detail.hpp_total IS NOT NULL AND sales_order.acc_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_sales_order . ') sale_qty,

        (SELECT SUM(sales_order_detail.hpp_total) AS total_sale FROM sales_order_detail JOIN sales_order ON sales_order_detail.sales_order_id = sales_order.id WHERE master_products.id = sales_order_detail.product_id AND sales_order.status = 2 AND sales_order.acc_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_sales_order . ') total_sale,

        (SELECT SUM(sale_return_detail.quantity) AS return_qty FROM sale_return_detail JOIN sale_return ON sale_return_detail.sale_return_id = sale_return.id WHERE master_products.id = sale_return_detail.product_id AND sale_return.status = 2 AND sale_return.updated_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_sale_return . ') return_qty,

        (SELECT SUM(sale_return_detail.quantity * sale_return_detail.hpp) AS total_return FROM sale_return_detail JOIN sale_return ON sale_return_detail.sale_return_id = sale_return.id WHERE master_products.id = sale_return_detail.product_id AND sale_return.status = 2 AND sale_return.updated_at BETWEEN "' . $request->start_date . ' 00:00:00" AND "' . $request->end_date . ' 23:59:59"' . $where_sale_return . ') total_return');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->editColumn('closing_qty', function (Product $model) {
            $closing_qty = $model->opening_qty + $model->receiving_qty - $model->sale_qty + $model->return_qty;
            return $closing_qty ? $closing_qty : '';
        });

        $table->editColumn('closing_balance', function (Product $model) {
            $closing_balance = $model->opening_balance + $model->total_receiving - $model->total_sale + $model->total_return;
            if ($closing_balance) {
                return 'Rp. ' . number_format($closing_balance, 2, ",", ".");
            }
            return null;
        });

        $table->editColumn('opening_qty', function (Product $model) {
            return $model->opening_qty ? $model->opening_qty : '';
        });

        $table->editColumn('opening_balance', function (Product $model) {
            return $model->opening_balance ? 'Rp. ' . number_format($model->opening_balance, 2, ",", ".") : '';
        });

        $table->editColumn('total_purchase', function (Product $model) {
            return $model->total_purchase !== null ? 'Rp. ' . number_format($model->total_purchase, 2, ",", ".") : '';
        });

        $table->editColumn('total_receiving', function (Product $model) {
            return $model->total_receiving !== null ? 'Rp. ' . number_format($model->total_receiving, 2, ",", ".") : '';
        });

        $table->editColumn('total_sale', function (Product $model) {
            return $model->total_sale ? 'Rp. ' . number_format($model->total_sale, 2, ",", ".") : '';
        });

        $table->editColumn('total_return', function (Product $model) {
            return $model->total_return ? 'Rp. ' . number_format($model->total_return, 2, ",", ".") : '';
        });

        return $table->make(true);
    }
}
