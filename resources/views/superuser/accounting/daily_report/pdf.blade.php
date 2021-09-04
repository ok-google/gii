<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daily Cash / Bank Report</title>
    @include('superuser.asset.css-pdf')
  </head>
  <body style="font-size: 0.7em;">
    <table>
      <tr>
        <td class="text-center text-bold" style="font-size: 1.3em;">Daily Cash / Bank Report</td>
      </tr>
    </table>
    <table class="mt-25">
      <tr>
        <td class="text-left" style="font-size: 1em;">{{ $title }}</td>
        <td class="text-right" style="font-size: 1em;">Date : {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
      </tr>
    </table>
    <table style="border-collapse: collapse;">
      <thead>
        <tr>
          <th class="text-center border-full" width="10%">Date</th>
          <th class="text-left border-full">Transaction</th>
          <th class="text-center border-full" width="20%">Debet</th>
          <th class="text-center border-full" width="20%">Credit</th>
          <th class="text-center border-full" width="20%">Balance</th>
        </tr>
      </thead>
      <tbody>
        @php
            $saldoakhir = 'Rp. 0,00';
        @endphp
        @foreach ($data as $value)
            <tr>
              <td class="text-center border-full">{{ $value[0] }}</td>
              <td class="text-left border-full">{{ $value[1] }}</td>
              <td class="text-center border-full">{{ $value[2] }}</td>
              <td class="text-center border-full">{{ $value[3] }}</td>
              <td class="text-center border-full">{{ $value[4] }}</td>
            </tr>
            @php
                $saldoakhir = $value[4];
            @endphp
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th class="text-right" colspan="4">SALDO AKHIR &nbsp;</th>
          <th class="text-center border-full">{{ $saldoakhir }}</th>
        </tr>
      </tfoot>
    </table>
  </body>
</html>
