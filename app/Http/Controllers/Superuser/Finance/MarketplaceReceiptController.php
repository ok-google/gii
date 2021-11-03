<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\DataTables\Finance\MarketplaceReceiptTable;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Finance\MarketplaceReceipt;
use App\Entities\Finance\MarketplaceReceiptDetail;
use App\Entities\Sale\SalesOrder;
use App\Exports\Finance\MarketplaceReceiptImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Finance\MarketplaceReceiptImport;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class MarketplaceReceiptController extends Controller
{
    public function json(Request $request, MarketplaceReceiptTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('marketplace receipt-manage')) {
            return abort(403);
        }

        $superuser = Auth::guard('superuser')->user();
        $data['superuser'] = $superuser;

        $data['coas'] = MasterRepo::coas_by_branch();

        return view('superuser.finance.marketplace_receipt.index', $data);
    }

    public function import_template()
    {
        $filename = 'marketplace-receipt-import-template.xlsx';
        return Excel::download(new MarketplaceReceiptImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new MarketplaceReceiptImport($request->store_name, $request->kode_transaksi);
            Excel::import($import, $request->import_file);

            if ($import->error) {
                return redirect()->back()->withErrors($import->error);
            }

            return redirect()->back()->with(['message' => 'Import success']);
        }
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            DB::beginTransaction();

            try {
                $superuser = Auth::guard('superuser')->user();
                $errors = [];

                $marketplace_receipts = MarketplaceReceipt::where('created_by', $superuser->id)->where('status', 0)->get();
                foreach ($marketplace_receipts as $marketplace_receipt) {
                    if (($marketplace_receipt->payment == null or $marketplace_receipt->payment == 0) && ($marketplace_receipt->cost_1 == null or $marketplace_receipt->cost_1 == 0) && ($marketplace_receipt->cost_2 == null or $marketplace_receipt->cost_2 == 0) && ($marketplace_receipt->cost_3 == null or $marketplace_receipt->cost_3 == 0)) {
                        $errors[] = array('INVOICE ' . $marketplace_receipt->code . ' HAS EMPTY PAYMENT AND COST : skipping');
                        continue;
                    }

                    $total_paid = $marketplace_receipt->payment + $marketplace_receipt->cost_1 + $marketplace_receipt->cost_2 + $marketplace_receipt->cost_3 + $marketplace_receipt->paid;
                    if ($marketplace_receipt->total < $total_paid) {
                        // $errors[] = array('INVOICE ' . $marketplace_receipt->code . ' EXCEED THE TOTAL PAYMENT : skipping');
                        // continue;
                    }
                    // ADD JOURNAL DEBET
                    if ($marketplace_receipt->payment) {
                        $journal = new Journal;
                        $journal->coa_id = $request->coa_payment;
                        $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                        $journal->debet = $marketplace_receipt->payment;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $marketplace_receipt->created_at;
                        $journal->save();
                    }
                    if ($marketplace_receipt->cost_1) {
                        $journal = new Journal;
                        $journal->coa_id = $request->coa_cost_1;
                        $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                        $journal->debet = $marketplace_receipt->cost_1;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $marketplace_receipt->created_at;
                        $journal->save();
                    }
                    if ($marketplace_receipt->cost_2) {
                        $journal = new Journal;
                        $journal->coa_id = $request->coa_cost_2;
                        $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                        $journal->debet = $marketplace_receipt->cost_2;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $marketplace_receipt->created_at;
                        $journal->save();
                    }
                    if ($marketplace_receipt->cost_3) {
                        $journal = new Journal;
                        $journal->coa_id = $request->coa_cost_3;
                        $journal->name = Journal::PREJOURNAL['MARKETPLACE_RECEIPT'] . $marketplace_receipt->code;
                        $journal->debet = $marketplace_receipt->cost_3;
                        $journal->status = Journal::STATUS['UNPOST'];
                        $journal->created_at = $marketplace_receipt->created_at;
                        $journal->save();
                    }
                    // ADD JOURNAL CREDIT
                    $journal = new Journal;
                    $journal->coa_id = $request->coa_total;
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
                    $mr_detail->credit_coa = $request->coa_total;
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
                    $coa = Coa::find($request->coa_total);
                    if($sales_order && $coa && $coa->kode_pelunasan) {
                        $sales_order->kode_pelunasan = $coa->kode_pelunasan . Carbon::parse($marketplace_receipt->created_at)->format('Y/m/d');
                        $sales_order->save();
                    }
                }

                DB::commit();

                if (count($errors) > 0) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $errors,
                    ];

                    $response['redirect_to'] = '#datatable';

                    return $this->response(200, $response);
                } else {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.finance.marketplace_receipt.index');

                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'Internal Server Error!',
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('marketplace receipt-delete')) {
                return abort(403);
            }

            $marketplace_receipts = MarketplaceReceipt::where('created_by', $id)->where('status', 0)->get();
            foreach ($marketplace_receipts as $marketplace_receipt) {
                if ($marketplace_receipt->paid == null) {
                    $marketplace_receipt->delete();
                }
            }
            $response['redirect_to'] = '#datatable';
            return $this->response(200, $response);
        }
    }

}
