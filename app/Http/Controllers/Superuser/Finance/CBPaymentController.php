<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\DataTables\Finance\CBPaymentTable;
use App\Entities\Finance\CBPayment;
use App\Entities\Finance\CBPaymentDetail;
use App\Entities\Master\Company;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Repositories\MasterRepo;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use DB;
use Validator;
use DomPDF;

class CBPaymentController extends Controller
{
    public function json(Request $request, CBPaymentTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank payment-manage')) {
            return abort(403);
        }

        return view('superuser.finance.payment.index');
    }

    public function create()
    {       
        if(!Auth::guard('superuser')->user()->can('cash/bank payment-create')) {
            return abort(403);
        }

        $data['coa'] = MasterRepo::coas_by_branch();

        return view('superuser.finance.payment.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'transaction' => 'required|string',
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

            if($request->subtotal_credit < 1 OR $request->subtotal_debet < 1) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Debit and Credit must be filled',
                ];
                return $this->response(400, $response);
            }

            if($request->subtotal_credit != $request->subtotal_debet) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Debit and Credit amount must be same',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $superuser = Auth::guard('superuser')->user();

                    $payment = new CBPayment;

                    $payment->code = $request->transaction;
                    $payment->select_date = $request->select_date;
                    $payment->type = $superuser->type;
                    $payment->branch_office_id = $superuser->branch_office_id;
                    $payment->status = CBPayment::STATUS['ACTIVE'];

                    if ($payment->save()) {

                        if($request->coa_credit_detail) {
                            foreach($request->coa_credit_detail as $key => $value){
                                if($request->coa_credit_detail[$key]) {
                                    $payment_credit = new CBPaymentDetail;
                                    $payment_credit->cb_payment_id = $payment->id;
                                    $payment_credit->coa_id = $request->coa_credit_detail[$key];
                                    $payment_credit->name = $request->transaction;
                                    $payment_credit->total = $request->total_credit_detail[$key];
                                    $payment_credit->status_transaction = CBPaymentDetail::STATUS_TRANSACTION['CREDIT'];
                                    $payment_credit->save();
                                }
                            }
                        }

                        if($request->coa_debet_detail) {
                            foreach($request->coa_debet_detail as $key => $value){
                                if($request->coa_debet_detail[$key]) {
                                    $payment_debet = new CBPaymentDetail;
                                    $payment_debet->cb_payment_id = $payment->id;
                                    $payment_debet->coa_id = $request->coa_debet_detail[$key];
                                    $payment_debet->name = $request->transaction;
                                    $payment_debet->total = $request->total_debet_detail[$key];
                                    $payment_debet->status_transaction = CBPaymentDetail::STATUS_TRANSACTION['DEBET'];
                                    $payment_debet->save();
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
    
                        $response['redirect_to'] = route('superuser.finance.payment.index');
    
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

    public function edit($id)
    {   
        if(!Auth::guard('superuser')->user()->can('cash/bank payment-edit')) {
            return abort(403);
        }

        $data['payment'] = CBPayment::find($id);
        $data['payment_credit'] = CBPaymentDetail::where('cb_payment_id', $id)->where('status_transaction', CBPaymentDetail::STATUS_TRANSACTION['CREDIT'])->get();
        $data['payment_debet'] = CBPaymentDetail::where('cb_payment_id', $id)->where('status_transaction', CBPaymentDetail::STATUS_TRANSACTION['DEBET'])->get();

        $data['coa'] = MasterRepo::coas_by_branch();
        
        return view('superuser.finance.payment.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $payment = CBPayment::find($id);

            if ($payment == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'transaction' => 'required|string',
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

            if($request->subtotal_credit < 1 OR $request->subtotal_debet < 1) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Debit and Credit must be filled',
                ];
                return $this->response(400, $response);
            }

            if($request->subtotal_credit != $request->subtotal_debet) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Debit and Credit amount must be same',
                ];
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $payment->code = $request->transaction;
                    $payment->select_date = $request->select_date;
                    if ($payment->save()) {
                        if($request->ids_delete) {
                            $pieces = explode(",",$request->ids_delete);
                            foreach($pieces as $piece){
                                CBPaymentDetail::where('id', $piece)->delete();
                            }
                        }

                        if($request->coa_credit_detail) {
                            foreach($request->coa_credit_detail as $key => $value){
                                if($request->coa_credit_detail[$key]) {
                                    if($request->edit_credit_detail[$key]) {
                                        $payment_credit = CBPaymentDetail::find($request->edit_credit_detail[$key]);
                                        
                                        $payment_credit->name = $request->transaction;
                                        $payment_credit->total = $request->total_credit_detail[$key];
                                        $payment_credit->save();
                                    } else {
                                        $payment_credit = new CBPaymentDetail;
                                        $payment_credit->cb_payment_id = $payment->id;
                                        $payment_credit->coa_id = $request->coa_credit_detail[$key];
                                        $payment_credit->name = $request->transaction;
                                        $payment_credit->total = $request->total_credit_detail[$key];
                                        $payment_credit->status_transaction = CBPaymentDetail::STATUS_TRANSACTION['CREDIT'];
                                        $payment_credit->save();
                                    }
                                }
                            }
                        }

                        if($request->coa_debet_detail) {
                            foreach($request->coa_debet_detail as $key => $value){
                                if($request->coa_debet_detail[$key]) {
                                    if($request->edit_debet_detail[$key]) {
                                        $payment_debet = CBPaymentDetail::find($request->edit_debet_detail[$key]);
                                        
                                        $payment_debet->name = $request->transaction;
                                        $payment_debet->total = $request->total_debet_detail[$key];
                                        $payment_debet->save();
                                    } else {
                                        $payment_debet = new CBPaymentDetail;
                                        $payment_debet->cb_payment_id = $payment->id;
                                        $payment_debet->coa_id = $request->coa_debet_detail[$key];
                                        $payment_debet->name = $request->transaction;
                                        $payment_debet->total = $request->total_debet_detail[$key];
                                        $payment_debet->status_transaction = CBPaymentDetail::STATUS_TRANSACTION['DEBET'];
                                        $payment_debet->save();
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
    
                        $response['redirect_to'] = route('superuser.finance.payment.index');
    
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
            if(!Auth::guard('superuser')->user()->can('cash/bank payment-acc')) {
                return abort(403);
            }

            $payment = CBPayment::find($id);

            if ($payment === null) {
                abort(404);
            }

            $superuser = Auth::guard('superuser')->user();
            $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

            if($journal_periode) {
                $min_date = Carbon::parse( $journal_periode->to_date );
                if( $payment->select_date <= $min_date ) {
                    $response['failed'] = 'Select date is invalid.';
                    return $this->response(200, $response);
                }
            }

            DB::beginTransaction();
            try {
                foreach ($payment->details as $detail) {
                    // ADD JOURNAL
                    if($detail->status_transaction == CBPaymentDetail::STATUS_TRANSACTION['DEBET']) {
                        $journal = new Journal;
                        $journal->coa_id = $detail->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_PAYMENT'].$detail->name;
                        $journal->debet = $detail->total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $payment->select_date;
                        $journal->save();
                    } else {
                        $journal = new Journal;
                        $journal->coa_id = $detail->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_PAYMENT'].$detail->name;
                        $journal->credit = $detail->total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $payment->select_date;
                        $journal->save();
                    }
                }

                $payment->status = CBPayment::STATUS['ACC'];
                $payment->save();
                
                DB::commit();

                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            } catch (\Exception $e) {
                DB::rollback();
                return $this->response(400, $response);
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank payment-show')) {
            return abort(403);
        }

        $data['payment'] = CBPayment::find($id);
        $data['payment_credit'] = CBPaymentDetail::where('cb_payment_id', $id)->where('status_transaction', CBPaymentDetail::STATUS_TRANSACTION['CREDIT'])->get();
        $data['payment_debet'] = CBPaymentDetail::where('cb_payment_id', $id)->where('status_transaction', CBPaymentDetail::STATUS_TRANSACTION['DEBET'])->get();

        return view('superuser.finance.payment.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('cash/bank payment-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['payment'] = CBPayment::find($id);
        $data['payment_credit'] = CBPaymentDetail::where('cb_payment_id', $id)->where('status_transaction', CBPaymentDetail::STATUS_TRANSACTION['CREDIT'])->get();
        $data['payment_debet'] = CBPaymentDetail::where('cb_payment_id', $id)->where('status_transaction', CBPaymentDetail::STATUS_TRANSACTION['DEBET'])->get();

        $pdf = DomPDF::loadView('superuser.finance.payment.pdf', $data);
        $pdf->setPaper('a5', 'landscape');

        if ($protect) {
            $pdf->setEncryption('12345678');
        }

        if ($generate) {
            return $pdf;
        }

        return $pdf->stream();
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('cash/bank payment-delete')) {
                return abort(403);
            }

            $payment = CBPayment::find($id);

            if ($payment === null) {
                abort(404);
            }

            $payment->status = CBPayment::STATUS['DELETED'];

            if ($payment->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

}
