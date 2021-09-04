<?php

namespace App\DataTables\Finance;

use App\DataTables\Table;
use App\Entities\Finance\MarketplaceReceipt;
use App\Entities\Sale\SalesOrder;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MarketplaceReceiptTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {
        $superuser = Auth::guard('superuser')->user();

        $model = MarketplaceReceipt::select('id', 'code', 'total', 'payment', 'cost_1', 'cost_2', 'cost_3', 'status', 'paid', 'created_at')
                    ->where('created_by', $superuser->id)->where('status', 0);

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (MarketplaceReceipt $model) {
            return '';
        });

        $table->addColumn('customer', function (MarketplaceReceipt $model) {
            $sales_order = SalesOrder::where('code', $model->code)->first();
            return $sales_order->customer_marketplace;
        });

        $table->editColumn('total', function (MarketplaceReceipt $model) {
            return $model->price_format($model->total);
        });

        $table->editColumn('payment', function (MarketplaceReceipt $model) {
            return $model->price_format($model->payment);
        });

        $table->editColumn('cost_1', function (MarketplaceReceipt $model) {
            return $model->price_format($model->cost_1);
        });

        $table->editColumn('cost_2', function (MarketplaceReceipt $model) {
            return $model->price_format($model->cost_2);
        });

        $table->editColumn('cost_3', function (MarketplaceReceipt $model) {
            return $model->price_format($model->cost_3);
        });

        $table->editColumn('paid', function (MarketplaceReceipt $model) {
            return $model->price_format($model->paid);
        });

        $table->addColumn('total_data', function (MarketplaceReceipt $model) {
            return $model->total;
        });

        $table->addColumn('payment_data', function (MarketplaceReceipt $model) {
            return $model->payment;
        });

        $table->addColumn('cost_1_data', function (MarketplaceReceipt $model) {
            return $model->cost_1;
        });

        $table->addColumn('cost_2_data', function (MarketplaceReceipt $model) {
            return $model->cost_2;
        });

        $table->addColumn('cost_3_data', function (MarketplaceReceipt $model) {
            return $model->cost_3;
        });

        $table->editColumn('created_at', function (MarketplaceReceipt $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j/m/Y'),
              'timestamp' => $model->created_at
            ];
        });

        return $table->make(true);
    }
}