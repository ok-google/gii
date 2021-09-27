<?php

namespace App\DataTables\TransactionReport;

use App\DataTables\Table;
use App\Entities\Purchasing\Receiving;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use App\Entities\Inventory\Recondition;
use App\Entities\Inventory\ReconditionDetail;
use \Carbon\Carbon;

class ReconditionReportTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        $model = Recondition::select('id', 'code', 'warehouse_id', 'status', 'created_at');
        if ($request->warehouse != 'all') {
            // dd($request->warehouse);
            $model = $model->where('warehouse_id', $request->warehouse);
        } else {
            $model = $model->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
        }
        $model = $model->where('status', 2);
        dd($model);
        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        $table->addIndexColumn();

        $table->setRowClass(function (Recondition $model) {
            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                case $model::STATUS['ACC']:
                    return 'table-success';
                default:
                    return '';
            }
        });

        $table->editColumn('warehouse_id', function (Recondition $model) {
            return $model->warehouse->name;
        });
        
        $table->editColumn('status', function (Recondition $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (Recondition $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (Recondition $model) {
            $view = route('superuser.inventory.recondition.show', $model);
            $edit = route('superuser.inventory.recondition.edit', $model);
            $destroy = route('superuser.inventory.recondition.destroy', $model);
            $acc = route('superuser.inventory.recondition.acc', $model);

            if ($model->status == $model::STATUS['DELETED'] || $model->status == $model::STATUS['ACC']) {
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-eye\"></i>
                        </button>
                    </a>
                ";
            }
            
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
        });

        return $table->make(true);
    }
}
