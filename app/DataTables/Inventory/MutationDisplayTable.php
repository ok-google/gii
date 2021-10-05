<?php

namespace App\DataTables\Inventory;

use App\DataTables\Table;
use App\Entities\Inventory\MutationDisplay;
use Carbon\Carbon;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;

class MutationDisplayTable extends Table
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

        $model = MutationDisplay::select('mutation_display.id', 'mutation_display.code', 'from.name as warehouse_from', 'to.name as warehouse_to', 'mutation_display.status', 'mutation_display.created_at')
        ->join('master_warehouses as from', 'mutation_display.warehouse_from' ,'=', 'from.id')
        ->join('master_warehouses as to', 'mutation_display.warehouse_to' ,'=', 'to.id')
        ->whereIn('warehouse_from', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
        $model = $model->whereBetween("mutation_display.created_at", [$from." 00:00:00",$to." 23:59:59"]);

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        $table->addIndexColumn();

        $table->setRowClass(function (MutationDisplay $model) {
            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                case $model::STATUS['ACC']:
                    return 'table-success';
                default:
                    return '';
            }
        });

        $table->editColumn('warehouse_from', function (MutationDisplay $model) {
            return $model->warehouse_from;
        });

        $table->editColumn('warehouse_to', function (MutationDisplay $model) {
            return $model->warehouse_to;
        });
        
        $table->editColumn('status', function (MutationDisplay $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (MutationDisplay $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (MutationDisplay $model) {
            $view = route('superuser.inventory.mutation_display.show', $model);
            $edit = route('superuser.inventory.mutation_display.edit', $model);
            $destroy = route('superuser.inventory.mutation_display.destroy', $model);
            $acc = route('superuser.inventory.mutation_display.acc', $model);

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