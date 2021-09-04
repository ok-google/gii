<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $receipt_invoice->code }}</title>
    @include('superuser.asset.css-pdf')
    <style>
      table.main-table td, table.main-table th {
        padding: 5px;
      }
    </style>
  </head>
  <body style="font-size: 0.7em;">

    <table>
      <tr>
        <td style="width: 70%">
          <table>
            <tr>
              <td class="text-bold">{{ $company->name ?? '' }}</td>
            </tr>
            <tr>
              <td>{{ $company->address . ', ' . $company->text_provinsi . ', ' . $company->text_kota }}</td>
            </tr>
            <tr>
              <td>No Telp : {{ $company->phone ?? '-' }} &nbsp;&nbsp;&nbsp;&nbsp; Email : {{ $company->email ?? '-' }}</td>
            </tr>
          </table>
        </td>
        <td style="width: 30%">
          <table>
            <tr><td colspan="2" class="text-bold text-center" style="font-size: 1.7em; text-decoration: underline;">Pembayaran Pembelian</td></tr>
            <tr>
              <td style="width: 50%">
                <table style="border-collapse: collapse">
                  <tr><td class="border-full text-center">No. Pembayaran</td></tr>
                  <tr><td class="border-full text-center">{{ $receipt_invoice->code }}</td></tr>
                </table>
              </td>
              <td style="width: 50%">
                <table style="border-collapse: collapse">
                  <tr><td class="border-full text-center">Tgl. Pembayaran</td></tr>
                  <tr><td class="border-full text-center">{{ \Carbon\Carbon::parse($receipt_invoice->select_date)->format('d/m/Y') }}</td></tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <hr />
    <hr />

    <table>
      <tr>
        <td style="width: 45%" valign="top">
          <table style="border-collapse: collapse">
            <tr><td class="border-full" style="width: 20%">Pemasok</td><td class="border-full">{{ $receipt_invoice->customer->name }}</td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full text-center" style="width: 100%">Dikirim ke</td></tr>
            <tr><td class="border-full" style="width: 100%"><br /><br /><br /></td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full" style="width: 20%">Departemen</td><td class="border-full"></td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full" style="width: 20%">Proyek</td><td class="border-full"></td></tr>
          </table>
        <td>
        <td style="width: 25%" valign="top">
          <table style="border-collapse: collapse">
            <tr><td class="border-full">Mata Uang</td><td class="border-full">IDR</td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full" style="width: 20px"></td><td class="border-full">Pembayaran Pajak</td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full" style="width: 20px"></td><td class="border-full">Cek Kosong</td></tr>
          </table>
        <td>
        <td style="width: 15%" valign="top">
          <table style="border-collapse: collapse">
            <tr><td class="border-full text-center">No. Formulir</td></tr>
            <tr><td class="border-full text-center">&nbsp;</td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full text-center">Bank</td></tr>
            <tr><td class="border-full text-center">{{ $receipt_invoice->coa->name }}</td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full text-center">No. Cek</td></tr>
            <tr><td class="border-full text-center">{{ $receipt_invoice->code }}</td></tr>
          </table>
        <td>
        <td style="width: 15%" valign="top">
          <table style="border-collapse: collapse">
            <tr><td class="border-full text-center">Jumlah Cek</td></tr>
            <tr><td class="border-full text-center">{{ number_format($totalPaid, 2, ',', '.') }}</td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full text-center">Nilai Tukar</td></tr>
            <tr><td class="border-full text-center">1</td></tr>
          </table>
          <table style="border-collapse: collapse; margin-top: 5px">
            <tr><td class="border-full text-center">Tgl. Cek</td></tr>
            <tr><td class="border-full text-center">{{ \Carbon\Carbon::parse($receipt_invoice->select_date)->format('d/m/Y') }}</td></tr>
          </table>
        <td>
      </tr>
    </table>

    <table class="mt-10" style="border-collapse: collapse">
      <thead>
        <tr>
          <th class="border-full">No.</th>
          <th class="border-full">No. Faktur</th>
          <th class="border-full">Tanggal</th>
          <th class="border-full">Jatuh Tempo</th>
          <th class="border-full">Diskon</th>
          <th class="border-full">Jumlah</th>
          <th class="border-full">Terhutang</th>
          <th class="border-full">Jumlah Pembayaran</th>
        </td>
      </thead>
      <tbody>
        @php
          $no = 1;
          $totalDetail = sizeof($receipt_invoice->details);
        @endphp
        @foreach ($receipt_invoice->details as $detail)
        @php
          $borderBottom = $totalDetail == $no ? ' border-bottom' : '';
        @endphp
        <tr>
          <td class="border-left border-right text-right{{ $borderBottom }}">{{ $no }}</td>
          <td class="border-left border-right{{ $borderBottom }}">{{ $detail->sales_order->code }}</td>
          <td class="border-left border-right{{ $borderBottom }}">{{ \Carbon\Carbon::parse($detail->sales_order->created_at)->format('d/m/Y') }}</td>
          <td class="border-left border-right{{ $borderBottom }}">{{ \Carbon\Carbon::parse($detail->sales_order->created_at)->format('d/m/Y') }}</td>
          <td class="border-left border-right text-right{{ $borderBottom }}">{{ number_format($detail->discount ? $detail->discount : 0, 2, ',', '.') }}</td>
          <td class="border-left border-right text-right{{ $borderBottom }}">{{ number_format($detail->sales_order->grand_total, 2, ',', '.') }}</td>
          <td class="border-left border-right text-right{{ $borderBottom }}">{{ number_format($detail->total, 2, ',', '.') }}</td>
          <td class="border-left border-right text-right{{ $borderBottom }}">{{ number_format($detail->paid, 2, ',', '.') }}</td>
        </tr>
        @php
          $no++;
        @endphp
        @endforeach
      </tbody>
    </table>

    <table class="mt-10">
      <tr>
        <td style="width: 80px">Terbilang</td>
        <td style="width: 1px">:</td>
        <td>{{ terbilang($totalPaid) }}</td>
      </tr>
    </table>

    <table class="mt-10" style="border-collapse: collapse; width: 45%">
      <tr>
        <td class="border-full text-center">Memo</td>
      </tr>
      <tr>
        <td class="border-full">@php echo $receipt_invoice->description ? $receipt_invoice->description : "<br /><br /><br />" @endphp</td>
      </tr>
    </table>

  </body>
</html>
@php
function terbilang($angka) {
  $angka=abs($angka);
  $baca =array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");

  $terbilang="";
  if ($angka < 12){
    $terbilang= " " . $baca[$angka];
  }
  else if ($angka < 20){
    $terbilang= terbilang($angka - 10) . " belas";
  }
  else if ($angka < 100){
    $terbilang= terbilang($angka / 10) . " puluh" . terbilang($angka % 10);
  }
  else if ($angka < 200){
    $terbilang= " seratus" . terbilang($angka - 100);
  }
  else if ($angka < 1000){
    $terbilang= terbilang($angka / 100) . " ratus" . terbilang($angka % 100);
  }
  else if ($angka < 2000){
    $terbilang= " seribu" . terbilang($angka - 1000);
  }
  else if ($angka < 1000000){
    $terbilang= terbilang($angka / 1000) . " ribu" . terbilang($angka % 1000);
  }
  else if ($angka < 1000000000){
    $terbilang= terbilang($angka / 1000000) . " juta" . terbilang($angka % 1000000);
  }
  return $terbilang;
}
@endphp