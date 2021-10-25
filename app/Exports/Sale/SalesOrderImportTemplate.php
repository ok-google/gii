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
}
