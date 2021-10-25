<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Purchasing\Receiving;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use App\Entities\Inventory\ProductConversion;
use App\Entities\Inventory\ProductConversionDetail;
use \Carbon\Carbon;

class ConversionReportTable extends Table
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
// dd($request->from);
        $model = ProductConversionDetail::select('product_conversion_detail.id', 'product_conversion.code', 'master_warehouses.name', 'product_conversion.status', 'product_conversion.created_at', 'product_conversion.created_at', 'product_conversion_detail.product_from', 'product_conversion_detail.product_to', 'product_conversion_detail.qty');

        $model = $model->leftJoin("product_conversion", "product_conversion_detail.product_conversion_id","=","product_conversion.id");
        $model = $model->leftJoin("master_warehouses", "product_conversion.warehouse_id", "=", "master_warehouses.id");
        // dd($request->warehouse);
        if ($request->warehouse != 'all') {
            // dd($request->warehouse);
            $model = $model->where('product_conversion.warehouse_id', $request->warehouse);
        } else {
            $model = $model->whereIn('product_conversion.warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
        }
        $model = $model->whereBetween("product_conversion.created_at", [$from." 00:00:00",$to." 23:59:59"]);
        $model = $model->where('product_conversion.status', 2);
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

        $table->setRowClass(function (ProductConversionDetail $model) {
            return 'acc';
            // switch ($model->status) {
            //     case $model::STATUS['DELETED']:
            //         return 'table-danger';
            //     case $model::STATUS['ACC']:
            //         return 'table-success';
            //     default:
            //         return '';
            // }
        });

        // $table->editColumn('warehouse_name', function (ProductConversionDetail $model) {
        //     return $model->product_conversion->warehouse->name;
        // });
        
        // $table->editColumn('status', function (ProductConversionDetail $model) {
        //     return $model->status();
        // });

        $table->editColumn('created_at', function (ProductConversionDetail $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('product_from', function (ProductConversionDetail $model) {
            return $model->product_from_rel->name;
        });

        $table->editColumn('product_to', function (ProductConversionDetail $model) {
            return $model->product_to_rel->name;
        });
        $table->addColumn('status', function(){
            return 'acc';
        });
        $table->addColumn('action', function (ProductConversionDetail $model) {
            // $view = route('superuser.inventory.recondition.show', $model);
            // $edit = route('superuser.inventory.recondition.edit', $model);
            // $destroy = route('superuser.inventory.recondition.destroy', $model);
            // $acc = route('superuser.inventory.recondition.acc', $model);

            // if ($model->status == $model::STATUS['DELETED'] || $model->status == $model::STATUS['ACC']) {
            //     return "
            //         <a href=\"{$view}\">
            //             <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
            //                 <i class=\"fa fa-eye\"></i>
            //             </button>
            //         </a>
            //     ";
            // }
            
            return "
            ";
        });

        return $table->make(true);
    }
}
