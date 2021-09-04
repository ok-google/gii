<?php

namespace App\DataTables\Purchasing;

use App\DataTables\Table;
use App\Entities\Purchasing\Receiving;
use Carbon\Carbon;
use App\Repositories\MasterRepo;

class ReceivingTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = Receiving::select('id', 'code', 'status', 'warehouse_id', 'created_at')->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Receiving $model) {

            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                // case $model::STATUS['ACTIVE']:
                //     return 'table-primary';
                default:
                    return '';
            }
        });

        $table->editColumn('created_at', function (Receiving $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('status', function (Receiving $model) {
            return $model->status();
        });

        $table->editColumn('warehouse_id', function (Receiving $model) {
            return $model->warehouse->name;
        });

        $table->addColumn('action', function (Receiving $model) {
            $view = route('superuser.purchasing.receiving.show', $model);
            $edit = route('superuser.purchasing.receiving.step', $model);
            $destroy = route('superuser.purchasing.receiving.destroy', $model);
            $acc = route('superuser.purchasing.receiving.acc', $model);
            $pdf = route('superuser.purchasing.receiving.pdf', $model);
            $print_barcode = route('superuser.purchasing.receiving.print_barcode', $model);

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    return "
                        <a href=\"javascript:saveConfirmation2('{$acc}')\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"ACC\">
                                <i class=\"fa fa-check\"></i>
                            </button>
                        </a>
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
                case $model::STATUS['ACC']:
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
                        <a href=\"{$print_barcode}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-success\" title=\"Barcode\">
                                <i class=\"fa fa-barcode\"></i>
                            </button>
                        </a>
                    ";
                default:
                    return "
                        <a href=\"{$view}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                <i class=\"fa fa-eye\"></i>
                            </button>
                        </a>
                    ";
            }

        });

        return $table->make(true);
    }
}