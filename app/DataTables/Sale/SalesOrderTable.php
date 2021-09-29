<?php

namespace App\DataTables\Sale;

use App\DataTables\Table;
use App\Entities\Sale\SalesOrder;
use Carbon\Carbon;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;

class SalesOrderTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        switch ($this->show) {
            case 'default':
              $model = SalesOrder::select('id', 'code', 'marketplace_order', 'customer_id', 'customer_marketplace', 'store_name', 'ekspedisi_id', 'ekspedisi_marketplace', 'status', 'status_sales_order', 'created_at')->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())->where('status', 1);
              if($request->from??false){
                  $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
              }
              break;
            case 'acc':
              $model = SalesOrder::select('id', 'code', 'marketplace_order', 'customer_id', 'customer_marketplace', 'store_name', 'ekspedisi_id', 'ekspedisi_marketplace', 'status', 'status_sales_order', 'created_at')->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())->where('status', 2);
              if($request->from??false){
                  $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
              }
              break;
            case 'all':
              $model = SalesOrder::select('id', 'code', 'marketplace_order', 'customer_id', 'customer_marketplace', 'store_name', 'ekspedisi_id', 'ekspedisi_marketplace', 'status', 'status_sales_order', 'created_at')->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
              if($request->from??false){
                  $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
              }
              break;
            default:
              $model = SalesOrder::select('id', 'code', 'marketplace_order', 'customer_id', 'customer_marketplace', 'store_name', 'ekspedisi_id', 'ekspedisi_marketplace', 'status', 'status_sales_order', 'created_at')->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())->where('status', 1);
              if($request->from??false){
                  $model = $model->whereDate("created_at", ">=", $request->from)->whereDate("created_at", "<=", $request->to);
              }
              break;
          }
  
          return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        $table->addIndexColumn();

        $table->setRowClass(function (SalesOrder $model) {
            return '';
        });

        

        $table->editColumn('customer_marketplace', function (SalesOrder $model) {
            if($model::MARKETPLACE_ORDER['Non Marketplace'] == $model->marketplace_order) {
                return $model->customer->name;
            } else {
                return $model->customer_marketplace;
            }
            
        });
        
        $table->editColumn('status', function (SalesOrder $model) {
            return $model->status();
        });

        $table->editColumn('ekspedisi_marketplace', function (SalesOrder $model) {
            if($model::MARKETPLACE_ORDER['Non Marketplace'] == $model->marketplace_order) {
                return $model->ekspedisi->name ?? '-';
            } else {
                return $model->ekspedisi_marketplace ?? '-';
            }
        });

        $table->editColumn('created_at', function (SalesOrder $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('id', function (SalesOrder $model) {
            return '<span class="sales_order_id" style="display: none;">'.$model->id.'</span>';
        });

        $table->addColumn('action', function (SalesOrder $model) {
            $view = route('superuser.sale.sales_order.show', $model);
            $edit = route('superuser.sale.sales_order.edit', $model);
            $destroy = route('superuser.sale.sales_order.destroy', $model);
            $force_delete = route('superuser.sale.sales_order.force_delete', $model);
            $acc = route('superuser.sale.sales_order.acc', $model);
            $pdf = route('superuser.sale.sales_order.pdf', $model);

            if ($model->status == $model::STATUS['ACC']) {
                $action_view = "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                        <a href=\"{$pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"PDF\">
                                <i class=\"fa fa-file-pdf-o\"></i>
                            </button>
                        </a>
                ";
                if($model->status_sales_order != 1) {
                    $action_view .= "
                        <a href=\"javascript:deleteConfirmation('{$force_delete}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Force Delete\">
                                <i class=\"fa fa-trash\"></i>
                            </button>
                        </a>
                    ";
                }
                return $action_view;
            }

            if ($model->status == $model::STATUS['DELETED']) {
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-eye\"></i>
                        </button>
                    </a>
                ";
            }
            
            return "
                <a href=\"{$edit}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                        <i class=\"fa fa-pencil\"></i>
                    </button>
                </a>
                <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"fa fa-times\"></i>
                    </button>
                </a>
            ";
        });
        $table->rawColumns(['id', 'action']);
        return $table->make(true);
    }
}