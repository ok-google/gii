<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\ConversionReportTable;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use App\Entities\Inventory\ProductConversion;
use App\Entities\Inventory\ProductConversionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversionReportController extends Controller
{
    public function json(Request $request, ConversionReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('recondition-manage')) {
            return abort(403);
        }
        // dd('asd');
        $data['warehouse'] = MasterRepo::warehouses_by_category(2);

        // $data['products'] = MasterRepo::products();

        return view('superuser.transaction_report.conversion_report.index', $data);
    }
}
