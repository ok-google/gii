<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Purchasing\Receiving;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use App\Entities\Sale\SalesOrder;
use App\Entities\Sale\SalesOrderDetail;
use \Carbon\Carbon;

class OrderDetailReportTable extends Table
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

        $model = SalesOrderDetail::select('sales_order_detail.id', 'sales_order.code', 'sales_order.status', 'sales_order.created_at', 'sales_order.created_at', 'sales_order_detail.product_id', 'sales_order_detail.quantity', 'sales_order_detail.price', 'sales_order_detail.total', 'sales_order_detail.hpp_total');

        $model = $model->leftJoin("sales_order", "sales_order_detail.sales_order_id","=","sales_order.id");
        // $model = $model->leftJoin("master_products", "sales_order_detail.warehouse_id", "=", "master_warehouses.id");
        // dd($request->warehouse);
        if ($request->product != 'all') {
            // dd($request->warehouse);
            $model = $model->where('sales_order_detail.product_id', $request->product);
        }
        $model = $model->where('sales_order.status', 2);
        
        $model = $model->whereBetween("sales_order.created_at", [$from." 00:00:00",$to." 23:59:59"]);
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

        $table->setRowClass(function (SalesOrderDetail $model) {
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

        $table->editColumn('created_at', function (SalesOrderDetail $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('product_id', function (SalesOrderDetail $model) {
            return $model->product->name;
        });

        $table->addColumn('product_sku', function(SalesOrderDetail $model){
            return $model->product->code;
        });

        $table->addColumn('status', function(){
            return 'acc';
        });

        $table->addColumn('action', function (SalesOrderDetail $model) {
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
