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
    </table>
    <table class="mt-25" style="border-collapse: collapse;">
      {{-- PENDAPATAN PENJUALAN --}}
      <tr>
        <td class="text-left text-bold" colspan="5">Pendapatan Penjualan</td>
      </tr>
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" colspan="2">{{ $profit_loss['a']['name'] }}</td>
        <td class="text-left" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['a']['value'], 2, ",", ".") }}</td>
      </tr>
      @foreach ($profit_loss['b'] as $item)
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" width="{{ $col2 }}"></td>
        <td class="text-left" width="{{ $col3 }}">{{ $item['name'] }}</td>
        <td class="text-left" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}">{{ 'Rp. '.number_format($item['value'], 2, ",", ".") }}</td>
      </tr>
      @endforeach
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Pendapatan Penjualan Bersih</td>
        <td class="text-left" width="{{ $col4 }}"></td>
        <td class="text-left border-top" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['pendapatan_penjualan_bersih'], 2, ",", ".") }}</td>
      </tr>
      {{-- HPP --}}
      <tr>
        <td class="text-left text-bold" colspan="5">Harga Pokok Penjualan</td>
      </tr>
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Persediaan Awal</td>
        <td class="text-left" width="{{ $col4 }}">{{ 'Rp. '.number_format($profit_loss['c']['persediaan_awal'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @foreach ($profit_loss['d'] as $item)
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" width="{{ $col2 }}"></td>
        <td class="text-left" width="{{ $col3 }}">{{ $item['name'] }}</td>
        <td class="text-left" width="{{ $col4 }}">{{ 'Rp. '.number_format($item['value'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @endforeach
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Pendapatan Penjualan Bersih</td>
        <td class="text-left border-top" width="{{ $col4 }}">{{ 'Rp. '.number_format($profit_loss['hpp_pendapatan_penjualan_bersih'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Persediaan Akhir</td>
        <td class="text-left" width="{{ $col4 }}">{{ 'Rp. '.number_format($profit_loss['c']['persediaan_akhir'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Harga Pokok Penjualan</td>
        <td class="text-left border-top" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['hpp'], 2, ",", ".") }}</td>
      </tr>
      {{-- BEBAN OPERASIONAL --}}
      <tr>
        <td class="text-left text-bold" colspan="5">Beban Operasional</td>
      </tr>
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Beban Penjualan</td>
        <td class="text-left" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @foreach ($profit_loss['e'] as $item)
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" width="{{ $col2 }}"></td>
        <td class="text-left" width="{{ $col3 }}">{{ $item['name'] }}</td>
        <td class="text-left" width="{{ $col4 }}">{{ 'Rp. '.number_format($item['value'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @endforeach
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Beban Administrasi</td>
        <td class="text-left" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @foreach ($profit_loss['f'] as $item)
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" width="{{ $col2 }}"></td>
        <td class="text-left" width="{{ $col3 }}">{{ $item['name'] }}</td>
        <td class="text-left" width="{{ $col4 }}">{{ 'Rp. '.number_format($item['value'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @endforeach
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Total Beban Operasional</td>
        <td class="text-left border-top" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['total_beban_operasional'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left text-bold" colspan="2">Laba Operasional</td>
        <td class="text-left" width="{{ $col4 }}"></td>
        <td class="text-left border-top" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['laba_operasional'], 2, ",", ".") }}</td>
      </tr>
      {{-- PENDAPATAN DAN KEUNTUNGAN LAIN --}}
      <tr>
        <td class="text-left text-bold" colspan="5">Pendapatan dan Keuntungan Lain</td>
      </tr>
      @foreach ($profit_loss['g'] as $item)
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" colspan="2">{{ $item['name'] }}</td>
        <td class="text-left" width="{{ $col4 }}">{{ 'Rp. '.number_format($item['value'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @endforeach
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" colspan="2"></td>
        <td class="text-left border-top" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['g_total'], 2, ",", ".") }}</td>
      </tr>
      {{-- BEBAN DAN KERUGIAN LAIN --}}
      <tr>
        <td class="text-left text-bold" colspan="5">Beban dan Kerugian Lain</td>
      </tr>
      @foreach ($profit_loss['h'] as $item)
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" colspan="2">{{ $item['name'] }}</td>
        <td class="text-left" width="{{ $col4 }}">{{ 'Rp. '.number_format($item['value'], 2, ",", ".") }}</td>
        <td class="text-left" width="{{ $col5 }}"></td>
      </tr>
      @endforeach
      <tr>
        <td class="text-left" width="{{ $col1 }}"></td>
        <td class="text-left" colspan="2"></td>
        <td class="text-left" width="{{ $col4 }}"></td>
        <td class="text-left" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['h_total'], 2, ",", ".") }}</td>
      </tr>
      {{-- LABA BERSIH --}}
      <tr>
        <td class="text-left text-bold" colspan="3">Laba Bersih</td>
        <td class="text-left border-top" width="{{ $col4 }}"></td>
        <td class="text-left border-top" width="{{ $col5 }}">{{ 'Rp. '.number_format($profit_loss['laba_bersih'], 2, ",", ".") }}</td>
      </tr>

    </table>
  </body>
</html>
