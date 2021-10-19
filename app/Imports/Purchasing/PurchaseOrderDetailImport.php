<?php

namespace App\Imports\Purchasing;

use App\Entities\Purchasing\PurchaseOrder;
use App\Entities\Purchasing\PurchaseOrderDetail;
use App\Entities\Master\Product;
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
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use DB;
// use Carbon\Carbon;

HeadingRowFormatter::default('none');

class PurchaseOrderDetailImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;

    public function  __construct($purchase_order_id)
    {
        $this->purchase_order_id = $purchase_order_id;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        $collect_error = [];

        $purchase_order = PurchaseOrder::find($this->purchase_order_id);

        if($purchase_order == null) {
            $collect_error = array('Something went wrong, please reload page!');
        } else {
            foreach ($rows as $row) {
                $product = Product::where('code', $row['sku'])->first();
                if($product == null) {
                    $collect_error = array('PRODUCT SKU '.$row['sku'].' NOT FOUND : all import aborted!');
                    break;
                }

                $quantity = $row['quantity'] ?? 0;
                $unit_price = $row['unit_price'] ?? 0;
                $local_freight_cost = $row['local_freight_cost'] ?? 0;
                $komisi = $row['komisi'] ?? 0;

                $total_price_rmb = ($quantity * $unit_price) + $local_freight_cost + $komisi;
                
                $kurs = $row['kurs'] ?? 0;
                

                // SET TAX
                $total_price_before_tax = $total_price_rmb * $kurs;

                $tax = 0;
                if($purchase_order->tax > 0) {
                    $tax = $total_price_before_tax * $purchase_order->tax / 100;
                }
                $total_price_after_tax = $total_price_before_tax + $tax;
                $total_price_idr = $total_price_after_tax;
                $unit_price_idr = $total_price_after_tax / $quantity;
                
                $order_date = $row['order_date'] ? $this->transformDate($row['order_date']) : 'NULL';
                
                $no_container = $row['no_container'];
                $qty_container = $row['qty_container'];
                $colly_qty = $row['colly_qty'];

                $purchase_order_detail = new PurchaseOrderDetail;
                $purchase_order_detail->ppb_id = $purchase_order->id;
                $purchase_order_detail->product_id = $product->id;
                $purchase_order_detail->quantity = $quantity;
                $purchase_order_detail->unit_price = $unit_price;
                $purchase_order_detail->local_freight_cost = $local_freight_cost;
                $purchase_order_detail->komisi = $komisi;
                $purchase_order_detail->total_price_rmb = $total_price_rmb;
                $purchase_order_detail->kurs = $kurs;
                $purchase_order_detail->total_tax = $tax;
                $purchase_order_detail->total_price_idr = $total_price_after_tax;
                $purchase_order_detail->unit_price_idr = $unit_price_idr;
                $purchase_order_detail->order_date = $order_date;
                $purchase_order_detail->no_container = $no_container;
                $purchase_order_detail->qty_container = $qty_container;
                $purchase_order_detail->colly_qty = $colly_qty;

                if($purchase_order_detail->save()) {
                } else {
                    $collect_error = array('Something went wrong, please reload page!');
                    break;
                }
                
            }
        }

        if($collect_error) {
            $this->error = $collect_error;
            DB::rollBack();
        } 
        
        DB::commit();
        
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
