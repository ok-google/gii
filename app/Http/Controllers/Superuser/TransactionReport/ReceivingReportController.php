<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\ReceivingReportTable;
use App\Entities\Master\Product;
use App\Entities\Master\Supplier;
use App\Entities\Purchasing\PurchaseOrder;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;
use SnappyPDF;
use Validator;

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

    public function export(Request $request)
    {
        if (!Auth::guard('superuser')->user()->can('receiving report-print')) {
            return abort(403);
        }

        $validator = Validator::make($request->all(), [
            'supplier' => 'required',
            'product' => 'required',
            'download_type' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        if ($request->download_type == 'excel') {
            return $this->excel($request);
        }

        if ($request->download_type == 'pdf') {
            return $this->pdf($request);
        }
    }

    private function excel(Request $request)
    {
        $datatable = new ReceivingReportTable();
        $model = $datatable->query($request);

        $filename = 'Receiving Report.xlsx';
        $header_style = (new StyleBuilder())->setFontSize(11)->setFontBold()->build();

        $rows_style = (new StyleBuilder())
            ->setFontSize(11)
            ->setShouldWrapText(false)
            ->build();

        return (new FastExcel($this->reportsGenerator($model)))->headerStyle($header_style)
            ->rowsStyle($rows_style)->download($filename);
    }

    private function reportsGenerator($model)
    {
        $datas = $model->cursor();
        foreach ($datas as $data) {
            yield [
                'Supplier' => $data->supplier,
                'PPB No' => $data->ppb,
                'PBM No' => $data->pbm,
                'Notes' => $data->description,
                'SKU' => $data->sku,
                'PPB Qty' => $data->ppb_qty,
                'RI Qty' => $data->ri_qty,
                'Incoming' => $data->incoming,
                'Colly Qty' => $data->colly_qty,
                'HPP' => number_format($data->hpp, 2, ',', ''),
            ];
        }
    }

    public function pdf(Request $request)
    {
        $datatable = new ReceivingReportTable();
        $model = $datatable->query($request);

        $lists = $model->cursor();

        $data = [
            'lists' => $lists,
            'supplier' => $request->supplier == 'all' ? 'All' : Supplier::where('id', $request->supplier)->value('name'),
            'sku' => $request->product == 'all' ? 'All' : Product::where('id', $request->product)->value('code'),
        ];

        $pdf = SnappyPDF::loadView('superuser.transaction_report.receiving_report.pdf', $data);
        $pdf->setPaper('a3', 'landscape');

        $filename = 'Receiving Report.pdf';

        return $pdf->download($filename);
    }
}
