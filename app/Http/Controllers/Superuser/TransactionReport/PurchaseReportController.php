<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\Entities\Account\Superuser;
use App\Entities\Master\Supplier;
use App\Entities\Finance\CBPaymentInvoiceDetail;
use App\Entities\Purchasing\PurchaseOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DomPDF;
use Validator;
use \Carbon\Carbon;

class PurchaseReportController extends Controller
{
    public function json(Request $request)
    {
        $data = [];
        $from_date = $request->from;
        $to_date = $request->to;

        if ($from_date && $to_date) {
            $purchase_order = PurchaseOrder::whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
                ->where('status', PurchaseOrder::STATUS['ACC'])
                ->whereBetween('created_at', [$from_date . " 00:00:00", $to_date . " 23:59:59"])
                ->get();

            foreach ($purchase_order as $item) {
                $detail_html = '<table class="table table-dark" style="margin-top: -10px;margin-bottom: -10px;">
                <thead class="thead-light">
                  <tr>
                    <th class="w-20">Date</th>
                    <th class="w-20">COA</th>
                    <th class="w-20">Account</th>
                    <th class="w-20">Debet</th>
                    <th class="w-20">Credit</th>
                  </tr>
                </thead>
                <tbody>';

                if (count($item->payment_history())) {
                    foreach ($item->payment_history() as $key => $history) {
                        $debet = $history['debet'] ? 'Rp. '.number_format($history['debet'], 2, ',', '.') : '';
                        $credit = $history['credit'] ? 'Rp. '.number_format($history['credit'], 2, ',', '.') : '';

                        $detail_html .= '<tr>
                        <td>' . Carbon::parse($history['date'])->format('d/m/Y') . '</td>
                        <td>' . $history['coa'] . '</td>
                        <td>' . $history['account'] . '</td>
                        <td>' . $debet . '</td>
                        <td>' . $credit . '</td>
                      </tr>';
                    }
                } else {
                    $detail_html .= '<tr>
                    <td colspan="5">Nothing payment history</td>
                  </tr>';
                }

                $detail_html .= '</tbody>
                </table>';

                $data['data'][] = [
                    '',
                    Carbon::parse($item->created_at)->format('d/m/Y H:i:s'),
                    $item->supplier->name,
                    $item->code,
                    $item->grand_total_idr,
                    $item->total_paid($item->id),
                    $item->grand_total_idr - $item->total_paid($item->id),
                    $detail_html,
                ];
            }

            if (empty($data['data'])) {
                $data['data'] = '';
            }
        } else {
            $data['data'] = '';
        }

        return $data;
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('purchase report-manage')) {
            return abort(403);
        }

        $superuser = Superuser::find(Auth::guard('superuser')->id());

        $supplier = PurchaseOrder::select('supplier_id')->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())->where('status', PurchaseOrder::STATUS['ACC'])->groupBy('supplier_id')->get();

        $data['supplier'] = $supplier;

        $purchase_order = PurchaseOrder::whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())->where('status', PurchaseOrder::STATUS['ACC'])->get();
        $data['purchase_order'] = $purchase_order;

        return view('superuser.transaction_report.purchase_report.index', $data);
    }

    public function pdf(Request $request)
    {
        if(!Auth::guard('superuser')->user()->can('purchase report-print')) {
            return abort(403);
        }

        $validator = Validator::make($request->all(), [
            'supplier' => 'required',
            'status' => 'required',
            'datesearch' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        $split = explode('-', str_replace(' ', '', $request->datesearch));
        $from_date = Carbon::createFromFormat('d/m/Y', $split[0])->format('Y-m-d');
        $to_date = Carbon::createFromFormat('d/m/Y', $split[1])->format('Y-m-d');

        $supplier = $request->supplier;
        $status = $request->status;

        $purchase_order = PurchaseOrder::addSelect([
            'total_paid' => CBPaymentInvoiceDetail::selectRaw('sum(paid) as total')->whereColumn('ppb_id', 'ppb.id')
                    ->groupBy('ppb_id'),
            ])
            ->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->where('status', PurchaseOrder::STATUS['ACC'])
            ->where(function ($query) use ($supplier) {
                if (isset($supplier) && $supplier != 'all') {
                    $query->where('supplier_id', $supplier);
                }
            })
            // ->where(function ($query) use ($status) {
            //     if (isset($status) && $status != 'all') {
            //         if($status == 'paid') {
            //             $query->whereColumn('grand_total_idr', '<=', 'total_paid');
            //         } else if($status == 'debt') {
            //             $query->whereColumn('grand_total_idr', '>', 'total_paid');
            //         }
            //     }
            // })
            ->whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
            ->get();

        $data['purchase_order'] = $purchase_order;

        if($supplier == 'all') {
            $supplier_text = 'All';
        } else {
            $supplier_text = Supplier::find($supplier)->name;
        }
        $data['supplier_text'] = $supplier_text;

        if($status == 'all') {
            $status_text = 'All';
        } elseif ($status == 'paid') {
            $status_text = 'Paid Off';
        } elseif ($status == 'debt') {
            $status_text = 'Debt';
        }
        $data['status_text'] = $status_text;

        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;

        $data['request'] = $request;

        $pdf = DomPDF::loadView('superuser.transaction_report.purchase_report.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream();
    }

}
