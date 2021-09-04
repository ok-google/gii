<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $receipt->code }}</title>
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
        <td>{{ $company->name }}</td>
      </tr>
      <tr>
        <td>EmaiL: {{ $company->email }}</td>
      </tr>
    </table>

    <table class="mt-25">
      <tr>
        <td class="text-center border-top border-bottom" style="font-size: 1.5em; padding: 10px"><strong>Cash/Bank Receipt</strong></td>
      </tr>
    </table>

    <div class="mt-10 mb-25" style="width: 40%; float: right">
      <table>
        <tr>
          <td>No. Transaksi</td><td>:</td><td>{{ $receipt->code }}</td>
        </tr>
        <tr>
          <td>Tanggal</td><td>:</td><td>{{ \Carbon\Carbon::parse($receipt->select_date)->format('d/m/Y') }}</td>
        </tr>
      </table>
    </div>

    <div style="clear: both;"></div>

    <table class="mt-25 main-table" style="border-collapse: collapse">
      <thead>
        <tr>
          <th style="background-color: rgb(97, 125, 138); color: white;">AKUN</th>
          <th style="background-color: rgb(97, 125, 138); color: white;">DESKRIPSI</th>
          <th style="background-color: rgb(97, 125, 138); color: white;">DEBIT (in IDR)</th>
          <th style="background-color: rgb(97, 125, 138); color: white;">KREDIT (in IDR)</th>
        </tr>
      </thead>
      <tbody>
        @php
          $debet = 0;
          $kredit = 0;
        @endphp
        @foreach ($receipt_debet as $detail)
        <tr>
          <td>{{ $detail->coa->code }} {{ $detail->coa->name }}</td>
          <td>{{ $detail->name }}</td>
          <td class="text-right">{{ 'Rp. ' . number_format($detail->total, 2, ',', '.') }}</td>
          <td class="text-right"></td>
        </tr>
        @php
          $debet += $detail->total;
        @endphp

        @endforeach
        @foreach ($receipt_credit as $detail)
        <tr>
          <td>{{ $detail->coa->code }} {{ $detail->coa->name }}</td>
          <td>{{ $detail->name }}</td>
          <td class="text-right"></td>
          <td class="text-right">{{ 'Rp. ' . number_format($detail->total, 2, ',', '.') }}</td>
        </tr>
        @php
          $kredit += $detail->total;
        @endphp

        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2" class="text-right" style="background-color: rgb(97, 125, 138); color: white;">TOTAL</td>
          <td class="text-right" style="background-color: rgb(97, 125, 138); color: white;">{{ 'Rp. ' . number_format($debet, 2, ',', '.') }}</td>
          <td class="text-right" style="background-color: rgb(97, 125, 138); color: white;">{{ 'Rp. ' . number_format($kredit, 2, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>

    <table class="mt-25" style="border-collapse: collapse">
      <tr>
        <td class="border-full text-center">Dibuat oleh,</td>
        <td class="border-full text-center">Diperiksa oleh,</td>
        <td class="border-full text-center">Disetujui oleh,</td>
        <td class="border-full text-center">Diterima oleh,</td>
      </tr>
      <tr>
        <td class="border-full"><br /><br /><br /><br /><br /><br /></td>
        <td class="border-full"><br /><br /><br /><br /><br /><br /></td>
        <td class="border-full"><br /><br /><br /><br /><br /><br /></td>
        <td class="border-full"><br /><br /><br /><br /><br /><br /></td>
      </tr>
    </table>
</html>
