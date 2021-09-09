<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        Receiving Report
    </title>
    @include('superuser.asset.css-pdf')
</head>

<body style="font-size: 0.7em;">
    <table>
        <tr>
            <td class="text-center text-bold" style="font-size: 1.3em;">Receiving Report</td>
        </tr>
    </table>

    <table class="mt-25">
        <tr>
            <td width="25%">Supplier : {{ $supplier }}</td>
            <td width="75%">SKU : {{ $sku }}</td>
        </tr>
    </table>

    <table class="mt-10" style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="text-center border-full">Supplier</th>
                <th class="text-center border-full">PPB No</th>
                <th class="text-center border-full">PBM No</th>
                <th class="text-center border-full">Notes</th>
                <th class="text-center border-full">SKU</th>
                <th class="text-center border-full" width="5%">PPB Qty</th>
                <th class="text-center border-full" width="5%">RI Qty</th>
                <th class="text-center border-full" width="5%">Incoming</th>
                <th class="text-center border-full" width="5%">Colly Qty</th>
                <th class="text-center border-full" width="7%">HPP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lists as $data)
                <tr>
                    <td class="text-center border-full">{{ $data->supplier }}</td>
                    <td class="text-center border-full">{{ $data->ppb }}</td>
                    <td class="text-center border-full">{{ $data->pbm }}</td>
                    <td class="text-center border-full">{{ $data->description }}</td>
                    <td class="text-center border-full">{{ $data->sku }}</td>
                    <td class="text-center border-full">{{ $data->ppb_qty }}</td>
                    <td class="text-center border-full">{{ $data->ri_qty }}</td>
                    <td class="text-center border-full">{{ $data->incoming }}</td>
                    <td class="text-center border-full">{{ $data->colly_qty }}</td>
                    <td class="text-center border-full">{{ 'Rp. ' . number_format($data->hpp, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="mt-25">
      <tr>
        <td width="50%"></td>
        <td class="text-center" width="50%"><b>Receiving Report |</b> Printed {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </td>
      </tr>
    </table>
</body>

</html>
