<?php

namespace App\Imports\Finance;

use App\Entities\Sale\SalesOrder;
use App\Entities\Finance\MarketplaceReceipt;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use DB;
use \Carbon\Carbon;

HeadingRowFormatter::default('none');

class MarketplaceReceiptImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;

    public function  __construct($store_name, $kode_transaksi)
    {
        $this->store_name = $store_name;
        $this->kode_transaksi = $kode_transaksi;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        $collect_error = [];

        try {
            $superuser = Auth::guard('superuser')->user();

            foreach ($rows as $row) {
                if(empty($row['invoice'])) {
                    $collect_error[] = array('Empty invoce : skipping import');
                    continue;
                }
                
                $sales_order = SalesOrder::where('code', $row['invoice'])->where('status', SalesOrder::STATUS['ACC'])->first();
                if($sales_order == null) {
                    $collect_error[] = array('INVOICE '.$row['invoice'].' NOT FOUND : skipping import');
                    continue;
                } else if($sales_order->marketplace_order == 0) {
                    $collect_error[] = array('INVOICE '.$row['invoice'].' NOT MARKETPLACE ORDER : skipping import');
                    continue;
                }

                $find_mr = MarketplaceReceipt::where('code', $row['invoice'])->first();
                if($find_mr) {
                    if($find_mr->status == 1) {
                        $collect_error[] = array('INVOICE '.$row['invoice'].' HAS BEEN PAID OFF : skipping import');
                        continue;
                    } else if($find_mr->created_by != $superuser->id) {
                        $collect_error[] = array('INVOICE '.$row['invoice'].' HAS BEEN IMPORTED BY OTHER USER : skipping import');
                        continue;
                    } else {
                        if($row['tgl_pencairan']) {
                            $find_mr->created_at = $this->transformDate($row['tgl_pencairan']);
                        }
                        $find_mr->payment = $row['payment'];
                        $find_mr->cost_1 = $row['cost_1'];
                        $find_mr->cost_2 = $row['cost_2'];
                        $find_mr->cost_3 = $row['cost_3'];
                        $find_mr->save();
                        continue;
                    }
                }

                $marketplace_receipt = new MarketplaceReceipt;
                $marketplace_receipt->code = $row['invoice'];
                $marketplace_receipt->store_name = $this->store_name;
                $marketplace_receipt->kode_transaksi = $this->kode_transaksi;
                $marketplace_receipt->total = $sales_order->grand_total;
                $marketplace_receipt->payment = $row['payment'];
                $marketplace_receipt->cost_1 = $row['cost_1'];
                $marketplace_receipt->cost_2 = $row['cost_2'];
                $marketplace_receipt->cost_3 = $row['cost_3'];
                $marketplace_receipt->status = 0;
                $marketplace_receipt->created_by = $superuser->id;
                if($row['tgl_pencairan']) {
                    $marketplace_receipt->created_at = $this->transformDate($row['tgl_pencairan']);
                }
                $marketplace_receipt->save();
            }
            if($collect_error) {
                $this->error = $collect_error;
            } 
            DB::commit();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            DB::rollBack();
        }
        
    }

    public function startRow(): int
    {
        return 2;
    }

    public function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }

}
