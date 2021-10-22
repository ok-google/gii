<?php

namespace App\Imports\Sale;

use App\Entities\Sale\SalesOrder;
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
        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];
            $collect_duplicate = [];

            if (SalesOrder::MARKETPLACE_ORDER[$this->marketplace] == SalesOrder::MARKETPLACE_ORDER['Shopee']) {

                $data = [];
                foreach ($rows as $row) {
                    if ($row['Waktu Pembayaran Dilakukan'] != '-') {
                        if (array_key_exists($row['No. Pesanan'], $data)) {
                            if (array_key_exists($row['Nomor Referensi SKU'], $data[$row['No. Pesanan']]['product'])) {
                                $qty = $data[$row['No. Pesanan']]['product'][$row['Nomor Referensi SKU']]['qty'];
                                $data[$row['No. Pesanan']]['product'][$row['Nomor Referensi SKU']]['qty'] = $qty + $row['Jumlah'];
                            } else {
                                $data[$row['No. Pesanan']]['product'][$row['Nomor Referensi SKU']] = [
                                    'price' => preg_replace('/\D/', '', $row['Harga Setelah Diskon']),
                                    'qty'   => $row['Jumlah']
                                ];
                            }
                        } else {
                            $data[$row['No. Pesanan']]['product'][$row['Nomor Referensi SKU']] = [
                                'price' => preg_replace('/\D/', '', $row['Harga Setelah Diskon']),
                                'qty'   => $row['Jumlah']
                            ];

                            $data[$row['No. Pesanan']]['info'] = [
                                'customer_marketplace'  => $row['Nama Penerima'],
                                'address_marketplace'   => $row['Alamat Pengiriman'],
                                'ekspedisi_marketplace' => $row['Opsi Pengiriman'],
                                'no_hp_marketplace'     => $row['No. Telepon'],
                                'resi'                  => $row['No. Resi'],
                                'batas_kirim'           => $row['Pesanan Harus Dikirimkan Sebelum (Menghindari keterlambatan)'],
                                'description'           => $row['Catatan dari Pembeli'],
                                'weight'                => $row['Total Berat'],
                                'discount'              => preg_replace('/\D/', '', $row['Voucher Ditanggung Penjual']),
                                'order_date'            => $row['Waktu Pembayaran Dilakukan'],
                            ];
                        }
                    } else {
                        $collect_error[] = $row['No. Pesanan'] . ' : "Waktu Pembayaran Dilakukan" is empty';
                    }
                }

                if ($data) {
                    foreach ($data as $key => $value) {

                        $sales_order = SalesOrder::where('code', $key)->first();

                        if ($sales_order == '') {
                            // VERIFIED DATA
                            if ($value['info']['resi'] == null) {
                                $collect_error[] = $key . ' : "No. Resi" is empty';
                                continue;
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
                            $sales_order->marketplace_order = SalesOrder::MARKETPLACE_ORDER[$this->marketplace];
                            $sales_order->warehouse_id = $this->warehouse;
                            $sales_order->store_name = $this->store_name;
                            $sales_order->store_phone = $this->store_phone;
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
            } else if (SalesOrder::MARKETPLACE_ORDER[$this->marketplace] == SalesOrder::MARKETPLACE_ORDER['Tokopedia']) {
                $status_pembayaran = 1;
                $no_pesanan = '';
                $collect_soid = [];
                $duplicate = 0;
                foreach ($rows as $row) {
                    if ($row['Payment Date'] == '') {
                        $status_pembayaran = 0;
                    } else {
                        if ($row['Count'] == '' && $status_pembayaran == 0) {
                            $status_pembayaran = 0;
                        } else if ($row['Count']) {

                            $sales_order = SalesOrder::where('code', $row['Invoice'])->first();
                            if ($sales_order == '') {
                                $cek_product = Product::where('code', $row['Stock Keeping Unit (SKU)'])->first();
                                if ($cek_product == null) {
                                    $collect_error[] = $row['Invoice'] . ' : SKU ' . $row['Stock Keeping Unit (SKU)'] . ' not found in database';
                                    continue;
                                }

                                if ($row['AWB'] == null) {
                                    $collect_error[] = $row['Invoice'] . ' : "No. Resi" is empty';
                                    continue;
                                }

                                $sales_order = new SalesOrder;
                                $sales_order->code = $row['Invoice'];
                                $sales_order->marketplace_order = SalesOrder::MARKETPLACE_ORDER[$this->marketplace];
                                $sales_order->warehouse_id = $this->warehouse;
                                $sales_order->store_name = $this->store_name;
                                $sales_order->store_phone = $this->store_phone;
                                $sales_order->customer_marketplace = $row['Recipient'];
                                $sales_order->address_marketplace = $row['Recipient Address'];
                                $sales_order->ekspedisi_marketplace = $row['Courier'];
                                $sales_order->resi = $row['AWB'];
                                $sales_order->description = $row['Notes'];
                                $sales_order->no_hp_marketplace = $row['Recipient Number'];
                                $sales_order->order_date = $row['Payment Date'] ? date('Y-m-d H:i:s', strtotime($row['Payment Date'])) : null;

                                $sales_order->total = $row['Quantity'] * preg_replace('/\D/', '', $row['Price (Rp.)']);
                                $sales_order->grand_total = $row['Quantity'] * preg_replace('/\D/', '', $row['Price (Rp.)']);

                                $sales_order->status = SalesOrder::STATUS['ACTIVE'];
                                $sales_order->save();

                                $sales_order_detail = new SalesOrderDetail;
                                $sales_order_detail->sales_order_id = $sales_order->id;
                                $sales_order_detail->product_id = $cek_product->id;
                                $sales_order_detail->quantity = $row['Quantity'];
                                $sales_order_detail->price = preg_replace('/\D/', '', $row['Price (Rp.)']);
                                $sales_order_detail->total = $row['Quantity'] * preg_replace('/\D/', '', $row['Price (Rp.)']);
                                $sales_order_detail->save();

                                $collect_soid[] = $sales_order->id;
                                $duplicate = 0;
                            } else {
                                $duplicate = 1;
                                $collect_error[] = $sales_order->code . ' : Duplicate order';
                            }

                            $no_pesanan = $sales_order->id;
                            $status_pembayaran = 1;
                        } else if ($row['Count'] == '' && $status_pembayaran == 1) {
                            if ($duplicate == 0) {
                                $cek_product = Product::where('code', $row['Stock Keeping Unit (SKU)'])->first();
                                if ($cek_product == null) {
                                    $collect_error[] = $row['Invoice'] . ' : SKU ' . $row['Stock Keeping Unit (SKU)'] . ' not found in database';

                                    // DELETE ORDER IF PRODUCT NOT FOUND
                                    $delete_order = SalesOrder::where('code', $row['Invoice'])->first();
                                    if ($delete_order) {
                                        $delete_order->sales_order_details()->delete();
                                        $delete_order->forceDelete();
                                    }

                                    continue;
                                }

                                $sales_order_detail = new SalesOrderDetail;
                                $sales_order_detail->sales_order_id = $no_pesanan;
                                $sales_order_detail->product_id = $cek_product->id;
                                $sales_order_detail->quantity = $row['Quantity'];
                                $sales_order_detail->price = preg_replace('/\D/', '', $row['Price (Rp.)']);
                                $sales_order_detail->total = $row['Quantity'] * preg_replace('/\D/', '', $row['Price (Rp.)']);
                                $sales_order_detail->save();
                            }

                            $status_pembayaran = 1;
                        }
                    }
                }

                if ($collect_soid) {
                    foreach ($collect_soid as $key => $value) {
                        $sales_order = SalesOrder::find($value);
                        if (!$sales_order) {
                            continue;
                        }

                        $collect_success[] = $sales_order->code;

                        $subtotal = SalesOrderDetail::where('sales_order_id', $value)->sum('total');

                        $sales_order->total = $subtotal;
                        $sales_order->grand_total = $subtotal;
                        $sales_order->save();
                    }
                }
            } else if (SalesOrder::MARKETPLACE_ORDER[$this->marketplace] == SalesOrder::MARKETPLACE_ORDER['Lazada']) {

                $data = [];
                foreach ($rows as $row) {
                    $ekspedisi = '';
                    $resi = '';
                    if ($row['Shipping Provider']) {
                        $ekspedisi = $row['Shipping Provider'];
                    }
                    if ($row['Tracking Code']) {
                        $resi = $row['Tracking Code'];
                    }

                    if ($row['Order Number']) {
                        if (array_key_exists($row['Order Number'], $data)) {
                            if (array_key_exists($row['Seller SKU'], $data[$row['Order Number']]['product'])) {
                                $qty = $data[$row['Order Number']]['product'][$row['Seller SKU']]['qty'];
                                $data[$row['Order Number']]['product'][$row['Seller SKU']]['qty'] = $qty + 1;
                            } else {
                                $data[$row['Order Number']]['product'][$row['Seller SKU']] = [
                                    'price' => $row['Unit Price'],
                                    'qty'   => 1
                                ];
                            }
                        } else {
                            $data[$row['Order Number']]['product'][$row['Seller SKU']] = [
                                'price' => $row['Unit Price'],
                                'qty'   => 1
                            ];

                            $data[$row['Order Number']]['info'] = [
                                'customer_marketplace'  => $row['Shipping Name'],
                                'address_marketplace'   => $row['Shipping Address'],
                                'ekspedisi_marketplace' => $ekspedisi,
                                'resi'                  => $resi,
                                'no_hp_marketplace'     => $row['Shipping Phone Number'],
                                'order_date'            => $row['Created at'],
                            ];
                        }
                    }
                }

                if ($data) {
                    foreach ($data as $key => $value) {

                        $sales_order = SalesOrder::where('code', $key)->first();

                        if ($sales_order == '') {
                            // VERIFIED DATA
                            if ($value['info']['resi'] == null) {
                                $collect_error[] = $key . ' : "No. Resi" is empty';
                                continue;
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
                            $sales_order->marketplace_order = SalesOrder::MARKETPLACE_ORDER[$this->marketplace];
                            $sales_order->warehouse_id = $this->warehouse;
                            $sales_order->store_name = $this->store_name;
                            $sales_order->store_phone = $this->store_phone;
                            $sales_order->customer_marketplace = $value['info']['customer_marketplace'];
                            $sales_order->address_marketplace = $value['info']['address_marketplace'];
                            $sales_order->ekspedisi_marketplace = $value['info']['ekspedisi_marketplace'];
                            $sales_order->resi = $value['info']['resi'];
                            $sales_order->no_hp_marketplace = $value['info']['no_hp_marketplace'];
                            $sales_order->order_date = $value['info']['order_date'] ? $this->transformDate($value['info']['order_date']) : null;

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
                            $sales_order_update->grand_total = $subtotal;
                            $sales_order_update->save();

                            $collect_success[] = $key;
                        } else {
                            $collect_error[] = $key . ' : Duplicate order';
                        }
                    }
                }
            } else if (SalesOrder::MARKETPLACE_ORDER[$this->marketplace] == SalesOrder::MARKETPLACE_ORDER['Blibli']) {

                $data = [];
                foreach ($rows as $row) {

                    if ($row['No. Order']) {
                        if (array_key_exists($row['No. Order'], $data)) {
                            if (array_key_exists($row['Merchant SKU'], $data[$row['No. Order']]['product'])) {
                                $qty = $data[$row['No. Order']]['product'][$row['Merchant SKU']]['qty'];
                                $data[$row['No. Order']]['product'][$row['Merchant SKU']]['qty'] = $qty + $row['Total Barang'];
                            } else {
                                $data[$row['No. Order']]['product'][$row['Merchant SKU']] = [
                                    'price' => $row['Harga Produk'],
                                    'qty'   => $row['Total Barang'],
                                ];
                            }
                        } else {
                            $data[$row['No. Order']]['product'][$row['Merchant SKU']] = [
                                'price' => $row['Harga Produk'],
                                'qty'   => $row['Total Barang'],
                            ];

                            $data[$row['No. Order']]['info'] = [
                                'customer_marketplace'  => $row['Nama Pemesan'],
                                // 'address_marketplace'   => $row[''],
                                'ekspedisi_marketplace' => $row['Servis Logistik'],
                                'resi'                  => $row['No. Awb'],
                                // 'no_hp_marketplace'     => $row[''],
                                'order_date'            => $row['Tanggal Order'],
                            ];
                        }
                    }
                }

                if ($data) {
                    foreach ($data as $key => $value) {

                        $sales_order = SalesOrder::where('code', $key)->first();

                        if ($sales_order == '') {
                            // VERIFIED DATA
                            if ($value['info']['resi'] == null) {
                                $collect_error[] = $key . ' : "No. Resi" is empty';
                                continue;
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
                            $sales_order->marketplace_order = SalesOrder::MARKETPLACE_ORDER[$this->marketplace];
                            $sales_order->warehouse_id = $this->warehouse;
                            $sales_order->store_name = $this->store_name;
                            $sales_order->store_phone = $this->store_phone;
                            $sales_order->customer_marketplace = $value['info']['customer_marketplace'];
                            // $sales_order->address_marketplace = $value['info']['address_marketplace'];
                            $sales_order->ekspedisi_marketplace = $value['info']['ekspedisi_marketplace'];
                            $sales_order->resi = $value['info']['resi'];
                            // $sales_order->no_hp_marketplace = $value['info']['no_hp_marketplace'];
                            $sales_order->order_date = $value['info']['order_date'] ? $this->transformDate($value['info']['order_date'], 'd/m/Y H:i') : null;

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
                            $sales_order_update->grand_total = $subtotal;
                            $sales_order_update->save();

                            $collect_success[] = $key;
                        } else {
                            $collect_error[] = $key . ' : Duplicate order';
                        }
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
