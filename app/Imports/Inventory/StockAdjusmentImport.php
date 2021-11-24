<?php

namespace App\Imports\Inventory;

use App\Entities\Inventory\StockAdjusment;
use App\Entities\Inventory\StockAdjusmentDetail;
use App\Entities\Master\Warehouse;
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
use Auth;
use Carbon\Carbon;

HeadingRowFormatter::default('none');

class StockAdjusmentImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function  __construct($warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function collection(Collection $rows)
    {
        // dd("asssss");
        DB::beginTransaction();

        try {
            $collect_error = [];
            $collect_success = [];
            $data = [];
            // dd($rows);
            foreach ($rows as $row) {
                // foreach($rowTmp as $row){
                    
                if($row['Sku'] != ""){
                        if (array_key_exists($row['Code'], $data)) {
                            if (array_key_exists($row['Sku'], $data[$row['Code']]['product'])) {
                                $qty = $data[$row['Code']]['product'][$row['Sku']]['Qty'];
                                $data[$row['Code']]['product'][$row['Sku']]['Qty'] = $qty + $row['Qty'];
                            } else {
                                $data[$row['Code']]['product'][$row['Sku']] = [
                                    'price' => preg_replace('/\D/', '', $row['Price']),
                                    'qty'   => $row['Qty']
                                ];
                            }
                        } else {
                            $data[$row['Code']]['product'][$row['Sku']] = [
                                'price' => preg_replace('/\D/', '', $row['Price']),
                                'qty'   => $row['Qty']
                            ];

                            $data[$row['Code']]['info'] = [
                                'warehouse'             => trim($row['Warehouse']),
                                'minus'                 => $row['Minus'],
                                'description'           => ($row['Description']??''),
                            ];
                        }
                }
            // }
            }

            if ($data) {
                // dd($data);
                foreach ($data as $key => $value) {
                    // foreach($value as )
                    
                    $stock_adjustment = StockAdjusment::where('code', $key)->first();

                    if (is_null($stock_adjustment)) {
                        // VERIFIED DATA

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

                        if ($value['product']) {
                            $found_error_product = false;
                            foreach ($value['product'] as $key_product => $value_product) {
                                $cek_product = Product::where('code', $key_product)->first();
                                if ($cek_product == null) {
                                    $collect_error[] = $key . ' : Sku ' . $key_product . ' not found in database';
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

                        $superuser = Auth::guard('superuser')->user();
                        $stock_adjustment2 = new StockAdjusment;
                        $stock_adjustment2->code = $key;
                        $stock_adjustment2->type = $superuser->type;
                        $stock_adjustment2->warehouse_id = $warehouse_id->id;
                        $stock_adjustment2->branch_office_id = $superuser->branch_office_id;
                        $stock_adjustment2->minus = $value['info']['minus'];
                        $stock_adjustment2->description = $value['info']['description'];

                        $stock_adjustment2->status = StockAdjusment::STATUS['ACTIVE'];
                        if($stock_adjustment2->save()){

                            if ($value['product']) {
                                foreach ($value['product'] as $key_product => $value_product) {
                                    $cek_product = Product::where('code', trim($key_product))->first();
                                    if($cek_product){
                                        $stock_adjustment_detail = new StockAdjusmentDetail;
                                        $stock_adjustment_detail->stock_adjusment_id = $stock_adjustment2->id;
                                        $stock_adjustment_detail->product_id = $cek_product->id;
                                        $stock_adjustment_detail->qty = $value_product['qty'];
                                        $stock_adjustment_detail->price = 0;
                                       $stock_adjustment_detail->total = 0;
                                        $stock_adjustment_detail->save();
                                    }
                                }
                            }

                        }

                        $collect_success[] = $key;
                    } else {
                        // dd("as");
                        $collect_error[] = $key . ' : Duplicate Code';
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
            // dd($e);
            $this->error = $e->getMessage();
            DB::rollBack();
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function convertDate($obj){
        // $ex = explode(" ", $obj);
            // $to = Carbon::parse($request->to)->format('Y-m-d');
        return Carbon::parse($obj)->format('Y-m-d H:i:s');
    }
    public function transformDate($value, $format = 'Y-m-d')
    {
        try {
            dd(\Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)));
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            dd($e);
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }
}
