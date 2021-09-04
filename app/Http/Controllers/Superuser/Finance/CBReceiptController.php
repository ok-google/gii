<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\DataTables\Finance\CBReceiptTable;
use App\Entities\Finance\CBReceipt;
use App\Entities\Finance\CBReceiptDetail;
use App\Entities\Master\Company;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Account\Superuser;
use App\Repositories\MasterRepo;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use DB;
use Validator;
use DomPDF;

class CBReceiptController extends Controller
{
    public function json(Request $request, CBReceiptTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt-manage')) {
            return abort(403);
        }

        return view('superuser.finance.receipt.index');
    }

    public function create()
    {       
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt-create')) {
            return abort(403);
        }

        $data['coa'] = MasterRepo::coas_by_branch();

        return view('superuser.finance.receipt.create', $data);
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

                    $receipt = new CBReceipt;

                    $receipt->code = $request->transaction;
                    $receipt->select_date = $request->select_date;
                    $receipt->type = $superuser->type;
                    $receipt->branch_office_id = $superuser->branch_office_id;
                    $receipt->status = CBReceipt::STATUS['ACTIVE'];

                    if ($receipt->save()) {

                        if($request->coa_credit_detail) {
                            foreach($request->coa_credit_detail as $key => $value){
                                if($request->coa_credit_detail[$key]) {
                                    $receipt_credit = new CBReceiptDetail;
                                    $receipt_credit->cb_receipt_id = $receipt->id;
                                    $receipt_credit->coa_id = $request->coa_credit_detail[$key];
                                    $receipt_credit->name = $request->transaction;
                                    $receipt_credit->total = $request->total_credit_detail[$key];
                                    $receipt_credit->status_transaction = CBReceiptDetail::STATUS_TRANSACTION['CREDIT'];
                                    $receipt_credit->save();
                                }
                            }
                        }

                        if($request->coa_debet_detail) {
                            foreach($request->coa_debet_detail as $key => $value){
                                if($request->coa_debet_detail[$key]) {
                                    $receipt_debet = new CBReceiptDetail;
                                    $receipt_debet->cb_receipt_id = $receipt->id;
                                    $receipt_debet->coa_id = $request->coa_debet_detail[$key];
                                    $receipt_debet->name = $request->transaction;
                                    $receipt_debet->total = $request->total_debet_detail[$key];
                                    $receipt_debet->status_transaction = CBReceiptDetail::STATUS_TRANSACTION['DEBET'];
                                    $receipt_debet->save();
                                }
                            }
                        }

                        DB::commit();

                        $response['notification'] = [
                            'alert' => 'notify',
                            'type' => 'success',
                            'content' => 'Success',
                        ];
    
                        $response['redirect_to'] = route('superuser.finance.receipt.index');
    
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
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt-edit')) {
            return abort(403);
        }

        $data['receipt'] = CBReceipt::find($id);
        $data['receipt_credit'] = CBReceiptDetail::where('cb_receipt_id', $id)
                                ->where('status_transaction', CBReceiptDetail::STATUS_TRANSACTION['CREDIT'])->get();
        $data['receipt_debet'] = CBReceiptDetail::where('cb_receipt_id', $id)
                                ->where('status_transaction', CBReceiptDetail::STATUS_TRANSACTION['DEBET'])->get();

        $data['coa'] = MasterRepo::coas_by_branch();
        
        return view('superuser.finance.receipt.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $receipt = CBReceipt::find($id);

            if ($receipt == null) {
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
                    $receipt->code = $request->transaction;
                    $receipt->select_date = $request->select_date;
                    if ($receipt->save()) {
                        if($request->ids_delete) {
                            $pieces = explode(",",$request->ids_delete);
                            foreach($pieces as $piece){
                                CBReceiptDetail::where('id', $piece)->delete();
                            }
                        }

                        if($request->coa_credit_detail) {
                            foreach($request->coa_credit_detail as $key => $value){
                                if($request->coa_credit_detail[$key]) {
                                    if($request->edit_credit_detail[$key]) {
                                        $receipt_credit = CBReceiptDetail::find($request->edit_credit_detail[$key]);
                                        
                                        $receipt_credit->name = $request->transaction;
                                        $receipt_credit->total = $request->total_credit_detail[$key];
                                        $receipt_credit->save();
                                    } else {
                                        $receipt_credit = new CBReceiptDetail;
                                        $receipt_credit->cb_receipt_id = $receipt->id;
                                        $receipt_credit->coa_id = $request->coa_credit_detail[$key];
                                        $receipt_credit->name = $request->transaction;
                                        $receipt_credit->total = $request->total_credit_detail[$key];
                                        $receipt_credit->status_transaction = CBReceiptDetail::STATUS_TRANSACTION['CREDIT'];
                                        $receipt_credit->save();
                                    }
                                }
                            }
                        }

                        if($request->coa_debet_detail) {
                            foreach($request->coa_debet_detail as $key => $value){
                                if($request->coa_debet_detail[$key]) {
                                    if($request->edit_debet_detail[$key]) {
                                        $receipt_debet = CBReceiptDetail::find($request->edit_debet_detail[$key]);
                                        
                                        $receipt_debet->name = $request->transaction;
                                        $receipt_debet->total = $request->total_debet_detail[$key];
                                        $receipt_debet->save();
                                    } else {
                                        $receipt_debet = new CBReceiptDetail;
                                        $receipt_debet->cb_receipt_id = $receipt->id;
                                        $receipt_debet->coa_id = $request->coa_debet_detail[$key];
                                        $receipt_debet->name = $request->transaction;
                                        $receipt_debet->total = $request->total_debet_detail[$key];
                                        $receipt_debet->status_transaction = CBReceiptDetail::STATUS_TRANSACTION['DEBET'];
                                        $receipt_debet->save();
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
    
                        $response['redirect_to'] = route('superuser.finance.receipt.index');
    
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
            if(!Auth::guard('superuser')->user()->can('cash/bank receipt-acc')) {
                return abort(403);
            }

            $receipt = CBReceipt::find($id);

            if ($receipt === null) {
                abort(404);
            }

            $superuser = Superuser::find(Auth::guard('superuser')->id());
            $journal_periode = JournalPeriode::where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->latest()->first();

            if($journal_periode) {
                $min_date = Carbon::parse( $journal_periode->to_date );
                if( $receipt->select_date <= $min_date ) {
                    $response['failed'] = 'Select date is invalid.';
                    return $this->response(200, $response);
                }
            }

            DB::beginTransaction();
            try {
                foreach ($receipt->details as $detail) {
                    // ADD JOURNAL
                    if($detail->status_transaction == CBReceiptDetail::STATUS_TRANSACTION['DEBET']) {
                        $journal = new Journal;
                        $journal->coa_id = $detail->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_RECEIPT'].$detail->name;
                        $journal->debet = $detail->total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $receipt->select_date;
                        $journal->save();
                    } else {
                        $journal = new Journal;
                        $journal->coa_id = $detail->coa_id;
                        $journal->name = Journal::PREJOURNAL['CB_RECEIPT'].$detail->name;
                        $journal->credit = $detail->total;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $receipt->select_date;
                        $journal->save();
                    }
                }

                $receipt->status = CBReceipt::STATUS['ACC'];
                $receipt->save();
                
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
        if(!Auth::guard('superuser')->user()->can('cash/bank receipt-show')) {
            return abort(403);
        }

        $data['receipt'] = CBReceipt::find($id);
        $data['receipt_credit'] = CBReceiptDetail::where('cb_receipt_id', $id)->where('status_transaction', CBReceiptDetail::STATUS_TRANSACTION['CREDIT'])->get();
        $data['receipt_debet'] = CBReceiptDetail::where('cb_receipt_id', $id)->where('status_transaction', CBReceiptDetail::STATUS_TRANSACTION['DEBET'])->get();

        return view('superuser.finance.receipt.show', $data);
    }

    public function pdf($id = NULL, $protect = false, $generate = false)
    {
        if (!Auth::guard('superuser')->user()->can('cash/bank receipt-print')) {
            return abort(403);
        }

        if ($id == NULL) {
            abort(404);
        }

        $data['company'] = Company::find(1);
        $data['receipt'] = CBReceipt::find($id);
        $data['receipt_credit'] = CBReceiptDetail::where('cb_receipt_id', $id)->where('status_transaction', CBReceiptDetail::STATUS_TRANSACTION['CREDIT'])->get();
        $data['receipt_debet'] = CBReceiptDetail::where('cb_receipt_id', $id)->where('status_transaction', CBReceiptDetail::STATUS_TRANSACTION['DEBET'])->get();

        $pdf = DomPDF::loadView('superuser.finance.receipt.pdf', $data);
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
            if(!Auth::guard('superuser')->user()->can('cash/bank receipt-delete')) {
                return abort(403);
            }

            $receipt = CBReceipt::find($id);

            if ($receipt === null) {
                abort(404);
            }

            $receipt->status = CBReceipt::STATUS['DELETED'];

            if ($receipt->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

}
