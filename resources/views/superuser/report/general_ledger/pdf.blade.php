<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>GL-{{ \Carbon\Carbon::parse($journal_periode->from_date)->isoFormat('DDMMYY') }}</title>
    @include('superuser.asset.css-pdf')
  </head>
  <body style="font-size: 0.7em;">
    <table>
      <tr>
        <td class="text-center text-bold" style="font-size: 1.3em;">General Ledger Report</td>
      </tr>
    </table>
    @foreach ($general_ledger as $item)
    <table class="mt-25">
      <tr>
        <td class="text-left" style="font-size: 1em;">{{ $item['title'] }}</td>
        <td class="text-right" style="font-size: 1em;">Period : {{ \Carbon\Carbon::parse($journal_periode->from_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($journal_periode->to_date)->format('d/m/Y') }}</td>
      </tr>
    </table>
    <table style="border-collapse: collapse;">
      <thead>
        <tr>
          <th class="text-center border-full" width="10%">Date</th>
          <th class="text-left border-full">Transaction</th>
          <th class="text-center border-full" width="20%">Debet</th>
          <th class="text-center border-full" width="20%">Credit</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($item['data'] as $value)
            <tr>
              <td class="border-full">{{ $value['date'] }}</td>
              <td class="text-left border-full">{{ $value['name'] }}</td>
              <td class="text-center border-full">{{ $value['debet'] }}</td>
              <td class="text-center border-full">{{ $value['credit'] }}</td>
            </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th class="text-right" colspan="2" id="action_table">TOTAL &nbsp;</th>
          <th class="text-center border-full">{{ $item['total']['debet'] }}</th>
          <th class="text-center border-full">{{ $item['total']['credit'] }}</th>
        </tr>
        <tr>
          <th class="text-right" colspan="2" id="action_table">SALDO AKHIR &nbsp;</th>
          <th class="text-center border-full">{{ $item['saldoakhir']['debet'] }}</th>
          <th class="text-center border-full">{{ $item['saldoakhir']['credit'] }}</th>
        </tr>
      </tfoot>
    </table>
    @endforeach
  </body>
</html>
