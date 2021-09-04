<?php

namespace App\DataTables\Accounting;

use App\DataTables\Table;
use App\Entities\Accounting\Coa;
use App\Entities\Account\Superuser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CoaTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $superuser = Superuser::find(Auth::guard('superuser')->id());
        
        $model = Coa::select('id', 'code', 'name', 'group', 'parent_level_1', 'parent_level_2', 'parent_level_3', 'status')
                    ->where('type', $superuser->type)
                    ->where('branch_office_id', $superuser->branch_office_id);

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Coa $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });
        
        $table->editColumn('status', function (Coa $model) {
            return $model->status();
        });

        $table->editColumn('group', function (Coa $model) {
            return $model->group();
        });

        $table->editColumn('parent_level_1', function (Coa $model) {
            return $model->parent_level_one['name'] ?? '';
        });

        $table->editColumn('parent_level_2', function (Coa $model) {
            return $model->parent_level_two['name'] ?? '';
        });

        $table->editColumn('parent_level_3', function (Coa $model) {
            return $model->parent_level_three['name'] ?? '';
        });

        // $table->editColumn('created_at', function (Coa $model) {
        //     return [
        //       'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
        //       'timestamp' => $model->created_at
        //     ];
        // });

        $table->addColumn('action', function (Coa $model) {
            $view = route('superuser.accounting.coa.show', $model);
            $edit = route('superuser.accounting.coa.edit', $model);
            $destroy = route('superuser.accounting.coa.destroy', $model);

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
                <a href=\"{$view}\">
                    <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                        <i class=\"fa fa-eye\"></i>
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