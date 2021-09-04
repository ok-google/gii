<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    DR-{{ \Carbon\Carbon::parse($date)->isoFormat('DDMMYY') }}
  </title>
  @include('superuser.asset.css-pdf')
</head>

<body style="font-size: 0.7em;">
  <table>
    <tr>
      <td class="text-center text-bold" style="font-size: 1.3em;">Daily Cash/Bank Recapitulation</td>
    </tr>
  </table>

  <table class="mt-25">
    <tr>
      <td width="50%">COA Account : {{ $coa_text }}</td>
      <td class="text-right" width="50%">Date : {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
      </td>
    </tr>
  </table>

  <table class="mt-10" style="border-collapse: collapse;">
    <thead>
      <tr>
        <th class="text-center border-full" width="15%">COA</th>
        <th class="text-center border-full" width="25%">Account</th>
        <th class="text-center border-full" width="15%">Beginning Balance</th>
        <th class="text-center border-full" width="15%">Debet</th>
        <th class="text-center border-full" width="15%">Credit</th>
        <th class="text-center border-full" width="15%">Ending Balance</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($data as $item)
        <tr>
          <td class="text-center border-full">{{ $item[0] }}</td>
          <td class="text-center border-full">{{ $item[1] }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item[2], 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item[3], 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item[4], 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item[5], 2, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>

</html>
