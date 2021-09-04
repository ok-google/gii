<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\HppReportTable;
use App\Entities\Sale\SalesOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HppReportController extends Controller
{
    public function json(Request $request, HppReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('hpp report-manage')) {
            return abort(403);
        }

        $data['warehouses'] = MasterRepo::warehouses_by_category(2);

        $data['stores'] = SalesOrder::select('store_name')->where('status', SalesOrder::STATUS['ACC'])
            ->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->groupBy('store_name')
            ->get();

        $data['products'] = MasterRepo::products();

        return view('superuser.transaction_report.hpp_report.index', $data);
    }
}
