<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PL-{{ \Carbon\Carbon::parse($journal_periode->from_date)->isoFormat('DDMMYY') }}</title>
    @include('superuser.asset.css-pdf')
    <style>
      td {
        padding: 5px;
      }
    </style>
  </head>
  <body style="font-size: 0.7em;">
    @php
        $col1 = '5%';
        $col2 = '5%';
        $col3 = '40%';
        $col4 = '25%';
        $col5 = '25%';
    @endphp
    <table>
      <tr>
        <td class="text-center text-bold" style="font-size: 1.3em;">Profit Loss Report</td>
      </tr>
      <tr>
        <td class="text-center text-bold">{{ \Carbon\Carbon::parse($journal_periode->from_date)->isoFormat('DD/MM/YYYY') }} - {{ \Carbon\Carbon::parse($journal_periode->to_date)->isoFormat('DD/MM/YYYY') }}</td>
      </tr>
    </table>

    <table class="mt-25">
      <tr>
        <td class="text-left" width="50" colspan="4">
          <b>Pendapatan dari penjualan</b>
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50">Penjualan</td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['A'], 2, ",", ".") }}</td>
        <td class="text-left" width="50"></td>
      </tr>
      <tr>
        <td class="text-left" width="50">Retur Penjualan</td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['B'], 2, ",", ".") }}</td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
      </tr>
      <tr>
        <td class="text-left" width="50">Potongan Penjualan</td>
        <td class="text-left border-bottom" width="50">{{ 'Rp. '.number_format($report['C'], 2, ",", ".") }}</td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
      </tr>
      <tr>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['B']+$report['C'], 2, ",", ".") }}</td>
        <td class="text-left" width="50"></td>
      </tr>
      <tr>
        <td class="text-left" width="50"><b>Penjualan Bersih</b></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['D'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50"><b>Harga Pokok Penjualan</b></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
        <td class="text-left border-bottom" width="50">{{ 'Rp. '.number_format($report['E'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50"><b>Laba Kotor</b></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['laba_kotor'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4"><b>Beban Operasional</b></td>
      </tr>
      @foreach ($report['F'] as $item)
        <tr>
          <td class="text-left" width="50">{{ $item->coa->code .' - '. $item->coa->name }}</td>
          <td class="text-left" width="50">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
          <td class="text-left" width="50"></td>
          <td class="text-left" width="50"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="50"><b>Total Beban Operasional</b></td>
        <td class="text-left border-top" width="50"></td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['G'], 2, ",", ".") }}</td>
        <td class="text-left" width="50"></td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4"><b>Beban Administrasi</b></td>
      </tr>
      @foreach ($report['H'] as $item)
        <tr>
          <td class="text-left" width="50">{{ $item->coa->code .' - '. $item->coa->name }}</td>
          <td class="text-left" width="50">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
          <td class="text-left" width="50"></td>
          <td class="text-left" width="50"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="50"><b>Total Beban Administrasi</b></td>
        <td class="text-left border-top" width="50"></td>
        <td class="text-left border-bottom" width="50">{{ 'Rp. '.number_format($report['I'], 2, ",", ".") }}</td>
        <td class="text-left" width="50"></td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50"><b>Jumlah Beban Operasional</b></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
        <td class="text-left border-bottom" width="50">{{ 'Rp. '.number_format($report['J'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50"><b>Laba Bersih Operasional</b></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['M'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4"><b>Pendapatan Lain - Lain :</b></td>
      </tr>
      @foreach ($report['K'] as $item)
        <tr>
          <td class="text-left" width="50">{{ $item->coa->code .' - '. $item->coa->name }}</td>
          <td class="text-left" width="50">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
          <td class="text-left" width="50"></td>
          <td class="text-left" width="50"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="50"><b>Total Pendapatan Lain - Lain</b></td>
        <td class="text-left border-top" width="50"></td>
        <td class="text-left border-bottom" width="50">{{ 'Rp. '.number_format($report['L'], 2, ",", ".") }}</td>
        <td class="text-left" width="50"></td>
      </tr>
      <tr>
        <td class="text-left" width="50" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="50"><b>Laba Bersih</b></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50"></td>
        <td class="text-left" width="50">{{ 'Rp. '.number_format($report['laba_bersih'], 2, ",", ".") }}</td>
      </tr>
    </table>

    {{-- PAGING --}}
  </body>
</html>
