<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Purchasing\Receiving;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReceivingReportTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    public function query(Request $request)
    {
        $model = Receiving::where('receiving.status', Receiving::STATUS['ACC'])
            ->whereIn('receiving.warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->join('receiving_detail', function ($join) use ($request) {
                $join->on('receiving.id', '=', 'receiving_detail.receiving_id')
                    ->where(function ($query) use ($request) {
                        if ($request->product != 'all') {
                            $query->where('product_id', $request->product);
                        } else {
                            $query;
                        }
                    });
            })
            ->join('ppb', function ($join) use ($request) {
                $join->on('receiving_detail.ppb_id', '=', 'ppb.id')
                    ->where(function ($query) use ($request) {
                        if ($request->supplier != 'all') {
                            $query->where('supplier_id', $request->supplier);
                        } else {
                            $query;
                        }
                    });
            })
            ->join('master_supplier', 'ppb.supplier_id', '=', 'master_supplier.id')
            ->join('master_products', 'receiving_detail.product_id', '=', 'master_products.id')
            ->join('ppb_detail', 'receiving_detail.ppb_detail_id', '=', 'ppb_detail.id')
            ->selectRaw(' receiving.pbm_date as pbm_date,
             master_supplier.name as supplier,
             ppb.code as ppb, 
             receiving.code as pbm, 
             master_products.code as sku, 
             ppb_detail.unit_price as unit_price, 
             ppb_detail.quantity as ppb_qty, 
             receiving_detail.total_quantity_ri as ri_qty, 
             (ppb_detail.quantity - receiving_detail.total_quantity_ri) as incoming, 
             receiving_detail.total_quantity_colly as colly_qty, 
             ((ppb_detail.local_freight_cost * receiving_detail.total_quantity_ri) / ppb_detail.quantity) as domestic_cost, 
             ((ppb_detail.komisi * receiving_detail.total_quantity_ri) / ppb_detail.quantity) as komisi, 
             ((ppb_detail.total_price_rmb * receiving_detail.total_quantity_ri) / ppb_detail.quantity) as total_price_rmb, 
             ppb_detail.kurs as kurs, 
             receiving_detail.delivery_cost as delivery_cost, 
             receiving_detail.description, 
             receiving.no_container as container, 
             ((ppb_detail.total_price_idr / ppb_detail.quantity) + (receiving_detail.delivery_cost / receiving_detail.total_quantity_ri)) as hpp');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        
        $table->editColumn('pbm_date', function (Receiving $model) {
            return [
              'display' => Carbon::parse($model->pbm_date)->format('d-m-Y'),
              'timestamp' => $model->pbm_date
            ];
        });

        return $table->make(true);
    }
}
