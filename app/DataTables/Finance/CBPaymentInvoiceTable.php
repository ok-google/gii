<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Finance\CBPaymentInvoice;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CBPaymentInvoiceTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $superuser = Auth::guard('superuser')->user();

        $model = CBPaymentInvoice::select('id', 'code', 'status', 'type', 'branch_office_id', 'created_at')
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

        $table->setRowClass(function (CBPaymentInvoice $model) {

            switch ($model->status) {
                case $model::STATUS['DELETED']:
                    return 'table-danger';
                default:
                    return '';
            }
        });

        $table->editColumn('created_at', function (CBPaymentInvoice $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('status', function (CBPaymentInvoice $model) {
            return $model->status();
        });

        $table->editColumn('date_filter', function (CBPaymentInvoice $model) {
            return Carbon::parse($model->created_at)->format('d/m/Y');
        });

        $table->addColumn('action', function (CBPaymentInvoice $model) {
            $view = route('superuser.finance.payment_invoice.show', $model);
            $edit = route('superuser.finance.payment_invoice.edit', $model);
            $destroy = route('superuser.finance.payment_invoice.destroy', $model);
            $acc = route('superuser.finance.payment_invoice.acc', $model);
            $pdf = route('superuser.finance.payment_invoice.pdf', $model);

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
                        <a href=\"{$pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-info\" title=\"PDF\">
                                <i class=\"fa fa-file-pdf-o\"></i>
                            </button>
                        </a>
                    ";
            }

        });

        return $table->make(true);
    }
}