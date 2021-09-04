<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\DataTables\Finance\CBPaymentInvoiceTable;
use App\Entities\Finance\CBPaymentInvoice;
use App\Entities\Finance\CBPaymentInvoiceDetail;
use App\Entities\Master\Company;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Master\SupplierCoa;
use App\Entities\Master\Supplier;
use App\Entities\Purchasing\PurchaseOrder;
use App\Repositories\MasterRepo;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use DB;
use Validator;
use DomPDF;

class CBPaymentInvoiceController extends Controller
{
    public function json(Request $request, CBPaymentInvoiceTable $datatable)
    {
        return $datatable->build();
    }

    public function get_ppb(Request $request)
    {
        if ($request->ajax()) {
            $data = [];

            $supplier = Supplier::find($request->id);

            $ppb = PurchaseOrder::where('supplier_id', $supplier->id)
                                ->where('status', PurchaseOrder::STATUS['ACC'])
                                ->where('transaction_type', PurchaseOrder::TRANSACTION_TYPE['Non Tunai'])
                                ->whereIn('warehouse_id', MasterRepo::warehouses_by_category(1)->pluck('id')->toArray())
                                ->get();

            foreach ($ppb as $key => $value) {
                $checkIfActive = CBPaymentInvoiceDetail::where('ppb_id', $value->id)
                                ->whereHas('payment_invoice', function($query) {
                                    $query->where('status', CBPaymentInvoice::STATUS['ACTIVE']);
                                })
                                ->first();
                if($checkIfActive == null) {
                    $total = 0;
                    $total_paid = CBPaymentInvoiceDetail::where('ppb_id', $value->id)
                                ->whereHas('payment_invoice', function($query) {
                                    $query->where('status', CBPaymentInvoice::STATUS['ACC']);
                                })
                                ->sum('paid');
                    if($total_paid) {
                        $total = $value->grand_total_idr - $total_paid;
                    } else {
                        $total = $value->grand_total_idr;
                    }

                    if($total > 0) {
                        $data[] = [
                            'ppb_id'     => $value->id,
                            'code'       => $value->code,
                            'total'      => $total,
                        ];
                    }
                }
            }

            return response()->json(['code'=> 200, 'data' => $data, 'address' => $supplier->address]);
        }
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank payment (inv)-manage')) {
            return abort(403);
        }

        return view('superuser.finance.payment_invoice.index');
    }

    public function create()
    {       
        if(!Auth::guard('superuser')->user()->can('cash/bank payment (inv)-create')) {
            return abort(403);
        }

        $data['coa'] = MasterRepo::coas_by_branch();
        $data['suppliers'] = MasterRepo::suppliers();
                        
        return view('superuser.finance.payment_invoice.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:cb_payment_invoice,code',
                'coa'  => 'required|integer',
                'supplier'  => 'required|integer',
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

            if(!$request->ppb_id) {
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
                    $superuser = Auth::guard('superuser')->user();

                    $payment_invoice = new CBPaymentInvoice;

                    $payment_invoice->code = $request->code;
                    $payment_invoice->type = $superuser->type;
                    $payment_invoice->branch_office_id = $superuser->branch_office_id;
                    $payment_invoice->coa_id = $request->coa;
                    $payment_invoice->supplier_id = $request->supplier;
                    $payment_invoice->description = $request->note;

                    $payment_invoice->select_date = $request->select_date;
                    
                    $payment_invoice->status = CBPaymentInvoice::STATUS['ACTIVE'];

                    if ($payment_invoice->save()) {

                        if($request->ppb_id) {
                            foreach($request->ppb_id as $key => $value){
                                if($request->ppb_id[$key]) {
                                    $payment_invoice_detail = new CBPaymentInvoiceDetail;
                                    $payment_invoice_detail->cb_payment_invoice_id = $payment_invoice->id;
                                    $payment_invoice_detail->ppb_id = $request->ppb_id[$key];
                                    $payment_invoice_detail->total = $request->total[$key];
                                    $payment_invoice_detail->paid = $request->paid[$key];
                                    $payment_invoice_detail->save();
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
    
                        $response['redirect_to'] = route('superuser.finance.payment_invoice.index');
    
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

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank payment (inv)-show')) {
            return abort(403);
        }

        $data['payment_invoice'] = CBPaymentInvoice::find($id);

        return view('superuser.finance.payment_invoice.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('cash/bank payment (inv)-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['payment_invoice'] = CBPaymentInvoice::find($id);
        $totalPaid = 0;
        foreach($data['payment_invoice']->details as $detail){
            $totalPaid += $detail->paid;
        }
        $data['totalPaid'] = $totalPaid;

        //return view('superuser.finance.payment_invoice.pdf', $data);
        $pdf = DomPDF::loadView('superuser.finance.payment_invoice.pdf', $data);
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
        if(!Auth::guard('superuser')->user()->can('cash/bank payment (inv)-edit')) {
            return abort(403);
        }

        $data['payment_invoice'] = CBPaymentInvoice::find($id);
        $data['coa'] = MasterRepo::coas_by_branch();
        $data['suppliers'] = MasterRepo::suppliers();
                        
        return view('superuser.finance.payment_invoice.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $payment_invoice = CBPaymentInvoice::find($id);

            if ($payment_invoice == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:cb_payment_invoice,code,' . $payment_invoice->id,
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

            if(!$request->ppb_id) {
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
                    $payment_invoice->code = $request->code;
                    $payment_invoice->coa_id = $request->coa;
                    $payment_invoice->select_date = $request->select_date;
                    $payment_invoice->description = $request->note;
                    if ($payment_invoice->save()) {
                        if($request->ids_delete) {
                            $pieces = explode(",",$request->ids_delete);
                            foreach($pieces as $piece){
                                CBPaymentInvoiceDetail::where('id', $piece)->delete();
                            }
                        }

                        if($request->ppb_id) {
                            foreach($request->ppb_id as $key => $value){
                                if($request->ppb_id[$key]) {
                                    if($request->edit[$key]) {
                                        $payment_invoice_detail = CBPaymentInvoiceDetail::find($request->edit[$key]);
                                        
                                        $payment_invoice_detail->paid = $request->paid[$key];
                                        $payment_invoice_detail->save();
                                    } else {
                                        $payment_invoice_detail = new CBPaymentInvoiceDetail;
                                        $payment_invoice_detail->cb_payment_invoice_id = $payment_invoice->id;
                                        $payment_invoice_detail->ppb_id = $request->ppb_id[$key];
                                        $payment_invoice_detail->total = $request->total[$key];
                                        $payment_invoice_detail->paid = $request->paid[$key];
                                        $payment_invoice_detail->save();
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
    
                        $response['redirect_to'] = route('superuser.finance.payment_invoice.index');
    
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
            if(!Auth::guard('superuser')->user()->can('cash/bank payment (inv)-acc')) {
                return abort(403);
            }

            $payment_invoice = CBPaymentInvoice::find($id);

            if ($payment_invoice === null) {
                abort(404);
            }

            $superuser = Auth::guard('superuser')->user();
            $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

            if($journal_periode) {
                $min_date = Carbon::parse( $journal_periode->to_date );
                if( $payment_invoice->select_date <= $min_date ) {
                    $response['failed'] = 'Select date is invalid.';
                    return $this->response(200, $response);
                }
            }

            DB::beginTransaction();
            try {
                $failed = '';

                $supplier_coa = SupplierCoa::where('supplier_id', $payment_invoice->supplier_id)
                                ->where('type', $superuser->type)
                                ->where('branch_office_id', $superuser->branch_office_id)
                                ->first();
                if($supplier_coa == null OR $supplier_coa->coa_id == null) {
                    $failed = 'Supplier Setting is not set, please contact your Administrator!';
                } else {
                    foreach ($payment_invoice->details as $detail) {
                        // ADD JOURNAL
                        $journal = new Journal;
                        $journal->coa_id = $supplier_coa->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_PAYMENT_INV'].$detail->ppb->code;
                        $journal->debet = $detail->paid;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $payment_invoice->select_date;
                        $journal->save();

                        $journal = new Journal;
                        $journal->coa_id = $payment_invoice->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_PAYMENT_INV'].$detail->ppb->code;
                        $journal->credit = $detail->paid;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $payment_invoice->select_date;
                        $journal->save();
                    }
                }

                if($failed) {
                    $response['failed'] = $failed;
                    return $this->response(200, $response);
                } else {
                    $payment_invoice->status = CBPaymentInvoice::STATUS['ACC'];
                    $payment_invoice->save();
                    
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


    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('cash/bank payment (inv)-delete')) {
                return abort(403);
            }

            $payment_detail = CBPaymentInvoice::find($id);

            if ($payment_detail === null) {
                abort(404);
            }

            $payment_detail->status = CBPaymentInvoice::STATUS['DELETED'];

            if ($payment_detail->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

}
