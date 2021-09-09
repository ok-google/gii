<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\StockValuationReportTable;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;
use SnappyPDF;
use Validator;
use \Carbon\Carbon;

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
        $data['warehouses'] = MasterRepo::warehouses_by_branch();

        return view('superuser.transaction_report.stock_valuation.index', $data);
    }

    public function export(Request $request)
    {
        if (!Auth::guard('superuser')->user()->can('stock valuation-print')) {
            return abort(403);
        }

        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'warehouse' => 'required',
            'datesearch' => 'required',
            'download_type' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        $split = explode('-', str_replace(' ', '', $request->datesearch));
        $from_date = Carbon::createFromFormat('d/m/Y', $split[0])->format('Y-m-d');
        $to_date = Carbon::createFromFormat('d/m/Y', $split[1])->format('Y-m-d');

        // change array to string
        $category_string = implode(',', $request->category);
        $warehouse_string = implode(',', $request->warehouse);

        // ADD request date to use in datatable query
        $request->request->add(['start_date' => $from_date, 'end_date' => $to_date, 'category' => $category_string, 'warehouse' => $warehouse_string]);
        if ($request->download_type == 'excel') {
            return $this->excel($request);
        }

        if ($request->download_type == 'pdf') {
            return $this->pdf($request);
        }
    }

    public static function getSqlWithBindings($query)
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }

    private function excel(Request $request)
    {
        $datatable = new StockValuationReportTable();
        $model = $datatable->query($request);

        // return $this->getSqlWithBindings($model);
        // $list = \DB::select($model->toSql(), $model->getBindings());

        $filename = 'SV-' . Carbon::parse($request->start_date)->format('dmy') . '-' . Carbon::parse($request->end_date)->format('dmy') . '.xlsx';
        $header_style = (new StyleBuilder())->setFontSize(11)->setFontBold()->build();

        $rows_style = (new StyleBuilder())
            ->setFontSize(11)
            ->build();

        return (new FastExcel($this->reportsGenerator($model)))->headerStyle($header_style)
            ->rowsStyle($rows_style)->download($filename);
    }

    private function reportsGenerator($model)
    {
        $datas = $model->cursor();
        foreach ($datas as $data) {
            yield [
                'SKU' => $data->sku,
                'Product' => $data->name,
                'Opening Qty' => $data->opening_qty,
                'Opening Balance' => number_format($data->opening_balance, 2, ',', ''),
                'Purchase Qty' => $data->purchase_qty,
                'Total Purchase' => number_format($data->total_purchase, 2, ',', ''),
                'Receiving Qty' => $data->receiving_qty,
                'Total Receiving' => number_format($data->total_receiving, 2, ',', ''),
                'Sale Qty' => $data->sale_qty,
                'Total Sale' => number_format($data->total_sale, 2, ',', ''),
                'Return Qty' => $data->return_qty,
                'Total Return' => number_format($data->total_return, 2, ',', ''),
                'Closing Qty'   => $data->opening_qty + $data->receiving_qty - $data->sale_qty + $data->return_qty,
                'Closing Balance'   => number_format($data->opening_balance + $data->total_receiving - $data->total_sale + $data->total_return, 2, ',', '')
            ];
        }
    }

    public function pdf(Request $request)
    {
        $datatable = new StockValuationReportTable();
        $query = $datatable->query($request);
        $lists = $query->cursor();

        $data = [
            'lists' => $lists,
            'category' => $request->category == 'all' ? 'All' : implode(',', ProductCategory::whereIn('id', explode(',', $request->category))->orderBy('name')->pluck('name')->toArray()),
            'warehouse' => $request->warehouse == 'all' ? 'All' : implode(',', Warehouse::whereIn('id', explode(',', $request->warehouse))->orderBy('name')->pluck('name')->toArray()),
            'from_date' => $request->start_date,
            'to_date'   => $request->end_date
        ];

        $pdf = SnappyPDF::loadView('superuser.transaction_report.stock_valuation.pdf', $data);
        $pdf->setPaper('a3', 'landscape');

        $filename = 'SV-' . Carbon::parse($request->start_date)->isoFormat('DDMMYY') . '/' . Carbon::parse($request->end_date)->isoFormat('DDMMYY') . '.pdf';

        return $pdf->download($filename);
    }
}
