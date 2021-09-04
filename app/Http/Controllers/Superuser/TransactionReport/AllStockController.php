<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\Entities\Inventory\Mutation;
use App\Entities\Inventory\MutationDisplay;
use App\Entities\Inventory\ProductConversion;
use App\Entities\Inventory\Recondition;
use App\Entities\Inventory\StockAdjusment;
use App\Entities\Master\Product;
use App\Entities\Purchasing\Receiving;
use App\Entities\Purchasing\ReceivingDetailColly;
use App\Entities\QualityControl\QualityControl2;
use App\Entities\Sale\BuyBack;
use App\Entities\Sale\SaleReturn;
use App\Entities\Sale\SalesOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllStockController extends Controller
{
    public function json(Request $request)
    {
        $data = [];
        $collect = [];

        /**
         * Barang yang ada di gudang Utama
         * dihitung berdasarkan total barang masuk - total rekondisi - total mutasi
         * faktor penambah (total barang masuk)
         */
        $receivings = Receiving::where('receiving.status', Receiving::STATUS['ACC'])
            ->select(\DB::raw('receiving.warehouse_id, receiving_detail.product_id, SUM(receiving_detail.total_quantity_ri) as totalquantity'))
            ->join('receiving_detail', 'receiving.id', '=', 'receiving_detail.receiving_id')
            ->groupBy(['receiving_detail.total_quantity_ri', 'receiving.warehouse_id', 'receiving_detail.product_id'])
            ->get();

        foreach ($receivings as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] += $item->totalquantity;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = $item->totalquantity;
            }
        }

        /**
         * Barang yang ada di gudang Utama
         * faktor pengurang (total rekondisi + total mutasi)
         */
        $receivings = Receiving::where('receiving.status', Receiving::STATUS['ACC'])
            ->select(\DB::raw('receiving.warehouse_id, receiving_detail.product_id, SUM(CASE WHEN receiving_detail_colly.status_qc=1 AND receiving_detail_colly.quantity_recondition > 0 THEN receiving_detail_colly.quantity_recondition ELSE 0 END) AS totalrekondisi, SUM(CASE WHEN receiving_detail_colly.status_mutation=1 AND receiving_detail_colly.quantity_mutation > 0 and exists (select 1 from mutation_detail where mutation_detail.receiving_detail_colly_id = receiving_detail_colly.id) and exists (select 1 from mutation join mutation_detail on mutation.id = mutation_detail.mutation_id where mutation_detail.receiving_detail_colly_id = receiving_detail_colly.id and mutation.status = 2) THEN receiving_detail_colly.quantity_mutation ELSE 0 END) AS totalmutasi'))
            ->join('receiving_detail', 'receiving.id', '=', 'receiving_detail.receiving_id')
            ->join('receiving_detail_colly', 'receiving_detail.id', '=', 'receiving_detail_colly.receiving_detail_id')
            ->groupBy(['receiving.warehouse_id', 'receiving_detail.product_id'])
            ->get();

        foreach ($receivings as $item) {
            $total = $item->totalrekondisi + $item->totalmutasi;
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] -= $total;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = -$total;
            }
        }

        /**
         * Barang yang masuk ke gudang Reparasi
         * langsung dari receiving detail colly
         */
        $receiving_detail_collys = ReceivingDetailColly::where('status_qc', ReceivingDetailColly::STATUS_QC['USED'])
            ->where('receiving_detail_colly.quantity_recondition', '>', 0)
            ->select(\DB::raw('receiving_detail_colly.warehouse_reparation_id, receiving_detail.product_id, SUM(receiving_detail_colly.quantity_recondition) as totalrekondisi'))
            ->leftJoin('receiving_detail', 'receiving_detail.id', '=', 'receiving_detail_colly.receiving_detail_id')
            ->groupBy(['receiving_detail_colly.warehouse_reparation_id', 'receiving_detail.product_id'])
            ->get();
        foreach ($receiving_detail_collys as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_reparation_id])) {
                $collect[$item->product_id][$item->warehouse_reparation_id] += $item->totalrekondisi;
            } else {
                $collect[$item->product_id][$item->warehouse_reparation_id] = $item->totalrekondisi;
            }
        }

        /**
         * Barang yang masuk ke gudang display melalui mutasi
         */
        $mutations = Mutation::where('status', Mutation::STATUS['ACC'])
            ->select(\DB::raw('mutation.warehouse_id, receiving_detail.product_id, SUM(receiving_detail_colly.quantity_mutation) as totalmutasi'))
            ->leftJoin('mutation_detail', 'mutation_detail.mutation_id', '=', 'mutation.id')
            ->leftJoin('receiving_detail_colly', 'receiving_detail_colly.id', '=', 'mutation_detail.receiving_detail_colly_id')
            ->leftJoin('receiving_detail', 'receiving_detail.id', '=', 'receiving_detail_colly.receiving_detail_id')
            ->groupBy(['mutation.warehouse_id', 'receiving_detail.product_id'])
            ->get();

        foreach ($mutations as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] += $item->totalmutasi;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = $item->totalmutasi;
            }
        }

        /**
         * Barang yang keluar dari Gudang Display dengan QC2
         */
        $quality_controls_out = QualityControl2::select(\DB::raw('warehouse_id, product_id, SUM(quantity) as totalkeluar'))
            ->groupBy(['warehouse_id', 'product_id'])
            ->get();

        foreach ($quality_controls_out as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] -= $item->totalkeluar;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = -$item->totalkeluar;
            }
        }

        /**
         * Barang yang masuk ke Gudang Reparasi dengan QC2
         */
        $quality_controls_in = QualityControl2::select(\DB::raw('warehouse_reparation_id, product_id, SUM(quantity) as totalmasuk'))
            ->groupBy(['warehouse_reparation_id', 'product_id'])
            ->get();

        foreach ($quality_controls_in as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_reparation_id])) {
                $collect[$item->product_id][$item->warehouse_reparation_id] += $item->totalmasuk;
            } else {
                $collect[$item->product_id][$item->warehouse_reparation_id] = $item->totalmasuk;
            }
        }

        /**
         * Barang yang terjual dari gudang display dan SUDAH DO
         */
        $sales_orders = SalesOrder::select(\DB::raw('sales_order.warehouse_id, sales_order_detail.product_id, SUM(sales_order_detail.quantity) as totalquantity'))
            ->leftJoin('sales_order_detail', 'sales_order_detail.sales_order_id', '=', 'sales_order.id')
            ->where('status', SalesOrder::STATUS['ACC'])
            ->whereHas('delivery_order_details', function ($query) {
                $query->where('status_validate', '1');
            })
            ->groupBy(['sales_order.warehouse_id', 'sales_order_detail.product_id'])
            ->get();

        foreach ($sales_orders as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] -= $item->totalquantity;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = -$item->totalquantity;
            }
        }

        /**
         * Barang yang terjual dari gudang display dan BELUM DO
         */
        $sales_orders = SalesOrder::where('status', SalesOrder::STATUS['ACC'])
            ->where(function ($query) {
                $query->whereHas('delivery_order_details', function ($query) {
                    $query->where('status_validate', '0');
                })->orDoesntHave('delivery_order_details');
            })
            ->select(\DB::raw('sales_order.warehouse_id, sales_order_detail.product_id, SUM(sales_order_detail.quantity) as totalquantity'))
            ->leftJoin('sales_order_detail', 'sales_order_detail.sales_order_id', '=', 'sales_order.id')
            ->groupBy(['sales_order.warehouse_id', 'sales_order_detail.product_id'])
            ->get();

        foreach ($sales_orders as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] -= $item->totalquantity;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = -$item->totalquantity;
            }
        }

        /**
         * Barang masuk gudang display dari rekondisi
         */
        $reconditions = Recondition::where('status', Recondition::STATUS['ACC'])
            ->select(\DB::raw('recondition.warehouse_id, recondition_valid.product_id, SUM(recondition_valid.quantity) as totalmasuk'))
            ->join('recondition_valid', 'recondition_valid.recondition_id', '=', 'recondition.id')
            ->groupBy(['recondition.warehouse_id', 'recondition_valid.product_id'])
            ->get();

        foreach ($reconditions as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] += $item->totalmasuk;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = $item->totalmasuk;
            }
        }

        /**
         * Barang keluar dari gudang reparasi status valid
         */
        $reconditions = Recondition::where('status', Recondition::STATUS['ACC'])
            ->select(\DB::raw('recondition.warehouse_reparation_id, recondition_valid.product_id, SUM(recondition_valid.quantity) as totalkeluar'))
            ->join('recondition_valid', 'recondition_valid.recondition_id', '=', 'recondition.id')
            ->groupBy(['recondition.warehouse_reparation_id', 'recondition_valid.product_id'])
            ->get();

        foreach ($reconditions as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_reparation_id])) {
                $collect[$item->product_id][$item->warehouse_reparation_id] -= $item->totalkeluar;
            } else {
                $collect[$item->product_id][$item->warehouse_reparation_id] = -$item->totalkeluar;
            }
        }

        /**
         * Barang keluar dari gudang reparasi status disposal
         */
        $reconditions = Recondition::where('status', Recondition::STATUS['ACC'])
            ->select(\DB::raw('recondition.warehouse_reparation_id, recondition_disposal.product_id, SUM(recondition_disposal.quantity) as totalkeluar'))
            ->join('recondition_disposal', 'recondition_disposal.recondition_id', '=', 'recondition.id')
            ->groupBy(['recondition.warehouse_reparation_id', 'recondition_disposal.product_id'])
            ->get();

        foreach ($reconditions as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_reparation_id])) {
                $collect[$item->product_id][$item->warehouse_reparation_id] -= $item->totalkeluar;
            } else {
                $collect[$item->product_id][$item->warehouse_reparation_id] = -$item->totalkeluar;
            }
        }

        /**
         * Barang masuk ke gudang reparasi melalui sale return
         */
        $sale_returns = SaleReturn::where('status', SaleReturn::STATUS['ACC'])
            ->select(\DB::raw('sale_return.warehouse_reparation_id, sale_return_detail.product_id, SUM(sale_return_detail.quantity) as totalmasuk'))
            ->leftJoin('sale_return_detail', 'sale_return_detail.sale_return_id', '=', 'sale_return.id')
            ->groupBy(['sale_return.warehouse_reparation_id', 'sale_return_detail.product_id'])
            ->get();

        foreach ($sale_returns as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_reparation_id])) {
                $collect[$item->product_id][$item->warehouse_reparation_id] += $item->totalmasuk;
            } else {
                $collect[$item->product_id][$item->warehouse_reparation_id] = $item->totalmasuk;
            }
        }

        /**
         * Barang masuk gudang display melalui buy back
         */
        $buy_backs = BuyBack::where('buy_back.status', BuyBack::STATUS['ACC'])
            ->where('buy_back.disposal', '0')
            ->select(\DB::raw('buy_back.warehouse_id, sales_order_detail.product_id, SUM(buy_back_detail.buy_back_qty) as totalmasuk'))
            ->leftJoin('buy_back_detail', 'buy_back_detail.buy_back_id', '=', 'buy_back.id')
            ->leftJoin('sales_order_detail', 'sales_order_detail.id', '=', 'buy_back_detail.sales_order_detail_id')
            ->groupBy(['buy_back.warehouse_id', 'sales_order_detail.product_id'])
            ->get();

        foreach ($buy_backs as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] += $item->totalmasuk;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = $item->totalmasuk;
            }
        }

        /**
         * Stock Adjusment ke gudang display
         */
        $stock_adjusments = StockAdjusment::where('status', StockAdjusment::STATUS['ACC'])
            ->select(\DB::raw('stock_adjusment.warehouse_id, stock_adjusment_detail.product_id, SUM(CASE WHEN stock_adjusment.minus = 0 THEN stock_adjusment_detail.qty ELSE 0 END) AS totalmasuk, SUM(CASE WHEN stock_adjusment.minus != 0 THEN stock_adjusment_detail.qty ELSE 0 END) AS totalkeluar'))
            ->join('stock_adjusment_detail', 'stock_adjusment_detail.stock_adjusment_id', '=', 'stock_adjusment.id')
            ->groupBy(['stock_adjusment.warehouse_id', 'stock_adjusment_detail.product_id'])
            ->get();

        foreach ($stock_adjusments as $item) {
            $total = $item->totalmasuk - $item->totalkeluar;
            if (!empty($collect[$item->product_id][$item->warehouse_id])) {
                $collect[$item->product_id][$item->warehouse_id] += $total;
            } else {
                $collect[$item->product_id][$item->warehouse_id] = $total;
            }
        }

        /**
         * Mutation Display (Antar gudang display)
         * barang keluar
         */
        $mutation_display = MutationDisplay::where('status', MutationDisplay::STATUS['ACC'])
            ->select(\DB::raw('mutation_display.warehouse_from, mutation_display_detail.product_id, SUM(mutation_display_detail.qty) AS totalkeluar'))
            ->join('mutation_display_detail', 'mutation_display.id', '=', 'mutation_display_detail.mutation_display_id')
            ->groupBy(['mutation_display.warehouse_from', 'mutation_display_detail.product_id'])
            ->get();

        foreach ($mutation_display as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_from])) {
                $collect[$item->product_id][$item->warehouse_from] -= $item->totalkeluar;
            } else {
                $collect[$item->product_id][$item->warehouse_from] = -$item->totalkeluar;
            }
        }

        /**
         * Mutation Display (Antar gudang display)
         * barang masuk
         */
        $mutation_display = MutationDisplay::where('status', MutationDisplay::STATUS['ACC'])
            ->select(\DB::raw('mutation_display.warehouse_to, mutation_display_detail.product_id, SUM(mutation_display_detail.qty) AS totalmasuk'))
            ->join('mutation_display_detail', 'mutation_display.id', '=', 'mutation_display_detail.mutation_display_id')
            ->groupBy(['mutation_display.warehouse_to', 'mutation_display_detail.product_id'])
            ->get();

        foreach ($mutation_display as $item) {
            if (!empty($collect[$item->product_id][$item->warehouse_to])) {
                $collect[$item->product_id][$item->warehouse_to] += $item->totalmasuk;
            } else {
                $collect[$item->product_id][$item->warehouse_to] = $item->totalmasuk;
            }
        }

        /**
         * Product Conversion
         * Barang Keluar
         */
        $product_conversion = ProductConversion::where('status', ProductConversion::STATUS['ACC'])
            ->selectRaw('warehouse_id, product_from, SUM(product_conversion_detail.qty) AS totalkeluar')
            ->join('product_conversion_detail', 'product_conversion.id', '=', 'product_conversion_detail.product_conversion_id')
            ->groupBy(['warehouse_id', 'product_from'])
            ->get();

        foreach ($product_conversion as $item) {
            if (!empty($collect[$item->product_from][$item->warehouse_id])) {
                $collect[$item->product_from][$item->warehouse_id] -= $item->totalkeluar;
            } else {
                $collect[$item->product_from][$item->warehouse_id] = -$item->totalkeluar;
            }
        }

        /**
         * Product Conversion
         * Barang Masuk
         */
        $product_conversion = ProductConversion::where('status', ProductConversion::STATUS['ACC'])
            ->selectRaw('warehouse_id, product_to, SUM(product_conversion_detail.qty) AS totalmasuk')
            ->join('product_conversion_detail', 'product_conversion.id', '=', 'product_conversion_detail.product_conversion_id')
            ->groupBy(['warehouse_id', 'product_to'])
            ->get();

        foreach ($product_conversion as $item) {
            if (!empty($collect[$item->product_to][$item->warehouse_id])) {
                $collect[$item->product_to][$item->warehouse_id] += $item->totalmasuk;
            } else {
                $collect[$item->product_to][$item->warehouse_id] = $item->totalmasuk;
            }
        }

        $warehouses_ids = MasterRepo::warehouses_by_branch()->pluck('id')->toArray();
        // COLLECT
        foreach ($collect as $key => $value) {
            $product = Product::find($key);

            $row = [];
            $row[] = $product->code;
            $row[] = $product->category->name;
            $row[] = $product->name;
            foreach ($warehouses_ids as $k => $v) {
                if (empty($value[$v])) {
                    $row[] = 0;
                } else {
                    $row[] = $value[$v];
                }
            }

            $data['data'][] = $row;
        }

        if (empty($collect)) {
            $data['data'] = '';
        }

        return $data;
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('all stock-manage')) {
            return abort(403);
        }

        $data['categories'] = MasterRepo::product_categories();
        $data['warehouses'] = MasterRepo::warehouses_by_branch();

        return view('superuser.transaction_report.all_stock.index', $data);
    }
}
