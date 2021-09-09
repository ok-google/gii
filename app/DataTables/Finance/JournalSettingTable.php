<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Accounting\JournalSetting;
use Carbon\Carbon;

class JournalSettingTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        return JournalSetting::select('id', 'name', 'status', 'created_at');
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (JournalSetting $model) {
            return $model->status == 0 ? 'table-danger' : '';
        });
        
        $table->editColumn('status', function (JournalSetting $model) {
            return $model->status();
        });

        $table->editColumn('created_at', function (JournalSetting $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('action', function (JournalSetting $model) {
            $edit = route('superuser.finance.journal_setting.edit', $model);
            $destroy = route('superuser.finance.journal_setting.destroy', $model);

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

        return $table->make(true);
    }
}