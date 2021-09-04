<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\GudangUtamaReportTable;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GudangUtamaReportController extends Controller
{
    public function json(Request $request, GudangUtamaReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('gudang utama-manage')) {
            return abort(403);
        }

        $data['warehouse'] = MasterRepo::warehouses_by_category(1);

        $data['products'] = MasterRepo::products();

        return view('superuser.transaction_report.gudang_utama_report.index', $data);
    }
}
