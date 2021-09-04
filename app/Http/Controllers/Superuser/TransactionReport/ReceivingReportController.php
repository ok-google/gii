<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\ReceivingReportTable;
use App\Entities\Purchasing\PurchaseOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceivingReportController extends Controller
{
    public function json(Request $request, ReceivingReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('receiving report-manage')) {
            return abort(403);
        }

        $supplier = PurchaseOrder::select('supplier_id')->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())->where('status', PurchaseOrder::STATUS['ACC'])->groupBy('supplier_id')->get();

        $data['supplier'] = $supplier;

        $data['products'] = MasterRepo::products();

        return view('superuser.transaction_report.receiving_report.index', $data);
    }
}
