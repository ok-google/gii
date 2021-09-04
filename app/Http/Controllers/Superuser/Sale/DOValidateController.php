<?php

namespace App\Http\Controllers\Superuser\Sale;

use App\Http\Controllers\Controller;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\Hpp;
use App\Entities\Finance\SettingFinance;
use App\Entities\Master\CustomerCoa;
use App\Entities\Master\CustomerCoaPenjualan;
use App\Entities\Sale\SalesOrder;
use App\Entities\Sale\SalesOrderDetail;
use App\Entities\Sale\DeliveryOrderDetail;
use App\Entities\Sale\DeliveryOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class DOValidateController extends Controller
{
    public function get_barcode(Request $request)
    {
        if ($request->ajax()) {
            $msg = '';

            DB::beginTransaction();
            try {
                $sales_order = SalesOrder::where('resi', $request->code)->first();
                $superuser = Auth::guard('superuser')->user();
                
                if($sales_order AND $request->code) {
                    $delivery_order_detail = DeliveryOrderDetail::where('sales_order_id', $sales_order->id)->first();
                    if($delivery_order_detail) {
                        if($delivery_order_detail->status_validate != 1) {
                            $piutang_coa = null;
                            $penjualan_coa = null;
                            if($sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Non Marketplace']) {
                                $customer_coa = CustomerCoa::where('customer_id', $sales_order->customer_id)->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->first();
                                if($customer_coa != null AND $customer_coa->coa_id != null) {
                                    $piutang_coa = $customer_coa->coa_id;
                                }

                                $customer_coa_penjualan = CustomerCoaPenjualan::where('customer_id', $sales_order->customer_id)->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->first();
                                if($customer_coa_penjualan != null AND $customer_coa_penjualan->coa_id != null) {
                                    $penjualan_coa = $customer_coa_penjualan->coa_id;
                                }
                            } elseif ($sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Shopee']) {
                                $piutang_shopee = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'piutang_shopee')->first();
                                if($piutang_shopee != null AND $piutang_shopee->coa_id != null) {
                                    $piutang_coa = $piutang_shopee->coa_id;
                                }

                                $penjualan_shopee = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_shopee')->first();
                                if($penjualan_shopee != null AND $penjualan_shopee->coa_id != null) {
                                    $penjualan_coa = $penjualan_shopee->coa_id;
                                }
                            } elseif ($sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Tokopedia']) {
                                $piutang_tokopedia = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'piutang_tokopedia')->first();
                                if($piutang_tokopedia != null AND $piutang_tokopedia->coa_id != null) {
                                    $piutang_coa = $piutang_tokopedia->coa_id;
                                }

                                $penjualan_tokopedia = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_tokopedia')->first();
                                if($penjualan_tokopedia != null AND $penjualan_tokopedia->coa_id != null) {
                                    $penjualan_coa = $penjualan_tokopedia->coa_id;
                                }
                            } elseif ($sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Lazada']) {
                                $piutang_lazada = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'piutang_lazada')->first();
                                if($piutang_lazada != null AND $piutang_lazada->coa_id != null) {
                                    $piutang_coa = $piutang_lazada->coa_id;
                                }

                                $penjualan_lazada = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_lazada')->first();
                                if($penjualan_lazada != null AND $penjualan_lazada->coa_id != null) {
                                    $penjualan_coa = $penjualan_lazada->coa_id;
                                }
                            } elseif ($sales_order->marketplace_order == SalesOrder::MARKETPLACE_ORDER['Blibli']) {
                                $piutang_blibli = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'piutang_blibli')->first();
                                if($piutang_blibli != null AND $piutang_blibli->coa_id != null) {
                                    $piutang_coa = $piutang_blibli->coa_id;
                                }

                                $penjualan_blibli = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'penjualan_blibli')->first();
                                if($penjualan_blibli != null AND $penjualan_blibli->coa_id != null) {
                                    $penjualan_coa = $penjualan_blibli->coa_id;
                                }
                            }

                            $do_hpp_debet = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'do_hpp_debet')->first();

                            $do_hpp_credit = SettingFinance::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('key', 'do_hpp_credit')->first();

                            if($piutang_coa == null OR $penjualan_coa == null OR $do_hpp_debet == null OR $do_hpp_debet->coa_id == null OR $do_hpp_credit == null OR $do_hpp_credit->coa_id == null) {
                                $msg = 'Finance Setting is not set, please contact your Administrator!';
                            } else {
                                $delivery_order_detail->status_validate = 1;
                                $delivery_order_detail->scan_by = $superuser->id;
                                if($delivery_order_detail->save()) {
                                    $check_do = DeliveryOrderDetail::where('delivery_order_id', $delivery_order_detail->delivery_order_id)
                                    ->where('status_validate', '0')    
                                    ->first();
                                    if($check_do == null) {
                                        $delivery_order = DeliveryOrder::find($delivery_order_detail->delivery_order_id);
                                        $delivery_order->status = DeliveryOrder::STATUS['ACC'];
                                        $delivery_order->save();
                                    }

                                    $hpp_grand_total = 0;
                                    // REMOVE HPP
                                    foreach ($sales_order->sales_order_details as $detail) {
                                        if($detail->product->non_stock == '1') {
                                            continue;
                                        }
                                        $hpp_total = 0;
                                        for ($i=0; $i < $detail->quantity ; $i++) { 
                                            $hpp = Hpp::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->where('product_id', $detail->product_id)->orderBy('created_at', 'ASC')->first();
                                            
                                            if( $hpp ) {
                                                $hpp_total = $hpp_total + $hpp->price;

                                                $min = $hpp->quantity - 1;
                                                if($min > 0) {
                                                    $hpp->quantity = $min;
                                                    $hpp->save();
                                                } else {
                                                    $hpp->delete();
                                                }
                                            }
                                        }

                                        $sales_order_detail = SalesOrderDetail::find($detail->id);
                                        $sales_order_detail->hpp_total = $hpp_total;
                                        $sales_order_detail->save();

                                        $hpp_grand_total = $hpp_grand_total + $hpp_total;
                                    }

                                    // ADD JOURNAL
                                    // Transaction Debet
                                    $journal = new Journal;
                                    $journal->coa_id = $piutang_coa;
                                    $journal->name = Journal::PREJOURNAL['DO_VALIDATE'].$sales_order->code;
                                    $journal->debet = $sales_order->grand_total;
                                    $journal->status = Journal::STATUS['UNPOST'];
                                    $journal->save();

                                    // Transaction Credit
                                    $journal = new Journal;
                                    $journal->coa_id = $penjualan_coa;
                                    $journal->name = Journal::PREJOURNAL['DO_VALIDATE'].$sales_order->code;
                                    $journal->credit = $sales_order->grand_total;
                                    $journal->status = Journal::STATUS['UNPOST'];
                                    $journal->save();

                                    // HPP Debet
                                    $journal = new Journal;
                                    $journal->coa_id = $do_hpp_debet->coa_id;
                                    $journal->name = Journal::PREJOURNAL['DO_VALIDATE'].$sales_order->code;
                                    $journal->debet = $hpp_grand_total;
                                    $journal->status = Journal::STATUS['UNPOST'];
                                    $journal->save();

                                    // HPP Credit
                                    $journal = new Journal;
                                    $journal->coa_id = $do_hpp_credit->coa_id;
                                    $journal->name = Journal::PREJOURNAL['DO_VALIDATE'].$sales_order->code;
                                    $journal->credit = $hpp_grand_total;
                                    $journal->status = Journal::STATUS['UNPOST'];
                                    $journal->save();

                                    $msg = 'VALIDATE SUCCESS';
                                }
                            }
                        } else {
                            $msg = 'Barcode has been validate.';
                        }
                    } else {
                        $msg = 'Barcode not found.';
                    }
                } else {
                    $msg = 'Barcode not found.';
                }

                if($msg == 'VALIDATE SUCCESS') {
                    DB::commit();
                    return response()->json(['code'=> 200, 'msg' => $msg]);
                } else {
                    DB::rollback();
                    return response()->json(['code'=> 200, 'msg' => $msg]);
                }
                
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['code'=> 200, 'msg' => 'Internal Server Error!']);
            }
        }
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('do validate-manage')) {
            return abort(403);
        }

        return view('superuser.sale.do_validate.index');
    }

}
