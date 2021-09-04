<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\SalesReportTable;
use App\Entities\Account\Superuser;
use App\Entities\Sale\SalesOrder;
use App\Http\Controllers\Controller;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;
use SnappyPDF;
use Validator;
use \Carbon\Carbon;

class SalesReportController extends Controller
{
    public function json(Request $request, SalesReportTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('sales report-manage')) {
            return abort(403);
        }

        return view('superuser.transaction_report.sales_report.index');
    }

    // public static function getSqlWithBindings($query)
    // {
    //     return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
    //         return is_numeric($binding) ? $binding : "'{$binding}'";
    //     })->toArray());
    // }

    private function reportsGenerator($model)
    {
        $datas = $model->cursor();
        foreach ($datas as $data) {
            yield [
                'Create Date' => Carbon::parse($data->create_date)->format('d/m/Y H:i'),
                'Order Date' => $data->order_date ? Carbon::parse($data->order_date)->format('d/m/Y H:i') : '-',
                'MP Receipt Code' => $data->kode_pelunasan,
                'Marketplace' => $data->marketplace_order(),
                'Store' => $data->store_name,
                'Customer' => $data->customer_name,
                'Invoice No' => $data->code,
                'Receivable' => $data->grand_total,
                'Paid' => $data->total_paid,
                'Cost' => $data->total_cost,
                'Unpaid' => $data->unpaid,
                'Retur' => $data->retur,
            ];
        }

        yield [
            'Create Date' => '',
            'Order Date' => '',
            'MP Receipt Code' => '',
            'Marketplace' => '',
            'Store' => '',
            'Customer' => '',
            'Invoice No' => 'Total',
            'Receivable' => collect($datas)->sum('grand_total'),
            'Paid' => collect($datas)->sum('total_paid'),
            'Cost' => collect($datas)->sum('total_cost'),
            'Unpaid' => collect($datas)->sum('unpaid'),
            'Retur' => collect($datas)->sum('retur'),
        ];
    }

    private function excel(Request $request)
    {
        $datatable = new SalesReportTable();
        $model = $datatable->query($request);

        // $list = \DB::select($model->toSql(), $model->getBindings());

        $filename = 'SR-' . Carbon::parse($request->start_date)->format('dmy') . '-' . Carbon::parse($request->end_date)->format('dmy') . '.xlsx';
        $header_style = (new StyleBuilder())->setFontSize(11)->setFontBold()->build();

        $rows_style = (new StyleBuilder())
            ->setFontSize(11)
            ->build();

        return (new FastExcel($this->reportsGenerator($model)))->headerStyle($header_style)
            ->rowsStyle($rows_style)->download($filename);
    }

    public function export(Request $request)
    {
        if (!Auth::guard('superuser')->user()->can('sales report-print')) {
            return abort(403);
        }

        $validator = Validator::make($request->all(), [
            'marketplace' => 'required',
            'status' => 'required',
            'datesearch' => 'required',
            'download_type' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        $split = explode('-', str_replace(' ', '', $request->datesearch));
        $from_date = Carbon::createFromFormat('d/m/Y', $split[0])->format('Y-m-d');
        $to_date = Carbon::createFromFormat('d/m/Y', $split[1])->format('Y-m-d');

        // ADD request date to use in datatable query
        $request->request->add(['start_date' => $from_date, 'end_date' => $to_date]);
        if ($request->download_type == 'excel') {
            return $this->excel($request);
        }

        if ($request->download_type == 'pdf') {
            return $this->pdf($request);
        }
    }

    public function pdf(Request $request)
    {
        $datatable = new SalesReportTable();
        $query = $datatable->query($request);
        $sales_order = $query->cursor();
        $data['sales_order'] = $sales_order;

        $marketplace = $request->marketplace;
        $status = $request->status;

        if ($marketplace == 'all') {
            $marketplace_text = 'All';
        } else {
            $marketplace_text = array_search($marketplace, SalesOrder::MARKETPLACE_ORDER);
        }

        $data['marketplace_text'] = $marketplace_text;

        if ($status == 'all') {
            $status_text = 'All';
        } elseif ($status == 'paid') {
            $status_text = 'Paid';
        } elseif ($status == 'debt') {
            $status_text = 'Unpaid';
        }
        $data['status_text'] = $status_text;

        $data['from_date'] = $request->start_date;
        $data['to_date'] = $request->end_date;

        $data['request'] = $request;

        // $pdf = DomPDF::loadView('superuser.transaction_report.sales_report.pdf', $data);
        $pdf = SnappyPDF::loadView('superuser.transaction_report.sales_report.pdf', $data);
        $pdf->setPaper('a3', 'landscape');

        $filename = 'SR-' . Carbon::parse($request->start_date)->isoFormat('DDMMYY') . '/' . Carbon::parse($request->end_date)->isoFormat('DDMMYY') . '.pdf';

        return $pdf->download($filename);
    }
}
