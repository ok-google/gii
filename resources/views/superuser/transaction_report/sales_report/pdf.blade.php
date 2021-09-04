<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    SR-{{ \Carbon\Carbon::parse($from_date)->isoFormat('DDMMYY') }}/{{ \Carbon\Carbon::parse($to_date)->isoFormat('DDMMYY') }}
  </title>
  @include('superuser.asset.css-pdf')

  <style>
    table#body tr {
      page-break-inside: avoid;
    }
  </style>
</head>

<body style="font-size: 0.7em;">
  <table>
    <tr>
      <td class="text-center text-bold" style="font-size: 1.3em;">Sales Report</td>
    </tr>
  </table>

  <table class="mt-25">
    <tr>
      <td width="30%">Marketplace : {{ $marketplace_text }}</td>
      <td width="30%">Status : {{ $status_text }}</td>
      <td class="text-right" width="40%">Period : {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} -
        {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
      </td>
    </tr>
  </table>

  <table id="body" class="mt-10" style="border-collapse: collapse;">
    <thead>
      <tr>
        <th class="text-center border-full">Create Date</th>
        <th class="text-center border-full">Order Date</th>
        <th class="text-center border-full">MP Receipt Code</th>
        <th class="text-center border-full">Marketplace</th>
        <th class="text-center border-full">Store</th>
        <th class="text-center border-full">Customer</th>
        <th class="text-center border-full">Invoice No</th>
        <th class="text-center border-full">Receivable</th>
        <th class="text-center border-full">Paid</th>
        <th class="text-center border-full">Cost</th>
        <th class="text-center border-full">Unpaid</th>
        <th class="text-center border-full">Retur</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($sales_order as $item)
        <tr>
          <td class="text-center border-full">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}</td>
          <td class="text-center border-full">{{ $item->order_date ? \Carbon\Carbon::parse($item->order_date)->format('d/m/Y H:i:s') : '-' }}</td>
          <td class="text-center border-full">{{ $item->kode_pelunasan ?? '' }}</td>
          <td class="text-center border-full">{{ $item->marketplace_order() }}</td>
          <td class="text-center border-full">{{ $item->store_name ?? '' }}</td>
          <td class="text-center border-full">{{ $item->customer_name }}</td>
          <td class="text-center border-full">{{ $item->code }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item->grand_total, 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item->total_paid, 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item->total_cost, 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item->unpaid, 2, ',', '.') }}</td>
          <td class="text-center border-full">{{ 'Rp. ' . number_format($item->retur, 2, ',', '.') }}</td>
        </tr>
      @endforeach

      <tr>
        <th class="text-right" colspan="7">TOTAL &nbsp;</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('grand_total'), 2, ',', '.') }}</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('total_paid'), 2, ',', '.') }}</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('total_cost'), 2, ',', '.') }}</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('unpaid'), 2, ',', '.') }}</th>
        <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('retur'), 2, ',', '.') }}</th>
      </tr>

    </tbody>
  </table>

  <table class="mt-25">
    <tr>
      <td width="50%"></td>
      <td class="text-center" width="50%"><b>Sales Report |</b> Printed {{ \Carbon\Carbon::now()->format('d/m/Y') }}
      </td>
    </tr>
  </table>
</body>

</html>
