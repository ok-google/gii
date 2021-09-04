<?php

namespace App\Http\Controllers\Superuser\Inventory;

use App\DataTables\Inventory\ReconditionTable;
use App\Entities\Accounting\Hpp;
use App\Entities\Accounting\Journal;
use App\Entities\Finance\SettingFinance;
use App\Entities\Master\Product;
use App\Entities\Sale\SaleReturn;
use App\Entities\Sale\SaleReturnDetail;
use App\Entities\Sale\StockSalesOrder;
use App\Entities\Inventory\Recondition;
use App\Entities\Inventory\ReconditionDetail;
use App\Entities\Inventory\ReconditionValid;
use App\Entities\Inventory\ReconditionDisposal;
use App\Entities\Inventory\ReconditionResidual;
use App\Entities\Inventory\SettingWarehouseRecondition;
use App\Entities\Purchasing\ReceivingDetailColly;
use App\Entities\QualityControl\QualityControl2;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use Carbon\Carbon;

class ReconditionController extends Controller
{

    public function json(Request $request, ReconditionTable $datatable)
    {
        return $datatable->build();
    }

    public function search_sku(Request $request)
    {

        $product = Product::findOrFail($request->product);
        $title = $product->code . ' / ' . $product->name;
        $product_id = $product->id;

        $sale_returns = SaleReturnDetail::where('status_recondition', 0)
            ->where('product_id', $request->product)
            ->join('master_products', function ($join) {
                $join->on('sale_return_detail.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->join('sale_return', function ($join) {
                $join->on('sale_return_detail.sale_return_id', '=', 'sale_return.id')
                    ->where('sale_return.status', SaleReturn::STATUS['ACC'])
                    ->whereIn('sale_return.warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
            })
            ->selectRaw('CONCAT("SALE_RETURN") as type, CONCAT("SALE_RETURN") as type_text, sale_return_detail.id as parent_id, sale_return_detail.product_id as product_id, master_products.code as sku, master_products.name as name, sale_return_detail.quantity as quantity, sale_return_detail.description as keterangan, sale_return.updated_at as date_in')
            ->get();

        $receiving_detail_colly = ReceivingDetailColly::where('status_qc', 1)
            ->where('status_recondition', 0)
            ->where('quantity_recondition', '>', 0)
            ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->join('receiving_detail', function ($join) use ($request) {
                $join->on('receiving_detail_colly.receiving_detail_id', '=', 'receiving_detail.id')
                    ->where('receiving_detail.product_id', $request->product);
            })
            ->join('master_products', function ($join) {
                $join->on('receiving_detail.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->selectRaw('CONCAT("QC_UTAMA") as type, CONCAT("QC_UTAMA") as type_text, receiving_detail_colly.id as parent_id, receiving_detail.product_id as product_id, master_products.code as sku, master_products.name as name, receiving_detail_colly.quantity_recondition as quantity, receiving_detail_colly.description as keterangan, receiving_detail_colly.date_recondition as date_in')
            ->get();

        $quality_control_2 = QualityControl2::where('status_recondition', 0)
            ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->where('product_id', $request->product)
            ->join('master_products', function ($join) {
                $join->on('quality_control2.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->selectRaw('CONCAT("QC_DISPLAY") as type, CONCAT("QC_DISPLAY") as type_text, quality_control2.id as parent_id, quality_control2.product_id as product_id, master_products.code as sku, master_products.name as name, quality_control2.quantity as quantity, quality_control2.description as keterangan, quality_control2.created_at as date_in')
            ->get();

        $recondition_residual = ReconditionResidual::where('status_recondition', 0)
            ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->where('product_id', $request->product)
            ->join('master_products', function ($join) {
                $join->on('recondition_residual.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->selectRaw('CONCAT("RESIDUAL") as type, recondition_residual.type_text as type_text, recondition_residual.id as parent_id, recondition_residual.product_id as product_id, master_products.code as sku, master_products.name as name, recondition_residual.quantity as quantity, recondition_residual.description as keterangan, recondition_residual.created_at as date_in')
            ->get();

        $collection = $sale_returns->concat($receiving_detail_colly)->concat($quality_control_2)->concat($recondition_residual);

        return ['title' => $title, 'id' => $product_id, 'list' => $collection];
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('recondition-manage')) {
            return abort(403);
        }

        return view('superuser.inventory.recondition.index');
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('recondition-create')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        $sale_returns = SaleReturnDetail::where('status_recondition', 0)
            ->join('master_products', function ($join) {
                $join->on('sale_return_detail.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->join('sale_return', function ($join) {
                $join->on('sale_return_detail.sale_return_id', '=', 'sale_return.id')
                    ->where('sale_return.status', SaleReturn::STATUS['ACC'])
                    ->whereIn('sale_return.warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
            })
            ->selectRaw('sale_return_detail.product_id as id, master_products.code as code')
            ->groupBy(['id', 'code'])
            ->get();

        $receiving_detail_colly = ReceivingDetailColly::where('status_qc', 1)
            ->where('status_recondition', 0)
            ->where('quantity_recondition', '>', 0)
            ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->join('receiving_detail', function ($join) {
                $join->on('receiving_detail_colly.receiving_detail_id', '=', 'receiving_detail.id');
            })
            ->join('master_products', function ($join) {
                $join->on('receiving_detail.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->selectRaw('receiving_detail.product_id as id, master_products.code as code')
            ->groupBy(['id', 'code'])
            ->get();

        $quality_control_2 = QualityControl2::where('status_recondition', 0)
            ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->join('master_products', function ($join) {
                $join->on('quality_control2.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->selectRaw('quality_control2.product_id as id, master_products.code as code')
            ->groupBy(['id', 'code'])
            ->get();

        $recondition_residual = ReconditionResidual::where('status_recondition', 0)
            ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->join('master_products', function ($join) {
                $join->on('recondition_residual.product_id', '=', 'master_products.id')
                    ->where('master_products.non_stock', '0');
            })
            ->selectRaw('recondition_residual.product_id as id, master_products.code as code')
            ->groupBy(['id', 'code'])
            ->get();

        $list_sku = $sale_returns->merge($receiving_detail_colly)->merge($quality_control_2)->merge($recondition_residual);
        $data['list_sku'] = $list_sku->sortBy('code');

        // FIND WAREHOUSE REPARATION ID
        if ($sale_returns) {
            $find_warehouse_reparation = SaleReturnDetail::where('status_recondition', 0)
                ->join('master_products', function ($join) {
                    $join->on('sale_return_detail.product_id', '=', 'master_products.id')
                        ->where('master_products.non_stock', '0');
                })
                ->join('sale_return', function ($join) {
                    $join->on('sale_return_detail.sale_return_id', '=', 'sale_return.id')
                        ->where('sale_return.status', SaleReturn::STATUS['ACC'])
                        ->whereIn('sale_return.warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray());
                })
                ->selectRaw('sale_return.warehouse_reparation_id')
                ->first();
        } elseif ($receiving_detail_colly) {
            $find_warehouse_reparation = ReceivingDetailColly::where('status_qc', 1)
                ->where('status_recondition', 0)
                ->where('quantity_recondition', '>', 0)
                ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
                ->join('receiving_detail', function ($join) {
                    $join->on('receiving_detail_colly.receiving_detail_id', '=', 'receiving_detail.id');
                })
                ->join('master_products', function ($join) {
                    $join->on('receiving_detail.product_id', '=', 'master_products.id')
                        ->where('master_products.non_stock', '0');
                })
                ->selectRaw('receiving_detail_colly.warehouse_reparation_id')
                ->first();
        } elseif ($quality_control_2) {
            $find_warehouse_reparation = QualityControl2::where('status_recondition', 0)
                ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
                ->join('master_products', function ($join) {
                    $join->on('quality_control2.product_id', '=', 'master_products.id')
                        ->where('master_products.non_stock', '0');
                })
                ->selectRaw('quality_control2.warehouse_reparation_id')
                ->first();
        } elseif ($recondition_residual) {
            $find_warehouse_reparation = ReconditionResidual::where('status_recondition', 0)
                ->whereIn('warehouse_reparation_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
                ->join('master_products', function ($join) {
                    $join->on('recondition_residual.product_id', '=', 'master_products.id')
                        ->where('master_products.non_stock', '0');
                })
                ->selectRaw('recondition_residual.warehouse_reparation_id')
                ->first();
        }
        $data['warehouse_reparation_id'] = $find_warehouse_reparation->warehouse_reparation_id ?? '';

        return view('superuser.inventory.recondition.create', $data);
    }

    public function store_setting(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'warehouse' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                $setting_warehouse_recondition = new SettingWarehouseRecondition;
                $setting_warehouse_recondition->warehouse_id = $request->warehouse;

                if ($setting_warehouse_recondition->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.inventory.recondition.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:recondition,code',
                'warehouse' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();
                try {
                    $failed = '';
                    $recondition = new Recondition;

                    $recondition->code = $request->code;
                    $recondition->warehouse_id = $request->warehouse;
                    $recondition->warehouse_reparation_id = $request->warehouse_reparation_id;
                    $recondition->status = Recondition::STATUS['ACTIVE'];

                    if ($recondition->save()) {

                        foreach ($request->type as $key => $value) {
                            // CEK INPUT QUANTITY VALIDATION
                            $quantity               = $request->quantity[$key];
                            $quantity_recondition   = $request->quantity_recondition[$key];
                            $quantity_disposal      = $request->quantity_disposal[$key];

                            $used = $quantity_recondition + $quantity_disposal;

                            if ($used > $quantity) {
                                $failed = 'Product exceeds the quantity!';
                                break;
                            }

                            if ($quantity_recondition == '0' && $quantity_disposal == '0') {
                                continue;
                            }

                            if ($request->type[$key] == 'QC_UTAMA') {
                                $model = ReceivingDetailColly::findOrFail($request->parent_id[$key]);
                                $model_relation_id = 'receiving_detail_colly_id';
                            } else if ($request->type[$key] == 'QC_DISPLAY') {
                                $model = QualityControl2::find($request->parent_id[$key]);
                                $model_relation_id = 'quality_control2_id';
                            } else if ($request->type[$key] == 'SALE_RETURN') {
                                $model = SaleReturnDetail::find($request->parent_id[$key]);
                                $model_relation_id = 'sale_return_detail_id';
                            } else if ($request->type[$key] == 'RESIDUAL') {
                                $model = ReconditionResidual::find($request->parent_id[$key]);
                                $model_relation_id = 'recondition_residual_id';
                            }

                            if ($model->status_recondition == 1) {
                                $failed = 'Product has been used, please refresh this page!';
                                break;
                            }

                            // ADD RECONDITION DETAIL
                            $recondition_detail = new ReconditionDetail;
                            $recondition_detail->recondition_id = $recondition->id;
                            $recondition_detail->$model_relation_id = $model->id;
                            $recondition_detail->product_id = $request->product_id[$key];
                            $recondition_detail->quantity_recondition = $quantity_recondition;
                            $recondition_detail->quantity_disposal = $quantity_disposal;
                            $recondition_detail->save();

                            // UPDATE USED RECONDITION
                            $model->status_recondition = 1;
                            $model->save();
                        }

                        if ($failed) {
                            DB::rollback();

                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $failed,
                            ];

                            return $this->response(400, $response);
                        } else {
                            DB::commit();

                            $response['notification'] = [
                                'alert' => 'notify',
                                'type' => 'success',
                                'content' => 'Success',
                            ];

                            $response['redirect_to'] = route('superuser.inventory.recondition.index');

                            return $this->response(200, $response);
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => "Internal Server Error!",
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if (!Auth::guard('superuser')->user()->can('recondition-show')) {
            return abort(403);
        }

        $data['recondition'] = Recondition::findOrFail($id);

        $collection = $data['recondition']->recondition_details->groupBy('product_id');
        $grouped = [];
        foreach ($collection as $key => $list) {
            $product = Product::findOrFail($key);
            $grouped[$key]['title'] = $product->code . ' / ' . $product->name;
            foreach ($list as $k => $value) {

                if ($value->receiving_detail_colly_id) {
                    $parent_id      = $value->receiving_detail_colly_id;
                    $type           = 'QC_UTAMA';
                    $type_text      = 'QC_UTAMA';
                    $description    = $value->receiving_detail_colly->description;
                    $created_at     = $value->receiving_detail_colly->date_recondition;
                    $quantity       = $value->receiving_detail_colly->quantity_recondition;
                } else if ($value->quality_control2_id) {
                    $parent_id      = $value->quality_control2_id;
                    $type           = 'QC_DISPLAY';
                    $type_text      = 'QC_DISPLAY';
                    $description    = $value->quality_control2_detail->description;
                    $created_at     = $value->quality_control2_detail->created_at;
                    $quantity       = $value->quality_control2_detail->quantity;
                } else if ($value->sale_return_detail_id) {
                    $parent_id      = $value->sale_return_detail_id;
                    $type           = 'SALE_RETURN';
                    $type_text      = 'SALE_RETURN';
                    $description    = $value->sale_return_detail->description;
                    $created_at     = $value->sale_return_detail->sale_return->updated_at;
                    $quantity       = $value->sale_return_detail->quantity;
                } else if ($value->recondition_residual_id) {
                    $parent_id      = $value->recondition_residual_id;
                    $type           = 'RESIDUAL';
                    $type_text      = $value->residual_detail->type_text;
                    $description    = $value->residual_detail->description;
                    $created_at     = $value->residual_detail->created_at;
                    $quantity       = $value->residual_detail->quantity;
                }

                $grouped[$key]['list'][] = [
                    'id'        => $value->id,
                    'type'      => $type,
                    'type_text' => $type_text,
                    'date_in'   => $created_at,
                    'parent_id' => $parent_id,
                    'quantity'  => $quantity,
                    'quantity_recondition'  => $value->quantity_recondition,
                    'quantity_disposal'     => $value->quantity_disposal,
                    'keterangan' => $description,
                ];
            }
        }

        $data['collect'] = $grouped;

        return view('superuser.inventory.recondition.show', $data);
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('recondition-edit')) {
            return abort(403);
        }

        $data['recondition'] = Recondition::findOrFail($id);
        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        $collection = $data['recondition']->recondition_details->groupBy('product_id');
        $grouped = [];
        foreach ($collection as $key => $list) {
            $product = Product::findOrFail($key);
            $grouped[$key]['title'] = $product->code . ' / ' . $product->name;
            foreach ($list as $k => $value) {

                if ($value->receiving_detail_colly_id) {
                    $parent_id      = $value->receiving_detail_colly_id;
                    $type           = 'QC_UTAMA';
                    $type_text      = 'QC_UTAMA';
                    $description    = $value->receiving_detail_colly->description;
                    $created_at     = $value->receiving_detail_colly->date_recondition;
                    $quantity       = $value->receiving_detail_colly->quantity_recondition;
                } else if ($value->quality_control2_id) {
                    $parent_id      = $value->quality_control2_id;
                    $type           = 'QC_DISPLAY';
                    $type_text      = 'QC_DISPLAY';
                    $description    = $value->quality_control2_detail->description;
                    $created_at     = $value->quality_control2_detail->created_at;
                    $quantity       = $value->quality_control2_detail->quantity;
                } else if ($value->sale_return_detail_id) {
                    $parent_id      = $value->sale_return_detail_id;
                    $type           = 'SALE_RETURN';
                    $type_text      = 'SALE_RETURN';
                    $description    = $value->sale_return_detail->description;
                    $created_at     = $value->sale_return_detail->sale_return->updated_at;
                    $quantity       = $value->sale_return_detail->quantity;
                } else if ($value->recondition_residual_id) {
                    $parent_id      = $value->recondition_residual_id;
                    $type           = 'RESIDUAL';
                    $type_text      = $value->residual_detail->type_text;
                    $description    = $value->residual_detail->description;
                    $created_at     = $value->residual_detail->created_at;
                    $quantity       = $value->residual_detail->quantity;
                }

                $grouped[$key]['list'][] = [
                    'id'        => $value->id,
                    'type'      => $type,
                    'type_text' => $type_text,
                    'date_in'   => $created_at,
                    'parent_id' => $parent_id,
                    'quantity'  => $quantity,
                    'quantity_recondition'  => $value->quantity_recondition,
                    'quantity_disposal'     => $value->quantity_disposal,
                    'keterangan' => $description,
                ];
            }
        }

        $data['collect'] = $grouped;

        return view('superuser.inventory.recondition.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $recondition = Recondition::find($id);

            if ($recondition == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:recondition,code,' . $recondition->id,
                'warehouse' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();
                try {
                    $recondition->code = $request->code;
                    $recondition->warehouse_id = $request->warehouse;

                    if ($recondition->save()) {
                        // DELETE IF NEEDED
                        if ($request->ids_delete) {
                            $pieces = explode(",", $request->ids_delete);
                            foreach ($pieces as $piece) {
                                if ($piece) {
                                    $recondition_detail = ReconditionDetail::findOrFail($piece);

                                    if ($recondition_detail->receiving_detail_colly_id) {
                                        $model = ReceivingDetailColly::findOrFail($recondition_detail->receiving_detail_colly_id);
                                    } else if ($recondition_detail->quality_control2_id) {
                                        $model = QualityControl2::findOrFail($recondition_detail->quality_control2_id);
                                    } else if ($recondition_detail->sale_return_detail_id) {
                                        $model = SaleReturnDetail::findOrFail($recondition_detail->sale_return_detail_id);
                                    } else if ($recondition_detail->recondition_residual_id) {
                                        $model = ReconditionResidual::findOrFail($recondition_detail->recondition_residual_id);
                                    }

                                    $model->status_recondition = 0;
                                    $model->save();

                                    ReconditionDetail::where('id', $piece)->delete();
                                }
                            }
                        }

                        $failed = '';
                        if ($request->id) {
                            foreach ($request->id as $key => $value) {
                                if ($request->id[$key]) {
                                    // DELETE IF QUANTITY 0
                                    if ($request->quantity_recondition[$key] == 0 && $request->quantity_disposal[$key] == 0) {
                                        $recondition_detail = ReconditionDetail::findOrFail($request->id[$key]);

                                        if ($recondition_detail->receiving_detail_colly_id) {
                                            $model = ReceivingDetailColly::findOrFail($recondition_detail->receiving_detail_colly_id);
                                        } else if ($recondition_detail->quality_control2_id) {
                                            $model = QualityControl2::findOrFail($recondition_detail->quality_control2_id);
                                        } else if ($recondition_detail->sale_return_detail_id) {
                                            $model = SaleReturnDetail::findOrFail($recondition_detail->sale_return_detail_id);
                                        } else if ($recondition_detail->recondition_residual_id) {
                                            $model = ReconditionResidual::findOrFail($recondition_detail->recondition_residual_id);
                                        }

                                        $model->status_recondition = 0;
                                        $model->save();

                                        ReconditionDetail::where('id', $request->id[$key])->delete();
                                    } else { //UPDATE QUANTITY
                                        $quantity               = $request->quantity[$key];
                                        $quantity_recondition   = $request->quantity_recondition[$key];
                                        $quantity_disposal      = $request->quantity_disposal[$key];

                                        $used = $quantity_recondition + $quantity_disposal;

                                        if ($used > $quantity) {
                                            $failed = 'Product exceeds the quantity!';
                                            break;
                                        }

                                        $recondition_detail = ReconditionDetail::findOrFail($request->id[$key]);
                                        $recondition_detail->quantity_recondition   = $quantity_recondition;
                                        $recondition_detail->quantity_disposal      = $quantity_disposal;
                                        $recondition_detail->save();
                                    }
                                }
                            }
                        }

                        if ($failed) {
                            DB::rollback();

                            $response['notification'] = [
                                'alert' => 'block',
                                'type' => 'alert-danger',
                                'header' => 'Error',
                                'content' => $failed,
                            ];

                            return $this->response(400, $response);
                        } else {
                            DB::commit();

                            $response['notification'] = [
                                'alert' => 'notify',
                                'type' => 'success',
                                'content' => 'Success',
                            ];

                            $response['redirect_to'] = route('superuser.inventory.recondition.index');

                            return $this->response(200, $response);
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $e->getMessage(),
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('recondition-acc')) {
                return abort(403);
            }

            $recondition = Recondition::find($id);

            if ($recondition === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $failed = '';
                $superuser = Auth::guard('superuser')->user();

                $disposal_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'disposal_debet')->first();
                $disposal_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'disposal_credit')->first();
                if ($disposal_debet == null or $disposal_debet->coa_id == null or $disposal_credit == null or $disposal_credit->coa_id == null) {
                    $failed = 'Finance Setting is not set, please contact your Administrator!';
                } else {

                    $warehouse = $recondition->warehouse_id;
                    $hpp_grand_total = 0;
                    foreach ($recondition->recondition_details as $detail) {
                        // ADD STOCK TO SALESORDER
                        $product_id = $detail->product_id;
                        $stock = $detail->quantity_recondition;
                        if ($stock > 0) {
                            $stock_sales_order = StockSalesOrder::where('warehouse_id', $warehouse)->where('product_id', $product_id)->first();
                            if ($stock_sales_order) {
                                $getstock = $stock_sales_order->stock;

                                $stock_sales_order->stock = $getstock + $stock;
                                $stock_sales_order->save();
                            } else {
                                $stock_sales_order = new StockSalesOrder;

                                $stock_sales_order->warehouse_id = $warehouse;
                                $stock_sales_order->product_id = $product_id;
                                $stock_sales_order->stock = $stock;
                                $stock_sales_order->save();
                            }
                        }

                        // ADD RESIDUAL IF NEEDED
                        if ($detail->receiving_detail_colly_id) {
                            $sisa           = $detail->receiving_detail_colly->quantity_recondition - $detail->quantity_recondition - $detail->quantity_disposal;
                            $type_text      = 'QC_UTAMA';
                            $description    = $detail->receiving_detail_colly->description;
                            $created_at     = $detail->receiving_detail_colly->date_recondition;
                        } else if ($detail->quality_control2_id) {
                            $sisa           = $detail->quality_control2_detail->quantity - $detail->quantity_recondition - $detail->quantity_disposal;
                            $type_text      = 'QC_DISPLAY';
                            $description    = $detail->quality_control2_detail->description;
                            $created_at     = $detail->quality_control2_detail->created_at;
                        } else if ($detail->sale_return_detail_id) {
                            $sisa           = $detail->sale_return_detail->quantity - $detail->quantity_recondition - $detail->quantity_disposal;
                            $type_text      = 'SALE_RETURN';
                            $description    = $detail->sale_return_detail->description;
                            $created_at     = $detail->sale_return_detail->sale_return->updated_at;
                        } else if ($detail->recondition_residual_id) {
                            $sisa           = $detail->residual_detail->quantity - $detail->quantity_recondition - $detail->quantity_disposal;
                            $type_text      = $detail->residual_detail->type_text;
                            $description    = $detail->residual_detail->description;
                            $created_at     = $detail->residual_detail->created_at;
                        }

                        if ($sisa > 0) {
                            $recondition_residual = new ReconditionResidual;
                            $recondition_residual->warehouse_reparation_id = $recondition->warehouse_reparation_id;
                            $recondition_residual->product_id = $detail->product_id;
                            $recondition_residual->quantity = $sisa;

                            $recondition_residual->type_text = $type_text;
                            $recondition_residual->description = $description;
                            $recondition_residual->created_at = $created_at;

                            $recondition_residual->save();
                        }

                        // ADD DISPOSAL ACCOUNT
                        $hpp_total = 0;
                        for ($i = 0; $i < $detail->quantity_disposal; $i++) {
                            $hpp = Hpp::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('product_id', $detail->product_id)->orderBy('created_at', 'ASC')->first();

                            if ($hpp) {
                                $hpp_total = $hpp_total + $hpp->price;

                                $min = $hpp->quantity - 1;
                                if ($min > 0) {
                                    $hpp->quantity = $min;
                                    $hpp->save();
                                } else {
                                    $hpp->delete();
                                }
                            }
                        }
                        $hpp_grand_total = $hpp_grand_total + $hpp_total;


                        // ADD DETAIL VALID
                        if ($detail->quantity_recondition > 0) {
                            $recondition_valid = new ReconditionValid;
                            $recondition_valid->recondition_id = $recondition->id;
                            $recondition_valid->product_id = $detail->product_id;
                            $recondition_valid->quantity = $detail->quantity_recondition;
                            $recondition_valid->save();
                        }

                        // ADD DETAIL DISPOSAL
                        if ($detail->quantity_disposal > 0) {
                            $recondition_disposal = new ReconditionDisposal;
                            $recondition_disposal->recondition_id = $recondition->id;
                            $recondition_disposal->product_id = $detail->product_id;
                            $recondition_disposal->quantity = $detail->quantity_disposal;
                            $recondition_disposal->save();
                        }
                    }

                    // ADD JOURNAL
                    if ($hpp_grand_total > 0) {
                        // Debet
                        $journal = new Journal;
                        $journal->coa_id = $disposal_debet->coa_id;
                        $journal->name = Journal::PREJOURNAL['DISPOSAL_ACC'] . $recondition->code;
                        $journal->debet = $hpp_grand_total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();

                        // Credit
                        $journal = new Journal;
                        $journal->coa_id = $disposal_credit->coa_id;
                        $journal->name = Journal::PREJOURNAL['DISPOSAL_ACC'] . $recondition->code;
                        $journal->credit = $hpp_grand_total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->save();
                    }
                }

                DB::commit();

                if ($failed) {
                    $response['failed'] = $failed;
                    return $this->response(200, $response);
                }

                $recondition->status = Recondition::STATUS['ACC'];
                if ($recondition->save()) {
                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();

                $response['failed'] = $e->getMessage();
                return $this->response(200, $response);

                // return $this->response(400, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('recondition-delete')) {
                return abort(403);
            }

            $recondition = Recondition::find($id);

            if ($recondition === null) {
                abort(404);
            }

            DB::beginTransaction();

            try {
                foreach ($recondition->recondition_details as $detail) {
                    if ($detail->receiving_detail_colly_id) {
                        $receiving_detail_colly = ReceivingDetailColly::find($detail->receiving_detail_colly_id);
                        if ($receiving_detail_colly) {
                            $receiving_detail_colly->status_recondition = 0;
                            $receiving_detail_colly->save();
                        }
                    } else if ($detail->quality_control2_id) {
                        $quality_control2 = QualityControl2::find($detail->quality_control2_id);
                        if ($quality_control2) {
                            $quality_control2->status_recondition = 0;
                            $quality_control2->save();
                        }
                    } else if ($detail->sale_return_detail_id) {
                        $sale_return_detail = SaleReturnDetail::find($detail->sale_return_detail_id);
                        if ($sale_return_detail) {
                            $sale_return_detail->status_recondition = 0;
                            $sale_return_detail->save();
                        }
                    } else if ($detail->recondition_residual_id) {
                        $recondition_residual = ReconditionResidual::find($detail->recondition_residual_id);
                        if ($recondition_residual) {
                            $recondition_residual->status_recondition = 0;
                            $recondition_residual->save();
                        }
                    }
                }

                $recondition->status = Recondition::STATUS['DELETED'];

                if ($recondition->delete()) {
                    DB::commit();

                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return $this->response(400, $response);
            }
        }
    }
}
