<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    PR-{{ \Carbon\Carbon::parse($from_date)->isoFormat('DDMMYY') }}/{{ \Carbon\Carbon::parse($to_date)->isoFormat('DDMMYY') }}
  </title>
  @include('superuser.asset.css-pdf')
</head>

<body style="font-size: 0.7em;">
  <table>
    <tr>
      <td class="text-center text-bold" style="font-size: 1.3em;">Purchase Report</td>
    </tr>
  </table>

  <table class="mt-25">
    <tr>
      <td width="30%">Supplier : {{ $supplier_text }}</td>
      <td width="30%">Status : {{ $status_text }}</td>
      <td class="text-right" width="40%">Period : {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} -
        {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
      </td>
    </tr>
  </table>

  <table class="mt-10" style="border-collapse: collapse;">
    <thead>
      <tr>
        <th class="text-center border-full" width="15%">Date</th>
        <th class="text-center border-full" width="15%">Supplier</th>
        <th class="text-center border-full" width="25%">Invoice No</th>
        <th class="text-center border-full" width="15%">Debt</th>
        <th class="text-center border-full" width="15%">Payment</th>
        <th class="text-center border-full" width="15%">Outstanding Debt</th>
      </tr>
    </thead>
    <tbody>
      @php
      $a = 0;
      $b = 0;
      $c = 0;
      @endphp
      @foreach ($purchase_order as $item)

        @if ($request->status == 'paid')
          @if ($item->grand_total_idr > $item->total_paid)
            @continue
          @endif
        @elseif ($request->status == 'debt')
          @if ($item->grand_total_idr <= $item->total_paid)
            @continue
          @endif
        @endif

        @php
        $a = $a + $item->grand_total_idr;
        $b = $b + $item->total_paid($item->id);
        $c = $c + $item->grand_total_idr - $item->total_paid($item->id);
        @endphp
        <tr>
          <td class="text-center border-full">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}</td>
          <td class="text-center border-full">{{ $item->supplier->name }}</td>
          <td class="text-center border-full">{{ $item->code }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item->grand_total_idr, 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item->total_paid($item->id), 2, ',', '.') }}
          </td>
          <td class="text-center border-full">
            {{ 'Rp. ' . number_format($item->grand_total_idr - $item->total_paid($item->id), 2, ',', '.') }}
          </td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th class="text-right" colspan="3">TOTAL &nbsp;</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format($a, 2, ',', '.') }}</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format($b, 2, ',', '.') }}</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format($c, 2, ',', '.') }}</th>
      </tr>
    </tfoot>
  </table>

  <table class="mt-25">
    <tr>
      <td width="50%"></td>
      <td class="text-center" width="50%"><b>Purchase Report |</b> Printed {{ \Carbon\Carbon::now()->format('d/m/Y') }}
      </td>
    </tr>
  </table>
</body>

</html>
