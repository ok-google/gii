<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\Customer;
use Carbon\Carbon;

class CustomerTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $model = Customer::select('id', 'category_id', 'type_id', 'code', 'name', 'status', 'created_at');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (Customer $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });

        $table->editColumn('status', function (Customer $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (Customer $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (Customer $model) {
            $view = route('superuser.master.customer.show', $model);
            $edit = route('superuser.master.customer.edit', $model);
            $destroy = route('superuser.master.customer.destroy', $model);

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