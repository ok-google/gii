<?php

namespace App\DataTables\Sale;

use App\DataTables\Table;
use App\Entities\Sale\SaleReturn;
use Carbon\Carbon;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;

class SaleReturnTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = SaleReturn::join('delivery_order_detail', 'sale_return.delivery_order_id', '=', 'delivery_order_detail.id')
            ->select('sale_return.id', 'sale_return.code', 'delivery_order_detail.code as delivery_order_code', 'sale_return.status', 'sale_return.created_at')
            ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());

        if(isset($request->from)){
            $model = $model->whereDate("sale_return.created_at", ">=", $request->from)->whereDate("sale_return.created_at", "<=", $request->to);
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

        $table->setRowClass(function (SaleReturn $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (SaleReturn $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (SaleReturn $model) {
            return [
                'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
                'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (SaleReturn $model) {
            $view = route('superuser.sale.sale_return.show', $model);
            $destroy = route('superuser.sale.sale_return.destroy', $model);
            $acc = route('superuser.sale.sale_return.acc', $model);
            $pdf = route('superuser.sale.sale_return.pdf', $model);


            if ($model->status == $model::STATUS['ACC']) {
                return "
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
            }

            return "
                <a href=\"{$view}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                        <i class=\"fa fa-eye\"></i>
                    </button>
                </a>
                <a href=\"javascript:saveConfirmation2('{$acc}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"ACC\">
                        <i class=\"fa fa-check\"></i>
                    </button>
                </a>
                <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                        <i class=\"fa fa-times\"></i>
                    </button>
                </a>
            ";
        });

        return $table->make(true);
    }
}
