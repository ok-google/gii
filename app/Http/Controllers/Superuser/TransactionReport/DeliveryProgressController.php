<?php

namespace App\Http\Controllers\Superuser\TransactionReport;

use App\DataTables\TransactionReport\DeliveryProgressTable;
use App\Entities\Account\Superuser;
use App\Entities\Sale\SalesOrder;
use App\Http\Controllers\Controller;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;
use SnappyPDF;
use Validator;
use \Carbon\Carbon;

class DeliveryProgressController extends Controller
{
    public function json(Request $request, DeliveryProgressTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('delivery progress-manage')) {
            return abort(403);
        }

        $data['stores'] = SalesOrder::select('store_name')->where('status', SalesOrder::STATUS['ACC'])
            ->whereIn('warehouse_id', MasterRepo::warehouses_by_branch()->pluck('id')->toArray())
            ->groupBy('store_name')
            ->get();

        return view('superuser.transaction_report.delivery_progress.index', $data);
    }

    public function export(Request $request)
    {
        if (!Auth::guard('superuser')->user()->can('delivery progress-print')) {
            return abort(403);
        }

        $validator = Validator::make($request->all(), [
            'marketplace' => 'required',
            'status' => 'required',
            'store' => 'required',
            'datesearch' => 'required',
            'download_type' => 'required',
        ]);

        if ($validator->fails()) {
            abort(404);
        }

        $split = explode('-', str_replace(' ', '', $request->datesearch));
        $from_date = Carbon::createFromFormat('d/m/Y', $split[0])->format('Y-m-d');
        $to_date = Carbon::createFromFormat('d/m/Y', $split[1])->format('Y-m-d');

        // change array store to string
        $store_string = implode(',', $request->store);

        // ADD request date to use in datatable query
        $request->request->add(['start_date' => $from_date, 'end_date' => $to_date, 'store' => $store_string]);

        if ($request->download_type == 'excel') {
            return $this->excel($request);
        }

        if ($request->download_type == 'pdf') {
            return $this->pdf($request);
        }
    }

    private function excel(Request $request)
    {
        $datatable = new DeliveryProgressTable();
        $model = $datatable->query($request);
        $model->orderBy('scan_by', 'ASC');

        // $list = \DB::select($model->toSql(), $model->getBindings());

        $filename = 'DP-' . Carbon::parse($request->start_date)->format('dmy') . '-' . Carbon::parse($request->end_date)->format('dmy') . '.xlsx';
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
                'Create Date' => Carbon::parse($data->created_at)->format('d/m/Y H:i'),
                'Shop' => $data->store_name,
                'Invoice No' => $data->code,
                'No Pack' => $data->no_pack ? $data->no_pack : '-',
                'AWB' => $data->resi,
                'Item QTY' => $data->quantity,
                'Order Date' => $data->order_date ? Carbon::parse($data->order_date)->format('d/m/Y H:i') : '-',
                'Approved Date' => $data->approved_date ? Carbon::parse($data->approved_date)->format('d/m/Y H:i') : '-',
                'Packing Date' => $data->packing_date ? Carbon::parse($data->packing_date)->format('d/m/Y H:i') : '-',
                'DO Validation' => $data->do_validation_date ? Carbon::parse($data->do_validation_date)->format('d/m/Y H:i') : '-',
                'Return' => $data->return_date ? Carbon::parse($data->return_date)->format('d/m/Y H:i') : '-',
                'User' => $data->scan_by,
            ];
        }
    }

    /**
     *
     * @param Request $request
     * @return void
     */
    public function pdf(Request $request)
    {
        $datatable = new DeliveryProgressTable();
        $model = $datatable->query($request);
        $model->orderBy('scan_by', 'ASC');

        $sales_order = $model->cursor();

        $data = [
            'sales_order' => $sales_order,
            'marketplace_text' => $request->marketplace == 'all' ? 'All' : array_search($request->marketplace, SalesOrder::MARKETPLACE_ORDER),
            'store_text' => $request->store == 'all' ? 'All' : $request->store,
            'status' => strtoupper($request->status),
            'from_date' => $request->start_date,
            'to_date' => $request->end_date
        ];

        $pdf = SnappyPDF::loadView('superuser.transaction_report.delivery_progress.pdf', $data);
        $pdf->setPaper('a3', 'landscape');

        $filename = 'DP-' . Carbon::parse($request->start_date)->isoFormat('DDMMYY') . '/' . Carbon::parse($request->end_date)->isoFormat('DDMMYY') . '.pdf';

        return $pdf->download($filename);
    }
}
