<?php

namespace App\Http\Controllers\Superuser\Inventory;

use App\Entities\Inventory\Mutation;
use App\Entities\Inventory\MutationDetail;
use App\Entities\Inventory\MutationDisplay;
use App\Entities\Inventory\ProductConversion;
use App\Entities\Inventory\Recondition;
use App\Entities\Inventory\SettingWarehouseRecondition;
use App\Entities\Inventory\StockAdjusment;
use App\Entities\Master\Product;
use App\Entities\Master\Warehouse;
use App\Entities\Purchasing\Receiving;
use App\Entities\Purchasing\ReceivingDetailColly;
use App\Entities\QualityControl\QualityControl2;
use App\Entities\Sale\BuyBack;
use App\Entities\Sale\DeliveryOrderDetail;
use App\Entities\Sale\SaleReturn;
use App\Entities\Sale\SalesOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function json(Request $request)
    {
        $data = [];
        $setting_warehouse_recondition = SettingWarehouseRecondition::first();
        $warehouse = $request->warehouse_id;

        $collect = [];
        if ($warehouse) {

            $receivings = Receiving::where('warehouse_id', $warehouse)->where('status', Receiving::STATUS['ACC'])->get();
            foreach ($receivings as $receiving) {
                foreach ($receiving->details as $detail) {
                    if (!empty($collect[$detail->product_id]['in'])) {
                        $collect[$detail->product_id]['in'] += $detail->total_quantity_ri;
                    } else {
                        $collect[$detail->product_id]['in'] = $detail->total_quantity_ri;
                    }

                    foreach ($detail->collys as $colly) {
                        if ($colly->status_qc == ReceivingDetailColly::STATUS_QC['USED'] && $colly->quantity_recondition > 0) {
                            if (!empty($collect[$colly->receiving_detail->product_id]['out'])) {
                                $collect[$colly->receiving_detail->product_id]['out'] += $colly->quantity_recondition;
                            } else {
                                $collect[$colly->receiving_detail->product_id]['out'] = $colly->quantity_recondition;
                            }
                        }

                        if ($colly->status_mutation == ReceivingDetailColly::STATUS_MUTATION['USED'] && $colly->quantity_mutation > 0) {
                            $mutation_detail = MutationDetail::where('receiving_detail_colly_id', $colly->id)->first();
                            if ($mutation_detail && $mutation_detail->mutation->status == Mutation::STATUS['ACC']) {
                                if (!empty($collect[$colly->receiving_detail->product_id]['out'])) {
                                    $collect[$colly->receiving_detail->product_id]['out'] += $colly->quantity_mutation;
                                } else {
                                    $collect[$colly->receiving_detail->product_id]['out'] = $colly->quantity_mutation;
                                }
                            }
                        }
                    }
                }
            }

            $receiving_detail_collys = ReceivingDetailColly::where('status_qc', ReceivingDetailColly::STATUS_QC['USED'])
                ->where('quantity_recondition', '>', 0)
                ->where('warehouse_reparation_id', $warehouse)
                ->get();
            foreach ($receiving_detail_collys as $colly) {
                if (!empty($collect[$colly->receiving_detail->product_id]['in'])) {
                    $collect[$colly->receiving_detail->product_id]['in'] += $colly->quantity_recondition;
                } else {
                    $collect[$colly->receiving_detail->product_id]['in'] = $colly->quantity_recondition;
                }
            }

            $mutations = Mutation::where('warehouse_id', $warehouse)->where('status', Mutation::STATUS['ACC'])->get();
            foreach ($mutations as $mutation) {
                foreach ($mutation->mutation_details as $detail) {
                    $product_id = $detail->receiving_detail_colly->receiving_detail->product_id;
                    $qty = $detail->receiving_detail_colly->quantity_mutation;
                    if (!empty($collect[$product_id]['in'])) {
                        $collect[$product_id]['in'] += $qty;
                    } else {
                        $collect[$product_id]['in'] = $qty;
                    }
                }
            }

            $quality_controls = QualityControl2::where('warehouse_id', $warehouse)->get();
            foreach ($quality_controls as $quality_control) {
                if (!empty($collect[$quality_control->product_id]['out'])) {
                    $collect[$quality_control->product_id]['out'] += $quality_control->quantity;
                } else {
                    $collect[$quality_control->product_id]['out'] = $quality_control->quantity;
                }
            }

            $quality_controls = QualityControl2::where('warehouse_reparation_id', $warehouse)->get();
            foreach ($quality_controls as $quality_control) {
                if (!empty($collect[$quality_control->product_id]['in'])) {
                    $collect[$quality_control->product_id]['in'] += $quality_control->quantity;
                } else {
                    $collect[$quality_control->product_id]['in'] = $quality_control->quantity;
                }
            }

            // $sales_orders = SalesOrder::where('warehouse_id', $warehouse)->where('status', SalesOrder::STATUS['ACC'])->get();
            // foreach ($sales_orders as $sales_order) {
            //     $delivery_order_detail = DeliveryOrderDetail::where('sales_order_id', $sales_order->id)->first();
            //     if ($delivery_order_detail && $delivery_order_detail->status_validate == 1) {
            //         foreach ($sales_order->sales_order_details as $detail) {
            //             if (!empty($collect[$detail->product_id]['out'])) {
            //                 $collect[$detail->product_id]['out'] += $detail->quantity;
            //             } else {
            //                 $collect[$detail->product_id]['out'] = $detail->quantity;
            //             }
            //         }
            //     } else {
            //         foreach ($sales_order->sales_order_details as $detail) {
            //             if (!empty($collect[$detail->product_id]['sell'])) {
            //                 $collect[$detail->product_id]['sell'] += $detail->quantity;
            //             } else {
            //                 $collect[$detail->product_id]['sell'] = $detail->quantity;
            //             }
            //         }
            //     }
            // }

            $sales_orders = SalesOrder::select(\DB::raw('sales_order_detail.product_id, SUM(sales_order_detail.quantity) as totalquantity'))
                ->leftJoin('sales_order_detail', 'sales_order_detail.sales_order_id', '=', 'sales_order.id')
                ->where('warehouse_id', $warehouse)
                ->where('status', SalesOrder::STATUS['ACC'])
                ->whereHas('delivery_order_details', function ($query) {
                    $query->where('status_validate', '1');
                })
                ->groupBy('sales_order_detail.product_id')
                ->get();

            foreach ($sales_orders as $detail) {
                if (!empty($collect[$detail->product_id]['out'])) {
                    $collect[$detail->product_id]['out'] += $detail->totalquantity;
                } else {
                    $collect[$detail->product_id]['out'] = $detail->totalquantity;
                }
            }

            $sales_orders = SalesOrder::where('warehouse_id', $warehouse)
                ->where('status', SalesOrder::STATUS['ACC'])
                ->where(function ($query) {
                    $query->whereHas('delivery_order_details', function ($query) {
                        $query->where('status_validate', '0');
                    })->orDoesntHave('delivery_order_details');
                })
                ->select(\DB::raw('sales_order_detail.product_id, SUM(sales_order_detail.quantity) as totalquantity'))
                ->leftJoin('sales_order_detail', 'sales_order_detail.sales_order_id', '=', 'sales_order.id')
                ->groupBy('sales_order_detail.product_id')
                ->get();

            foreach ($sales_orders as $detail) {
                if (!empty($collect[$detail->product_id]['sell'])) {
                    $collect[$detail->product_id]['sell'] += $detail->totalquantity;
                } else {
                    $collect[$detail->product_id]['sell'] = $detail->totalquantity;
                }
            }




            $reconditions = Recondition::where('warehouse_id', $warehouse)->where('status', Recondition::STATUS['ACC'])->get();
            foreach ($reconditions as $recondition) {
                foreach ($recondition->recondition_valids as $detail) {
                    if (!empty($collect[$detail->product_id]['in'])) {
                        $collect[$detail->product_id]['in'] += $detail->quantity;
                    } else {
                        $collect[$detail->product_id]['in'] = $detail->quantity;
                    }
                }
            }

            $reconditions = Recondition::where('warehouse_reparation_id', $warehouse)->where('status', Recondition::STATUS['ACC'])->get();
            foreach ($reconditions as $recondition) {
                foreach ($recondition->recondition_valids as $detail) {
                    if (!empty($collect[$detail->product_id]['out'])) {
                        $collect[$detail->product_id]['out'] += $detail->quantity;
                    } else {
                        $collect[$detail->product_id]['out'] = $detail->quantity;
                    }
                }
                foreach ($recondition->recondition_disposals as $detail) {
                    if (!empty($collect[$detail->product_id]['out'])) {
                        $collect[$detail->product_id]['out'] += $detail->quantity;
                    } else {
                        $collect[$detail->product_id]['out'] = $detail->quantity;
                    }
                }
            }

            $sale_returns = SaleReturn::where('status', SaleReturn::STATUS['ACC'])
                ->where('warehouse_reparation_id', $warehouse)
                ->get();
            foreach ($sale_returns as $sale_return) {
                foreach ($sale_return->sale_return_details as $detail) {
                    if (!empty($collect[$detail->product_id]['in'])) {
                        $collect[$detail->product_id]['in'] += $detail->quantity;
                    } else {
                        $collect[$detail->product_id]['in'] = $detail->quantity;
                    }
                }
            }

            $buy_backs = BuyBack::where('status', BuyBack::STATUS['ACC'])
                ->where('disposal', '0')
                ->where('warehouse_id', $warehouse)
                ->get();

            foreach ($buy_backs as $buy_back) {
                foreach ($buy_back->details as $detail) {
                    if (!empty($collect[$detail->sales_order_detail->product_id]['in'])) {
                        $collect[$detail->sales_order_detail->product_id]['in'] += $detail->buy_back_qty;
                    } else {
                        $collect[$detail->sales_order_detail->product_id]['in'] = $detail->buy_back_qty;
                    }
                }
            }

            $stock_adjusments = StockAdjusment::where('status', StockAdjusment::STATUS['ACC'])
                ->where('warehouse_id', $warehouse)
                ->get();

            foreach ($stock_adjusments as $stock_adjusment) {
                if ($stock_adjusment->minus == '0') {
                    foreach ($stock_adjusment->details as $detail) {
                        if (!empty($collect[$detail->product_id]['in'])) {
                            $collect[$detail->product_id]['in'] += $detail->qty;
                        } else {
                            $collect[$detail->product_id]['in'] = $detail->qty;
                        }
                    }
                } else {
                    foreach ($stock_adjusment->details as $detail) {
                        if (!empty($collect[$detail->product_id]['out'])) {
                            $collect[$detail->product_id]['out'] += $detail->qty;
                        } else {
                            $collect[$detail->product_id]['out'] = $detail->qty;
                        }
                    }
                }
            }

            $mutation_display = MutationDisplay::where('status', MutationDisplay::STATUS['ACC'])
                ->where('warehouse_from', $warehouse)
                ->get();
            foreach ($mutation_display as $single) {
                foreach ($single->details as $detail) {
                    if (!empty($collect[$detail->product_id]['out'])) {
                        $collect[$detail->product_id]['out'] += $detail->qty;
                    } else {
                        $collect[$detail->product_id]['out'] = $detail->qty;
                    }
                }
            }


            $mutation_display = MutationDisplay::where('status', MutationDisplay::STATUS['ACC'])
                ->where('warehouse_to', $warehouse)
                ->get();
            foreach ($mutation_display as $single) {
                foreach ($single->details as $detail) {
                    if (!empty($collect[$detail->product_id]['in'])) {
                        $collect[$detail->product_id]['in'] += $detail->qty;
                    } else {
                        $collect[$detail->product_id]['in'] = $detail->qty;
                    }
                }
            }

            $product_conversion = ProductConversion::where('status', ProductConversion::STATUS['ACC'])
                ->where('warehouse_id', $warehouse)
                ->get();
            foreach ($product_conversion as $single) {
                foreach ($single->details as $detail) {
                    if (!empty($collect[$detail->product_from]['out'])) {
                        $collect[$detail->product_from]['out'] += $detail->qty;
                    } else {
                        $collect[$detail->product_from]['out'] = $detail->qty;
                    }

                    if (!empty($collect[$detail->product_to]['in'])) {
                        $collect[$detail->product_to]['in'] += $detail->qty;
                    } else {
                        $collect[$detail->product_to]['in'] = $detail->qty;
                    }
                }
            }



            // COLLECT
            foreach ($collect as $key => $value) {
                $product = Product::find($key);
                $in = !empty($value['in']) ? $value['in'] : 0;
                $out = !empty($value['out']) ? $value['out'] : 0;
                $sell = !empty($value['sell']) ? $value['sell'] : 0;
                $stock = $in - $out;
                $effective = $stock - $sell;
                $data['data'][] = [$product->code, '<a href="' . route('superuser.inventory.stock.detail', [$warehouse, $product->id]) . '" target="_blank">' . $product->name . '</a>', $in, $out, $stock, $sell, $effective];
            }

            if (empty($collect)) {
                $data['data'] = '';
            }
        } else {
            $data['data'] = '';
        }

        return $data;
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('stock-manage')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_branch();

        return view('superuser.inventory.stock.index', $data);
    }

    public function date_compare($element1, $element2)
    {
        $datetime1 = strtotime($element1['created_at']);
        $datetime2 = strtotime($element2['created_at']);
        return $datetime1 - $datetime2;
    }

    public function detail($warehouse, $product)
    {
        $data['product'] = Product::findOrFail($product);
        $data['warehouse'] = Warehouse::findOrFail($warehouse);
        $data['collects'] = [];

        $collect = [];

        $receiving_detail_collys = ReceivingDetailColly::where('status_qc', ReceivingDetailColly::STATUS_QC['USED'])
            ->where('quantity_recondition', '>', 0)
            ->where('warehouse_reparation_id', $warehouse)
            ->whereHas('receiving_detail', function ($query) use ($product) {
                $query->where('product_id', $product);
            })
            ->get();
        foreach ($receiving_detail_collys as $colly) {
            if ($colly->receiving_detail->product_id == $product) {
                $collect[] = [
                    'created_at' => $colly->created_at,
                    'transaction' => 'QC_UTAMA',
                    'in' => $colly->quantity_recondition,
                    'out' => '',
                    'balance' => '',
                    'description' => $colly->description,
                ];
            }
        }

        $quality_controls = QualityControl2::where('warehouse_reparation_id', $warehouse)->where('product_id', $product)->get();
        foreach ($quality_controls as $quality_control) {
            if ($quality_control->product_id == $product) {
                $collect[] = [
                    'created_at' => $quality_control->created_at,
                    'transaction' => 'QC_DISPLAY',
                    'in' => $quality_control->quantity,
                    'out' => '',
                    'balance' => '',
                    'description' => $quality_control->description,
                ];
            }
        }

        $sale_returns = SaleReturn::where('status', SaleReturn::STATUS['ACC'])
            ->where('warehouse_reparation_id', $warehouse)
            ->whereHas('sale_return_details', function ($query) use ($product) {
                $query->where('product_id', $product);
            })
            ->get();
        foreach ($sale_returns as $sale_return) {
            foreach ($sale_return->sale_return_details as $detail) {
                if ($detail->product_id == $product) {
                    $collect[] = [
                        'created_at' => $sale_return->created_at,
                        'transaction' => $sale_return->code,
                        'in' => $detail->quantity,
                        'out' => '',
                        'balance' => '',
                        'description' => $detail->description,
                    ];
                }
            }
        }

        $reconditions = Recondition::where('status', Recondition::STATUS['ACC'])
            ->where('warehouse_reparation_id', $warehouse)
            ->whereHas('recondition_valids', function ($query) use ($product) {
                $query->where('product_id', $product);
            })
            ->get();
        foreach ($reconditions as $recondition) {
            foreach ($recondition->recondition_valids as $detail) {
                if ($detail->product_id == $product and $detail->quantity > 0) {
                    $collect[] = [
                        'created_at' => $recondition->created_at,
                        'transaction' => $recondition->code . ' / RECONDITION',
                        'in' => '',
                        'out' => $detail->quantity,
                        'balance' => '',
                        'description' => '',
                    ];
                }
            }
        }

        $reconditions = Recondition::where('status', Recondition::STATUS['ACC'])
            ->where('warehouse_reparation_id', $warehouse)
            ->whereHas('recondition_disposals', function ($query) use ($product) {
                $query->where('product_id', $product);
            })
            ->get();
        foreach ($reconditions as $recondition) {
            foreach ($recondition->recondition_disposals as $detail) {
                if ($detail->product_id == $product) {
                    if ($detail->quantity > 0) {
                        $collect[] = [
                            'created_at' => $recondition->created_at,
                            'transaction' => $recondition->code . ' / DISPOSAL',
                            'in' => '',
                            'out' => $detail->quantity,
                            'balance' => '',
                            'description' => '',
                        ];
                    }
                }
            }
        }

        // NON REPARATION

        $receivings = Receiving::where('warehouse_id', $warehouse)->where('status', Receiving::STATUS['ACC'])
            ->whereHas('details', function ($query) use ($product) {
                $query->where('product_id', $product);
            })
            ->get();
        foreach ($receivings as $receiving) {
            foreach ($receiving->details as $detail) {
                if ($detail->product_id == $product) {
                    $collect[] = [
                        'created_at' => $receiving->created_at,
                        'transaction' => $receiving->code,
                        'in' => $detail->total_quantity_ri,
                        'out' => '',
                        'balance' => '',
                        'description' => '',
                    ];
                }

                foreach ($detail->collys as $colly) {
                    if ($colly->status_qc == ReceivingDetailColly::STATUS_QC['USED'] && $colly->quantity_recondition > 0) {
                        if ($colly->receiving_detail->product_id == $product) {
                            $collect[] = [
                                'created_at' => $colly->created_at,
                                'transaction' => 'QC_UTAMA',
                                'in' => '',
                                'out' => $colly->quantity_recondition,
                                'balance' => '',
                                'description' => $colly->description,
                            ];
                        }
                    }

                    if ($colly->status_mutation == ReceivingDetailColly::STATUS_MUTATION['USED'] && $colly->quantity_mutation > 0) {
                        $mutation_detail = MutationDetail::where('receiving_detail_colly_id', $colly->id)->first();
                        if ($mutation_detail && $mutation_detail->mutation->status == Mutation::STATUS['ACC']) {
                            if ($colly->receiving_detail->product_id == $product) {
                                $collect[] = [
                                    'created_at' => $mutation_detail->mutation->created_at,
                                    'transaction' => $mutation_detail->mutation->code,
                                    'in' => '',
                                    'out' => $colly->quantity_mutation,
                                    'balance' => '',
                                    'description' => '',
                                ];
                            }
                        }
                    }
                }
            }
        }

        $mutations = Mutation::where('warehouse_id', $warehouse)
            ->where('status', Mutation::STATUS['ACC'])
            ->whereHas('mutation_details', function ($query) use ($product) {
                $query->whereHas('receiving_detail_colly', function ($query) use ($product) {
                    $query->whereHas('receiving_detail', function ($query) use ($product) {
                        $query->where('product_id', $product);
                    });
                });
            })
            ->get();
        foreach ($mutations as $mutation) {
            foreach ($mutation->mutation_details as $detail) {
                $product_id = $detail->receiving_detail_colly->receiving_detail->product_id;
                $qty = $detail->receiving_detail_colly->quantity_mutation;

                if ($product_id == $product) {
                    $collect[] = [
                        'created_at' => $mutation->created_at,
                        'transaction' => $mutation->code,
                        'in' => $qty,
                        'out' => '',
                        'balance' => '',
                        'description' => '',
                    ];
                }
            }
        }

        $quality_controls = QualityControl2::where('warehouse_id', $warehouse)->get();
        foreach ($quality_controls as $quality_control) {
            if ($quality_control->product_id == $product) {
                $collect[] = [
                    'created_at' => $quality_control->created_at,
                    'transaction' => 'QC_DISPLAY',
                    'in' => '',
                    'out' => $quality_control->quantity,
                    'balance' => '',
                    'description' => $quality_control->description,
                ];
            }
        }

        $sales_orders = SalesOrder::where('warehouse_id', $warehouse)
            ->where('status', SalesOrder::STATUS['ACC'])
            ->where('status_sales_order', '1')
            ->whereHas('sales_order_details', function ($query) use ($product) {
                $query->where('product_id', $product);
            })
            ->get();
        foreach ($sales_orders as $sales_order) {
            $delivery_order_detail = DeliveryOrderDetail::where('sales_order_id', $sales_order->id)->first();
            if ($delivery_order_detail && $delivery_order_detail->status_validate == 1) {
                foreach ($sales_order->sales_order_details as $detail) {
                    if ($detail->product_id == $product) {
                        $collect[] = [
                            'created_at' => $sales_order->updated_at,
                            'transaction' => $sales_order->code,
                            'in' => '',
                            'out' => $detail->quantity,
                            'balance' => '',
                            'description' => '',
                        ];
                    }
                }
            }
        }

        $reconditions = Recondition::where('warehouse_id', $warehouse)->where('status', Recondition::STATUS['ACC'])->get();
        foreach ($reconditions as $recondition) {
            foreach ($recondition->recondition_valids as $detail) {
                if ($detail->product_id == $product and $detail->quantity > 0) {
                    $collect[] = [
                        'created_at' => $recondition->created_at,
                        'transaction' => $recondition->code,
                        'in' => $detail->quantity,
                        'out' => '',
                        'balance' => '',
                        'description' => '',
                    ];
                }
            }
        }

        $buy_backs = BuyBack::where('status', BuyBack::STATUS['ACC'])->where('disposal', '0')->where('warehouse_id', $warehouse)->get();

        foreach ($buy_backs as $buy_back) {
            foreach ($buy_back->details as $detail) {
                if ($detail->sales_order_detail->product_id == $product) {
                    $collect[] = [
                        'created_at' => $buy_back->acc_at,
                        'transaction' => $buy_back->code,
                        'in' => $detail->buy_back_qty,
                        'out' => '',
                        'balance' => '',
                        'description' => '',
                    ];
                }
            }
        }

        $stock_adjusments = StockAdjusment::where('status', StockAdjusment::STATUS['ACC'])
            ->where('warehouse_id', $warehouse)
            ->get();

        foreach ($stock_adjusments as $stock_adjusment) {
            foreach ($stock_adjusment->details as $detail) {
                if ($detail->product_id == $product) {
                    $collect[] = [
                        'created_at' => $stock_adjusment->acc_at,
                        'transaction' => $stock_adjusment->code,
                        'in' => $stock_adjusment->minus == '0' ? $detail->qty : '',
                        'out' => $stock_adjusment->minus == '1' ? $detail->qty : '',
                        'balance' => '',
                        'description' => $detail->description ?? '',
                    ];
                }
            }
        }

        $mutation_display = MutationDisplay::where('status', MutationDisplay::STATUS['ACC'])
            ->where('warehouse_from', $warehouse)
            ->get();

        foreach ($mutation_display as $single) {
            foreach ($single->details as $detail) {
                if ($detail->product_id == $product) {
                    $collect[] = [
                        'created_at' => $single->acc_at,
                        'transaction' => $single->code,
                        'in' => '',
                        'out' => $detail->qty,
                        'balance' => '',
                        'description' => $detail->description ?? '',
                    ];
                }
            }
        }

        $mutation_display = MutationDisplay::where('status', MutationDisplay::STATUS['ACC'])
            ->where('warehouse_to', $warehouse)
            ->get();

        foreach ($mutation_display as $single) {
            foreach ($single->details as $detail) {
                if ($detail->product_id == $product) {
                    $collect[] = [
                        'created_at' => $single->acc_at,
                        'transaction' => $single->code,
                        'in' => $detail->qty,
                        'out' => '',
                        'balance' => '',
                        'description' => $detail->description ?? '',
                    ];
                }
            }
        }

        $product_conversion = ProductConversion::where('status', ProductConversion::STATUS['ACC'])
            ->where('warehouse_id', $warehouse)
            ->get();

        foreach ($product_conversion as $single) {
            foreach ($single->details as $detail) {
                if ($detail->product_from == $product) {
                    $collect[] = [
                        'created_at' => $single->acc_at,
                        'transaction' => $single->code,
                        'in' => '',
                        'out' => $detail->qty,
                        'balance' => '',
                        'description' => $detail->description ?? '',
                    ];
                }
                if ($detail->product_to == $product) {
                    $collect[] = [
                        'created_at' => $single->acc_at,
                        'transaction' => $single->code,
                        'in' => $detail->qty,
                        'out' => '',
                        'balance' => '',
                        'description' => $detail->description ?? '',
                    ];
                }
            }
        }

        if ($collect) {
            $balance = 0;
            $newCollect = [];

            $sortedArr = collect($collect)->sortBy('created_at')->all();
            foreach ($sortedArr as $key => $value) {
                if ($value['in']) {
                    $balance = $balance + $value['in'];
                } else if ($value['out']) {
                    $balance = $balance - $value['out'];
                }
                $newCollect[] = [
                    'created_at' => $value['created_at'],
                    'transaction' => $value['transaction'],
                    'in' => $value['in'],
                    'out' => $value['out'],
                    'balance' => $balance,
                    'description' => $value['description'],
                ];
            }

            $sortedArr = collect($newCollect)->sortKeysDesc()->all();
            $data['collects'] = $sortedArr;
        }

        return view('superuser.inventory.stock.detail', $data);
    }
}
