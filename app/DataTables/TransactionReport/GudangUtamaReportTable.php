<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Purchasing\Receiving;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use \Carbon\Carbon;

class GudangUtamaReportTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = Receiving::where('receiving.status', Receiving::STATUS['ACC'])
            ->where(function ($query) use ($request) {
                if ($request->warehouse != 'all') {
                    $query->where('receiving.warehouse_id', $request->warehouse);
                } else {
                    $query->whereIn('receiving.warehouse_id', MasterRepo::warehouses_by_category(1)->pluck('id')->toArray());
                }
            })
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
            ->join('master_products', 'receiving_detail.product_id', '=', 'master_products.id')
            ->join('master_warehouses', 'receiving.warehouse_id', '=', 'master_warehouses.id')
            ->join('receiving_detail_colly', function ($join) use ($request) {
                $join->on('receiving_detail.id', '=', 'receiving_detail_colly.receiving_detail_id')
                    ->whereNotExists(function ($query) {
                        $query->select(\DB::raw(1))
                            ->from('mutation_detail')
                            ->join('mutation', function ($join) {
                                $join->on('mutation_detail.mutation_id', '=', 'mutation.id')
                                    ->where('mutation.status', 2);
                            })
                            ->whereRaw('receiving_detail_colly.id = mutation_detail.receiving_detail_colly_id');
                    });
            })
            ->selectRaw('master_warehouses.name as warehouse, receiving.acc_at as receiving_date, master_products.code as sku, master_products.name as product, receiving_detail_colly.code as barcode, receiving_detail_colly.quantity_ri as qty, receiving_detail.total_quantity_ri as stock');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->editColumn('receiving_date', function (Receiving $model) {
            return Carbon::parse($model->receiving_date)->format('d/m/Y');
        });

        return $table->make(true);
    }
}
