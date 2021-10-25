<?php

namespace App\Imports\Sale;

use App\Entities\Sale\SalesOrder;
use App\Entities\Master\Warehouse;
use App\Entities\Master\Store;
use App\Entities\Sale\SalesOrderDetail;
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

HeadingRowFormatter::default('none');

class SalesOrderImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function  __construct($warehouse, $marketplace, $store_name, $store_phone)
    {
        $this->warehouse = $warehouse;
        $this->marketplace = $marketplace;
        $this->store_name = $store_name;
        $this->store_phone = $store_phone;
    }

    public function collection(Collection $rows)
    {
        // dd("asssss");
        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];
            $collect_duplicate = [];

            
            // dd($rows[0]);
            $data = [];
            foreach ($rows as $row) {
                if($row['SKU'] != ""){
                    if ($row['Waktu Pembayaran'] != '-') {
                        if (array_key_exists($row['Invoice'], $data)) {
                            if (array_key_exists($row['SKU'], $data[$row['Invoice']]['product'])) {
                                $qty = $data[$row['Invoice']]['product'][$row['SKU']]['qty'];
                                $data[$row['Invoice']]['product'][$row['SKU']]['qty'] = $qty + $row['QTY'];
                            } else {
                                $data[$row['Invoice']]['product'][$row['SKU']] = [
                                    'price' => preg_replace('/\D/', '', $row['Satuan Harga']),
                                    'qty'   => $row['QTY']
                                ];
                            }
                        } else {
                            $data[$row['Invoice']]['product'][$row['SKU']] = [
                                'price' => preg_replace('/\D/', '', $row['Satuan Harga']),
                                'qty'   => $row['QTY']
                            ];

                            $data[$row['Invoice']]['info'] = [
                                'marketplace'           => $row['Marketplace'],
                                'warehouse'             => trim($row['Warehouse']),
                                'store'                 => $row['Store'],
                                'customer_marketplace'  => $row['Nama Penerima'],
                                'address_marketplace'   => $row['Alamat Penerima'],
                                'ekspedisi_marketplace' => $row['Ekspedisi'],
                                'no_hp_marketplace'     => $row['No. Telp'],
                                'resi'                  => $row['Resi / AWB'],
                                'batas_kirim'           => $row['Batas Waktu Pengiriman'].":00",
                                'description'           => ($row['Catatan dari Pembeli']??''),
                                'weight'                => $row['total Berat Barang'],
                                'discount'              => preg_replace('/\D/', '', $row['Voucher']),
                                'order_date'            => $row['Waktu Pembayaran'].":00",
                            ];
                        }
                    } else {
                        $collect_error[] = $row['Invoice'] . ' : "Waktu Pembayaran" is empty';
                    }
                }
            }

            // dd($data);
            if ($data) {
                foreach ($data as $key => $value) {

                    $sales_order = SalesOrder::where('code', $key)->first();

                    if ($sales_order == '') {
                        // VERIFIED DATA
                        if ($value['info']['resi'] == null) {
                            $collect_error[] = $key . ' : "Resi / AWB" is empty';
                            continue;
                        }

                        if ($value['info']['warehouse'] == null) {
                            $collect_error[] = $key . ' : "Warehouse" is empty';
                            continue;
                        }else{
                            $warehouse_id = Warehouse::where("name", $value['info']['warehouse'])->first();
                            // if($key == "ABC124"){ dd($warehouse_id); }
                            if ($warehouse_id == null) {
                                $collect_error[] = $key . ' : "Warehouse" not found';
                                continue;
                            }
                        }

                        if ($value['info']['store'] == null) {
                            $collect_error[] = $key . ' : "Store" is empty';
                            continue;
                        }else{
                            $store_id = Store::where("code", $value['info']['store'])->first();
                            // if($key == "ABC124"){ dd($warehouse_id); }
                            if ($store_id == null) {
                                $collect_error[] = $key . ' : "Store" not found';
                                continue;
                            }
                        }


                        if ($value['product']) {
                            $found_error_product = false;
                            foreach ($value['product'] as $key_product => $value_product) {
                                $cek_product = Product::where('code', $key_product)->first();
                                if ($cek_product == null) {
                                    $collect_error[] = $key . ' : SKU ' . $key_product . ' not found in database';
                                    $found_error_product = true;
                                    continue;
                                }
                            }
                            if ($found_error_product) {
                                continue;
                            }
                        } else {
                            $collect_error[] = $key . ' : No product column';
                            continue;
                        }

                        $sales_order = new SalesOrder;
                        $sales_order->code = $key;
                        $sales_order->marketplace_order = SalesOrder::MARKETPLACE_ORDER[$value['info']["marketplace"]];
                        $sales_order->warehouse_id = $warehouse_id->id;
                        $sales_order->store_name = $store_id->code;
                        // $sales_order->store_phone = $this->store_phone;
                        $sales_order->customer_marketplace = $value['info']['customer_marketplace'];
                        $sales_order->address_marketplace = $value['info']['address_marketplace'];
                        $sales_order->ekspedisi_marketplace = $value['info']['ekspedisi_marketplace'];
                        $sales_order->no_hp_marketplace = $value['info']['no_hp_marketplace'];
                        $sales_order->resi = $value['info']['resi'];
                        $sales_order->batas_kirim = $value['info']['batas_kirim'];
                        $sales_order->description = $value['info']['description'];
                        $sales_order->weight = $value['info']['weight'];
                        $sales_order->discount = $value['info']['discount'];
                        $sales_order->order_date = $value['info']['order_date'];

                        $sales_order->status = SalesOrder::STATUS['ACTIVE'];
                        $sales_order->save();

                        if ($value['product']) {
                            foreach ($value['product'] as $key_product => $value_product) {
                                $cek_product = Product::where('code', $key_product)->first();

                                $sales_order_detail = new SalesOrderDetail;
                                $sales_order_detail->sales_order_id = $sales_order->id;
                                $sales_order_detail->product_id = $cek_product->id;
                                $sales_order_detail->quantity = $value_product['qty'];
                                $sales_order_detail->price = $value_product['price'];
                                $sales_order_detail->total = $value_product['price'] * $value_product['qty'];
                                $sales_order_detail->save();
                            }
                        }

                        $subtotal = SalesOrderDetail::where('sales_order_id', $sales_order->id)->sum('total');

                        $sales_order_update = SalesOrder::find($sales_order->id);

                        $sales_order_update->total = $subtotal;
                        $sales_order_update->grand_total = $subtotal - $sales_order_update->discount;
                        $sales_order_update->save();

                        $collect_success[] = $key;
                    } else {
                        $collect_error[] = $key . ' : Duplicate order';
                    }
                }
            }

            if (!$collect_success) {
                $collect_success[] = 'No successful import.';
            }

            if (!$collect_error) {
                $collect_error[] = 'No failed import.';
            }

            $this->error = $collect_error;
            $this->success = $collect_success;

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
