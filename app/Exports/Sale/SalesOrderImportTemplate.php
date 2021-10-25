<?php

namespace App\Exports\Sale;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesOrderImportTemplate implements FromArray, ShouldAutoSize
{
    public function array(): array
    {
        return [
            [
                'Warehouse',
                'Marketplace',
                'Store',
                // 'Tanggal Invoice',
                'Invoice',
                'Ekspedisi',
                'Resi / AWB',
                'Waktu Pembayaran',
                // 'Batas Waktu Pengiriman',
                'Nama Penerima',
                'Nama Akun',
                'SKU',
                'QTY',
                // 'Satuan',
                'Satuan Harga',
                // 'Total Harga Produk',
                'Voucher',
                'total Berat Barang',
                'Alamat Penerima',
                'No. Telp',
                'Kota',
                'Provinsi'
            ]
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,
            'N' => NumberFormat::FORMAT_TEXT,
            'O' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_TEXT,
            'Q' => NumberFormat::FORMAT_TEXT,
            'R' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
