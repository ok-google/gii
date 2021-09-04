<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>BS-{{ \Carbon\Carbon::parse($journal_periode->to_date)->isoFormat('DDMMYY') }}</title>
    @include('superuser.asset.css-pdf')
    <style>
      td {
        padding: 5px;
      }
      td.no-pad {
        padding: 0px;
      }
    </style>
  </head>
  <body style="font-size: 0.7em;">
    <table>
      <tr>
        <td class="text-center text-bold" style="font-size: 1.3em;">Balance Sheet</td>
      </tr>
      <tr>
        <td class="text-center text-bold">{{ \Carbon\Carbon::parse($journal_periode->from_date)->isoFormat('DD/MM/YYYY') }} - {{ \Carbon\Carbon::parse($journal_periode->to_date)->isoFormat('DD/MM/YYYY') }}</td>
      </tr>
    </table>

    <table class="" style="border-collapse: collapse;">
      <tr>
        <td class="no-pad" width="50%" style="vertical-align: top">
          <table id="a-id" style="border-collapse: collapse;">
            <tr>
              <td class="text-center border-full" colspan="3"><b>Activa</b></td>
            </tr>
            <tr>
              <td class="text-left border-left" width="33.33%"><b>Activa Lancar</b></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>
            {{-- A1 --}}
            @foreach ($collect['A1'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">{{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }}</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL A1 --}}
            <tr>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format($collect['A1_TOTAL'], 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-left" width="33.33%"><b>Activa Tetap</b></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>
            {{-- A2 --}}
            @foreach ($collect['A2'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">{{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }}</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- A3 --}}
            @foreach ($collect['A3'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">({{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }})</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL A3 --}}
            <tr>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format($collect['A2_TOTAL'] - $collect['A3_TOTAL'], 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-left" width="33.33%"><b>Activa Tidak Lancar</b></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>
            {{-- A4 --}}
            @foreach ($collect['A4'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">({{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }})</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL A4 --}}
            <tr>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format($collect['A4_TOTAL'], 2, ",", ".") }}</td>
            </tr>

            {{-- SPACE --}}
            <tr>
              <td class="space-a text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>

            {{-- TOTAL A --}}
            <tr>
              <td class="text-right border-top border-right" colspan="2"><b>Total</b></td>
              <td class="text-left border-right border-top border-bottom" width="33.33%">{{ 'Rp. '.number_format($collect['A1_TOTAL'] + $collect['A2_TOTAL'] - $collect['A3_TOTAL'] + $collect['A4_TOTAL'], 2, ",", ".") }}</td>
            </tr>
            
          </table>
        </td>
        {{-- PASIVA --}}
        <td class="no-pad" width="50%" style="vertical-align: top">
          <table id="p-id" style="border-collapse: collapse;">
            <tr>
              <td class="text-center border-top border-right border-bottom" colspan="3"><b>Passiva</b></td>
            </tr>
            <tr>
              <td class="text-left border-right" width="33.33%"><b>Hutang Lancar</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>
            {{-- P1 --}}
            @foreach ($collect['P1'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }}</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL P1 --}}
            <tr>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P1_TOTAL']), 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-right" width="33.33%"><b>Hutang Jangka Panjang</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>
            {{-- P2 --}}
            @foreach ($collect['P2'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }}</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL P2 --}}
            <tr>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P2_TOTAL']), 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-right" width="33.33%"><b>Modal</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>
            {{-- P3 --}}
            @foreach ($collect['P3'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }}</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- P4 --}}
            @foreach ($collect['P4'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">({{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }})</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach

            {{-- TOTAL P4 --}}
            <tr>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P3_TOTAL'] - $collect['P4_TOTAL']), 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-right" width="33.33%"><b>L/R Tahun Lalu</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format($collect['PL_PREV'], 2, ",", ".") }}</td>
            </tr>
            <tr>
              <td class="text-left border-right" width="33.33%"><b>L/R Tahun Berjalan</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format($collect['PL_NOW'], 2, ",", ".") }}</td>
            </tr>

            {{-- SPACE --}}
            <tr>
              <td class="space-p text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>

            {{-- TOTAL P --}}
            <tr>
              <td class="text-right border-top border-right" colspan="2"><b>Total</b></td>
              <td class="text-left border-right border-top border-bottom" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P1_TOTAL']) + abs($collect['P2_TOTAL']) + abs($collect['P3_TOTAL']) - abs($collect['P4_TOTAL']) + $collect['PL_PREV'] + $collect['PL_NOW'], 2, ",", ".") }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <script type="text/javascript">
      $(document).ready(function() {
        
        var a_height = $('#a-id').height();
        var p_height = $('#p-id').height();
  
        if(a_height > p_height) {
          $('.space-p').height( a_height - p_height - 0.5);
        } else if(a_height < p_height) {
          $('.space-a').height( p_height - a_height - 0.5);
        }
  
      });
      </script>
  </body>
</html>
