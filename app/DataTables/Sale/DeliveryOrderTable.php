<?php

namespace App\DataTables\Sale;

use App\DataTables\Table;
use App\Entities\Sale\DeliveryOrder;
use Carbon\Carbon;
use App\Repositories\MasterRepo;

class DeliveryOrderTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query()
    {   
        $warehouse_ids = MasterRepo::warehouses_by_branch()->pluck('id')->toArray();

        $model = DeliveryOrder::
                join('delivery_order_detail', 'delivery_order.id', '=', 'delivery_order_detail.delivery_order_id')
                ->join('sales_order', 'delivery_order_detail.sales_order_id', '=', 'sales_order.id')
                ->select('delivery_order.id', 'delivery_order.code', 'delivery_order.status', 'print_count', 'delivery_order.created_at', 'is_marketplace', 'sales_order.store_name')
                ->whereHas('details', function($query) use ($warehouse_ids) {
                    $query->whereHas('sales_order', function($query2) use($warehouse_ids) {
                        $query2->whereIn('warehouse_id', $warehouse_ids);
                    });
                })
                ->addSelect(\DB::raw('GROUP_CONCAT(sales_order.code) as list_so'))
                ->groupBy(['delivery_order.id', 'delivery_order.code', 'delivery_order.status', 'print_count', 'delivery_order.created_at', 'is_marketplace', 'sales_order.store_name'])
                ->orderBy('status', 'ASC')->orderBy('created_at', 'DESC');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build()
    {
        $table = Table::of($this->query());
        $table->addIndexColumn();

        $table->setRowClass(function (DeliveryOrder $model) {
            return '';
        });

        $table->editColumn('created_at', function (DeliveryOrder $model) {
            return [
              'display' => Carbon::parse($model->created_at)->format('j F Y H:i:s'),
              'timestamp' => $model->created_at
            ];
        });
        
        $table->editColumn('status', function (DeliveryOrder $model) {
            return $model->status();
        });

        $table->addColumn('action', function (DeliveryOrder $model) {
            $packing_pdf = route('superuser.sale.delivery_order.packing_pdf', $model);

            $delivery_order_pdf = route('superuser.sale.delivery_order.delivery_order_pdf', $model);
            if($model->is_marketplace !== 1) {
                $delivery_order_pdf = route('superuser.sale.delivery_order.delivery_order_pdf_non_marketplace', $model);
            }

            $count_pack = count($model->details);
            $count_pack_proccess = 0;

            $html_so = '<div class="detail-validate" style="display: none;">';
            foreach($model->details as $detail) {
                $so_code = $detail->sales_order->code ?? 'deleted'; 
                if($detail->status_validate == 1) {
                    $count_pack_proccess++;
                    $html_so .= '<div class="form-group row">
                                    <label class="col-md-6 col-form-label text-left" for="grand_total">'.$so_code.'</label>
                                    <div class="col-md-6">
                                    <div class="form-control-plaintext"><i class="fa fa-check text-success" aria-hidden="true"></i></div>
                                    </div>
                                </div>';
                } else {
                    $html_so .= '<div class="form-group row">
                                    <label class="col-md-6 col-form-label text-left" for="grand_total">'.$so_code.'</label>
                                    <div class="col-md-6">
                                    <div class="form-control-plaintext"><i class="fa fa-clock-o text-danger" aria-hidden="true"></i></div>
                                    </div>
                                </div>';
                }
            }
            $html_so .= '</div>';

            switch ($model->status) {
                case $model::STATUS['ACTIVE']:
                    return "
                        <a href=\"{$packing_pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-alt-warning\" title=\"Packing Plan\">
                                Packing Plan <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                        <a href=\"{$delivery_order_pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-alt-info\" title=\"DO Print\">
                                DO Print <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                        <a href=\"#\" class=\"btn-detail-validate\">
                            <button type=\"button\" class=\"btn btn-sm btn-alt-".($count_pack_proccess == $count_pack ? 'success' : 'danger' )."\" title=\"DO Validate\" data-toggle=\"modal\" data-target=\"#modal-manage\">
                                DO Validate (".$count_pack_proccess."/".$count_pack.")
                            </button>
                        </a>
                        ".$html_so."
                    ";
                default:
                    return "
                        <a href=\"{$packing_pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-alt-warning\" title=\"Packing Plan\">
                                Packing Plan <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                        <a href=\"{$delivery_order_pdf}\" target=\"_blank\">
                            <button type=\"button\" class=\"btn btn-sm btn-alt-info\" title=\"DO Print\">
                                DO Print <i class=\"fa fa-print\"></i>
                            </button>
                        </a>
                        <a href=\"#\" class=\"btn-detail-validate\">
                            <button type=\"button\" class=\"btn btn-sm btn-alt-".($count_pack_proccess == $count_pack ? 'success' : 'danger' )."\" title=\"DO Validate\" data-toggle=\"modal\" data-target=\"#modal-manage\">
                                DO Validate (".$count_pack_proccess."/".$count_pack.")
                            </button>
                        </a>
                        ".$html_so."
                    ";
            }

        });

        return $table->make(true);
    }
}