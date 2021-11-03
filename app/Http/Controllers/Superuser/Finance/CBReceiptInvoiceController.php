<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\DataTables\Finance\CBReceiptInvoiceTable;
use App\Entities\Finance\CBReceiptInvoice;
use App\Entities\Finance\CBReceiptInvoiceDetail;
use App\Entities\Finance\MarketplaceReceipt;
use App\Entities\Finance\MarketplaceReceiptDetail;
use App\Entities\Master\Company;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Account\Superuser;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCoa;
use App\Entities\Sale\SalesOrder;
use App\Repositories\MasterRepo;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use DB;
use Validator;
use DomPDF;

class CBReceiptInvoiceController extends Controller
{
    public function json(Request $request, CBReceiptInvoiceTable $datatable)
    {
        return $datatable->build();
    }

    public function get_sales_order(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            // dd($request->q);
            $param = $request->q;
            // $customer = Customer::find($request->id);

            $sales_order = SalesOrder::where('status', SalesOrder::STATUS['ACC'])
                                // ->where('marketplace_order', SalesOrder::MARKETPLACE_ORDER['Non Marketplace'])
                                ->whereIn('warehouse_id', MasterRepo::warehouses_by_category(2)->pluck('id')->toArray());
            if($param != ""){
                $sales_order = $sales_order->where("code", "LIKE", $param.'%');
            }                   
            $sales_order = $sales_order->orderBy('order_date','desc')->limit(30)->get();
            foreach ($sales_order as $key => $value) {
                $checkIfActive = CBReceiptInvoiceDetail::where('sales_order_id', $value->id)
                                ->whereHas('receipt_invoice', function($query) {
                                    $query->where('status', CBReceiptInvoice::STATUS['ACTIVE']);
                                })
                                ->first();
                // if($checkIfActive == null) {
                    $total = 0;
                    $total_paid = CBReceiptInvoiceDetail::where('sales_order_id', $value->id)
                                ->whereHas('receipt_invoice', function($query) {
                                    $query->where('status', CBReceiptInvoice::STATUS['ACC']);
                                })
                                ->sum('paid');
                    if($total_paid) {
                        $total = $value->grand_total - $total_paid;
                    } else {
                        $total = $value->grand_total;
                    }

                    // if($total > 0) {
                        $data[] = [
                            'id'     => $value->id,
                            'code'       => $value->code,
                            'total'      => $total,
                            'grand_total' => $value->grand_total,
                            'marketplace' => $value->marketplace_order
                        ];
                    // }
                // }
            }
            // dd($request->q);

            return response()->json($data);
        }
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt (inv)-manage')) {
            return abort(403);
        }

        return view('superuser.finance.receipt_invoice.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt (inv)-create')) {
            return abort(403);
        }

        $data['coa'] = MasterRepo::coas_by_branch();
        $data['coas'] = $data['coa'];

        // $data['customers'] = MasterRepo::customers();
        $data['disable_mr'] = true;
        return view('superuser.finance.receipt_invoice.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:cb_receipt_invoice,code',
                'coa'  => 'required|integer',
                // 'customer'  => 'required|integer',
                'select_date' => 'required|date',
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

            if(!$request->so_id) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Please select Invoice at least 1 item.',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $superuser = Auth::guard('superuser')->user();
                    $soMarketplace = [];

                    $receipt_invoice = new CBReceiptInvoice;

                    $receipt_invoice->code = $request->code;
                    $receipt_invoice->type = $superuser->type;
                    $receipt_invoice->branch_office_id = $superuser->branch_office_id;
                    $receipt_invoice->coa_id = $request->coa;
                    // $receipt_invoice->customer_id = $request->customer;
                    $receipt_invoice->description = $request->note;

                    $receipt_invoice->select_date = $request->select_date;
                    
                    $receipt_invoice->status = CBReceiptInvoice::STATUS['ACTIVE'];

                    if ($receipt_invoice->save()) {

                        if($request->so_id) {
                            foreach($request->so_id as $key => $value){


                                if($request->so_id[$key]) {
                                    $receipt_invoice_detail = new CBReceiptInvoiceDetail;
                                    $receipt_invoice_detail->cb_receipt_invoice_id = $receipt_invoice->id;
                                    $receipt_invoice_detail->sales_order_id = $request->so_id[$key];
                                    $receipt_invoice_detail->total = $request->total[$key];
                                    $receipt_invoice_detail->paid = $request->paid[$key];
                                    $receipt_invoice_detail->save();

                                    
                                    if($request->marketplace[$key] > 0){
                                        $soMarketplace[] = $key;
                                    }
                                }
                            }

                            if(sizeof($soMarketplace) > 0){
                                // dd($soMarketplace);
                                self::storeMR($request, $soMarketplace);
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
    
                        $response['redirect_to'] = route('superuser.finance.receipt_invoice.index');
    
                        return $this->response(200, $response);
                    }
                } catch (\Exception $e) {
                    DB::rollback();
                    dd($e);
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

    public function storeMR(Request $request, $key){
        $superuser = Auth::guard('superuser')->user();

        foreach($key as $k=>$v){
            $inv = $request->inv_code[$v];
            $grand_total = $request->grand_total[$v];
            $type = $request->type[$v];
            $val = $request->paid[$v];
            $mp_cp = 0; $mp_c1 = 0; $mp_c2 = 0; $mp_c3 = 0;
            // ADD MR
            $marketplace_receipt = new MarketplaceReceipt;
            $marketplace_receipt->code = $inv;
            // $marketplace_receipt->store_name = $this->store_name;
            // $marketplace_receipt->kode_transaksi = $this->kode_transaksi;
            $marketplace_receipt->total = $grand_total;
            if($type == "payment"){ $mp_cp = $val; }
            if($type == "cost_1"){ $mp_c1 = $val; }
            if($type == "cost_2"){ $mp_c2 = $val; }
            if($type == "cost_3"){ $mp_c3 = $val; }

            $marketplace_receipt->payment = $mp_cp;
            $marketplace_receipt->cost_1 = $mp_c1;
            $marketplace_receipt->cost_2 = $mp_c2;
            $marketplace_receipt->cost_3 = $mp_c3; 
            $marketplace_receipt->status = 0;
            $marketplace_receipt->created_by = $superuser->id;
            // if($row['tgl_pencairan']) {
            $marketplace_receipt->created_at = $request->select_date;
            // }
            if($marketplace_receipt->save()){

                $total_paid = $marketplace_receipt->payment + $marketplace_receipt->cost_1 + $marketplace_receipt->cost_2 + $marketplace_receipt->cost_3 + $marketplace_receipt->paid;
                if ($marketplace_receipt->total < $total_paid) {
                    // $errors[] = array('INVOICE ' . $marketplace_receipt->code . ' EXCEED THE TOTAL PAYMENT : skipping');
                    // continue;
                }
                // ADD JOURNAL DEBET
                if ($marketplace_receipt->payment) {
                    $journal = new Journal;
                    $journal->coa_id = $request->mp_coa_payment;
                    $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                    $journal->debet = $marketplace_receipt->payment;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->created_at = $marketplace_receipt->created_at;
                    $journal->save();
                }
                if ($marketplace_receipt->cost_1) {
                    $journal = new Journal;
                    $journal->coa_id = $request->mp_coa_cost1;
                    $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                    $journal->debet = $marketplace_receipt->cost_1;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->created_at = $marketplace_receipt->created_at;
                    $journal->save();
                }
                if ($marketplace_receipt->cost_2) {
                    $journal = new Journal;
                    $journal->coa_id = $request->mp_coa_cost2;
                    $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                    $journal->debet = $marketplace_receipt->cost_2;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->created_at = $marketplace_receipt->created_at;
                    $journal->save();
                }
                if ($marketplace_receipt->cost_3) {
                    $journal = new Journal;
                    $journal->coa_id = $request->mp_coa_cost3;
                    $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                    $journal->debet = $marketplace_receipt->cost_3;
                    $journal->status = Journal::STATUS['UNPOST'];
                    $journal->created_at = $marketplace_receipt->created_at;
                    $journal->save();
                }
                // ADD JOURNAL CREDIT
                $journal = new Journal;
                $journal->coa_id = $request->mp_coa_credit;
                $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                $journal->credit = $marketplace_receipt->payment + $marketplace_receipt->cost_1 + $marketplace_receipt->cost_2 + $marketplace_receipt->cost_3;
                $journal->status = Journal::STATUS['UNPOST'];
                $journal->created_at = $marketplace_receipt->created_at;
                $journal->save();

                // ADD MR-DETAIL
                $mr_detail = new MarketplaceReceiptDetail;
                $mr_detail->marketplace_receipt_id = $marketplace_receipt->id;
                $mr_detail->payment = $marketplace_receipt->payment;
                $mr_detail->cost = $marketplace_receipt->cost_1 + $marketplace_receipt->cost_2 + $marketplace_receipt->cost_3;
                if ($marketplace_receipt->payment) {
                    $mr_detail->payment_coa = $request->coa_payment;
                }
                if ($marketplace_receipt->cost_1) {
                    $mr_detail->cost_1 = $marketplace_receipt->cost_1;
                    $mr_detail->cost_1_coa = $request->coa_cost_1;
                }
                if ($marketplace_receipt->cost_2) {
                    $mr_detail->cost_2 = $marketplace_receipt->cost_2;
                    $mr_detail->cost_2_coa = $request->coa_cost_2;
                }
                if ($marketplace_receipt->cost_3) {
                    $mr_detail->cost_3 = $marketplace_receipt->cost_3;
                    $mr_detail->cost_3_coa = $request->coa_cost_3;
                }
                $mr_detail->credit_coa = $request->mp_coa_credit;
                $mr_detail->created_at = $marketplace_receipt->created_at;
                $mr_detail->save();

                // UPDATE MR
                $mr = MarketplaceReceipt::find($marketplace_receipt->id);
                $mr->paid = $total_paid;
                if ($total_paid < $marketplace_receipt->total) {
                    $mr->payment = 0;
                    $mr->cost_1 = null;
                    $mr->cost_2 = null;
                    $mr->cost_3 = null;
                    $mr->status = 0;
                } else {
                    $mr->status = 1;
                }

                $mr->save();

                // UPDATE KODE PELUNASAN IN SALES ORDER TABLE
                $sales_order = SalesOrder::where('code', $marketplace_receipt->code)->first();
                $coa = Coa::find($request->mp_coa_credit);
                if($sales_order && $coa && $coa->kode_pelunasan) {
                    $sales_order->kode_pelunasan = $coa->kode_pelunasan . Carbon::parse($marketplace_receipt->created_at)->format('Y/m/d');
                    $sales_order->save();
                }
            }
        }


    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt (inv)-show')) {
            return abort(403);
        }

        $data['receipt_invoice'] = CBReceiptInvoice::find($id);

        return view('superuser.finance.receipt_invoice.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('cash/bank receipt (inv)-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['receipt_invoice'] = CBReceiptInvoice::find($id);
        $totalPaid = 0;
        foreach($data['receipt_invoice']->details as $detail){
            $totalPaid += $detail->paid;
        }
        $data['totalPaid'] = $totalPaid;

        //return view('superuser.finance.receipt_invoice.pdf', $data);
        $pdf = DomPDF::loadView('superuser.finance.receipt_invoice.pdf', $data);
        $pdf->setPaper('a5', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt (inv)-edit')) {
            return abort(403);
        }

        $data['receipt_invoice'] = CBReceiptInvoice::find($id);
        $data['coa'] = MasterRepo::coas_by_branch();
        $data['customers'] = MasterRepo::customers();
        
        return view('superuser.finance.receipt_invoice.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $receipt_invoice = CBReceiptInvoice::find($id);

            if ($receipt_invoice == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:cb_receipt_invoice,code,' . $receipt_invoice->id,
                'coa'  => 'required|integer',
                'select_date' => 'required|date',
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

            if(!$request->so_id) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Please select PPB at least 1 item.',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $receipt_invoice->code = $request->code;
                    $receipt_invoice->coa_id = $request->coa;
                    $receipt_invoice->select_date = $request->select_date;
                    $receipt_invoice->description = $request->note;
                    if ($receipt_invoice->save()) {
                        if($request->ids_delete) {
                            $pieces = explode(",",$request->ids_delete);
                            foreach($pieces as $piece){
                                CBReceiptInvoiceDetail::where('id', $piece)->delete();
                            }
                        }

                        if($request->so_id) {
                            foreach($request->so_id as $key => $value){
                                if($request->so_id[$key]) {
                                    if($request->edit[$key]) {
                                        $receipt_invoice_detail = CBReceiptInvoiceDetail::find($request->edit[$key]);
                                        
                                        $receipt_invoice_detail->paid = $request->paid[$key];
                                        $receipt_invoice_detail->save();
                                    } else {
                                        $receipt_invoice_detail = new CBReceiptInvoiceDetail;
                                        $receipt_invoice_detail->cb_receipt_invoice_id = $receipt_invoice->id;
                                        $receipt_invoice_detail->sales_order_id = $request->so_id[$key];
                                        $receipt_invoice_detail->total = $request->total[$key];
                                        $receipt_invoice_detail->paid = $request->paid[$key];
                                        $receipt_invoice_detail->save();
                                    }
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
    
                        $response['redirect_to'] = route('superuser.finance.receipt_invoice.index');
    
                        return $this->response(200, $response);
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

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('cash/bank receipt (inv)-acc')) {
                return abort(403);
            }

            $receipt_invoice = CBReceiptInvoice::find($id);

            if ($receipt_invoice === null) {
                abort(404);
            }

            $superuser = Auth::guard('superuser')->user();
            $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

            if($journal_periode) {
                $min_date = Carbon::parse( $journal_periode->to_date );
                if( $receipt_invoice->select_date <= $min_date ) {
                    $response['failed'] = 'Select date is invalid.';
                    return $this->response(200, $response);
                }
            }

            DB::beginTransaction();
            try {
                $failed = '';

                // 
                $customer_coa = CustomerCoa::where('customer_id', $receipt_invoice->customer_id)
                                ->where('type', $superuser->type)
                                ->where('branch_office_id', $superuser->branch_office_id)
                                ->first();
                if($customer_coa == null OR $customer_coa->coa_id == null) {
                    $failed = 'Supplier Setting is not set, please contact your Administrator!';
                } else {
                    foreach ($receipt_invoice->details as $detail) {
                        // ADD JOURNAL
                        $journal = new Journal;
                        $journal->coa_id = $receipt_invoice->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_RECEIPT_INV'].$detail->sales_order->code;
                        $journal->debet = $detail->paid;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $receipt_invoice->select_date;
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $customer_coa->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_RECEIPT_INV'].$detail->sales_order->code;
                        $journal->credit = $detail->paid;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $receipt_invoice->select_date;
                        $journal->save();
                    }
                }

                // if($failed) {
                //     $response['failed'] = $failed;
                //     return $this->response(200, $response);
                // } else {
                    $receipt_invoice->status = CBReceiptInvoice::STATUS['ACC'];
                    $receipt_invoice->save();
                    
                    DB::commit();

                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                // }
            } catch (\Exception $e) {
                DB::rollback();
                return $this->response(400, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('cash/bank receipt (inv)-delete')) {
                return abort(403);
            }

            $receipt_detail = CBReceiptInvoice::find($id);

            if ($receipt_detail === null) {
                abort(404);
            }

            $receipt_detail->status = CBReceiptInvoice::STATUS['DELETED'];

            if ($receipt_detail->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

}
