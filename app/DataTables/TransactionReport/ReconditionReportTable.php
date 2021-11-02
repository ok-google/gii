<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Purchasing\Receiving;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use App\Entities\Inventory\Recondition;
use App\Entities\Inventory\ReconditionDetail;
use \Carbon\Carbon;
use DB;

class ReconditionReportTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $from = date("Y-m-d");
        $to = date("Y-m-d");

        if($request->from??false){
            $from = Carbon::parse($request->from)->format('Y-m-d');
            $to = Carbon::parse($request->to)->format('Y-m-d');
        }

        $model = ReconditionDetail::select(DB::raw('
        recondition_detail.id, 
                b.code, 
                h.name as warehouse, 
                b.status, 
                b.created_at,
                CASE
                    WHEN recondition_detail.receiving_detail_colly_id IS NOT NULL THEN "QC_UTAMA"
                    WHEN recondition_detail.quality_control2_id IS NOT NULL THEN "QC_DISPLAY"
                    WHEN recondition_detail.sale_return_detail_id IS NOT NULL THEN "SALE_RETURN"
                    WHEN recondition_detail.recondition_residual_id IS NOT NULL THEN "RESIDUAL"
                END AS type_text,
                CASE
                    WHEN recondition_detail.receiving_detail_colly_id IS NOT NULL THEN c.description
                    WHEN recondition_detail.quality_control2_id IS NOT NULL THEN d.description
                    WHEN recondition_detail.sale_return_detail_id IS NOT NULL THEN e.description
                    WHEN recondition_detail.recondition_residual_id IS NOT NULL THEN f.description
                END AS keterangan,
                CASE
                    WHEN recondition_detail.receiving_detail_colly_id IS NOT NULL THEN c.date_recondition
                    WHEN recondition_detail.quality_control2_id IS NOT NULL THEN d.created_at
                    WHEN recondition_detail.sale_return_detail_id IS NOT NULL THEN e.updated_at
                    WHEN recondition_detail.recondition_residual_id IS NOT NULL THEN f.created_at
                END AS date_in,
                CASE
                    WHEN recondition_detail.receiving_detail_colly_id IS NOT NULL THEN c.quantity_recondition
                    WHEN recondition_detail.quality_control2_id IS NOT NULL THEN d.quantity
                    WHEN recondition_detail.sale_return_detail_id IS NOT NULL THEN e.quantity
                    WHEN recondition_detail.recondition_residual_id IS NOT NULL THEN f.quantity
                END AS quantity,
                recondition_detail.quantity_recondition as quantity_recondition,
                recondition_detail.quantity_disposal as quantity_disposal'));
        $model = $model->leftJoin("recondition as b", "recondition_detail.recondition_id","=","b.id")
                ->leftJoin("receiving_detail_colly as c", "recondition_detail.receiving_detail_colly_id","=","c.id")
                ->leftJoin("quality_control2 as d", "recondition_detail.quality_control2_id","=","d.id")
                ->leftJoin("sale_return_detail as e", "recondition_detail.quality_control2_id","=","e.id")
                ->leftJoin("recondition_residual as f", "recondition_detail.recondition_residual_id","=","f.id")
                ->leftJoin("sale_return as g", "e.sale_return_id","=","g.id")
                ->leftJoin("master_warehouses as h", "b.warehouse_id","=","h.id");
        if ($request->warehouse != 'all') {
            // dd($request->warehouse);
            $model = $model->where('b.warehouse_id', $request->warehouse);
        } else {
            $model = $model->whereIn('b.warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
        }
        $model = $model->whereBetween("b.created_at", [$from." 00:00:00",$to." 23:59:59"]);
        $model = $model->where('b.status', 2);
        // dd($model);
        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        $table->addIndexColumn();

        // $table->setRowClass(function (ReconditionDetail $model) {
        //     switch ($model->recondition->status) {
        //         case $model::STATUS['DELETED']:
        //             return 'table-danger';
        //         case $model::STATUS['ACC']:
        //             return 'table-success';
        //         default:
        //             return '';
        //     }
        // });

        // $table->editColumn('warehouse_id', function (ReconditionDetail $model) {
        //     return $model->recondition->warehouse->name;
        // });
        
        // $table->editColumn('status', function (ReconditionDetail $model) {
        //     return $model->recondition->status();
        // });

        $table->editColumn('created_at', function (ReconditionDetail $model) {
            if($model->created_at??false){

                return [
                    'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
                    'timestamp' => $model->created_at
                  ];
            }else{
                return [
                    'display' => '',
                    'timestamp' => ''
                  ];
            }
        });


        return $table->make(true);
    }
}
