<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\StockValuationReportTable;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockValuationReportController extends Controller
{
    public function json(Request $request, StockValuationReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('stock valuation-manage')) {
            return abort(403);
        }

        $data['categories'] = MasterRepo::product_categories();

        return view('superuser.transaction_report.stock_valuation.index', $data);
    }
}
