<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\OrderDetailReportTable;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use App\Entities\Inventory\SalesOrder;
use App\Entities\Inventory\SalesOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderDetailReportController extends Controller
{
    public function json(Request $request, OrderDetailReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('recondition-manage')) {
            return abort(403);
        }

        // $data["warehouse"] = [];
        // dd('asd');
        $data['products'] = MasterRepo::products();

        // $data['products'] = MasterRepo::products();

        return view('superuser.transaction_report.order_detail_report.index', $data);
    }
}
