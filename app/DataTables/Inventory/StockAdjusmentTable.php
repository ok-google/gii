<?php

namespace App\DataTables\Inventory;

use App\DataTables\Table;
use App\Entities\Inventory\StockAdjusment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class StockAdjusmentTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
    {
        // $from = date("Y-m-d");
        // $to = date("Y-m-d");

        // if($request->from??false){
        //     $from = Carbon::parse($request->from)->format('Y-m-d');
        //     $to = Carbon::parse($request->to)->format('Y-m-d');
        // }

        $superuser = Auth::guard('superuser')->user();

        $model = StockAdjusment::select('id', 'code', 'status', 'type', 'branch_office_id', 'created_at')
                    ->where('type', $superuser->type)
                    ->where('branch_office_id', $superuser->branch_office_id);

        // $model = $model->whereBetween("created_at", [$from." 00:00:00",$to." 23:59:59"]);

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        $table->addIndexColumn();

        $table->setRowClass(function (StockAdjusment $model) {

            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                default:
                    return '';
            }
        });

        $table->editColumn('created_at', function (StockAdjusment $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('status', function (StockAdjusment $model) {
            return $model->status();
        });

        $table->editColumn('date_filter', function (StockAdjusment $model) {
            return Carbon::parse($model->created_at)->format('d/m/Y');
        });

        $table->addColumn('action', function (StockAdjusment $model) {
            $view = route('superuser.inventory.stock_adjusment.show', $model);
            $edit = route('superuser.inventory.stock_adjusment.edit', $model);
            $destroy = route('superuser.inventory.stock_adjusment.destroy', $model);
            $acc = route('superuser.inventory.stock_adjusment.acc', $model);

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