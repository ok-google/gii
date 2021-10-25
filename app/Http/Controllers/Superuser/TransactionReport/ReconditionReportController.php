<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\ReconditionReportTable;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use App\Entities\Inventory\Recondition;
use App\Entities\Inventory\ReconditionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReconditionReportController extends Controller
{
    public function json(Request $request, ReconditionReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('recondition-manage')) {
            return abort(403);
        }
        // dd('asd');
        $data['warehouse'] = MasterRepo::warehouses_by_category(1);

        // $data['products'] = MasterRepo::products();

        return view('superuser.transaction_report.recondition_report.index', $data);
    }
}
