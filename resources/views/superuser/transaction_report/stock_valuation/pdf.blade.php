<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        SV-{{ \Carbon\Carbon::parse($from_date)->isoFormat('DDMMYY') }}/{{ \Carbon\Carbon::parse($to_date)->isoFormat('DDMMYY') }}
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
            <td class="text-center text-bold" style="font-size: 1.3em;">Stock Valuation Report</td>
        </tr>
    </table>

    <table class="mt-25">
        <tr>
            <td width="30%">Category : {{ $category }}</td>
            <td width="30%">Warehouse : {{ $warehouse }}</td>
            <td class="text-right" width="40%">Period : {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table id="body" class="mt-10" style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="text-center border-full">SKU</th>
                <th class="text-center border-full">Opening Qty</th>
                <th class="text-center border-full">Opening Balance</th>
                <th class="text-center border-full">Purchase Qty</th>
                <th class="text-center border-full">Total Purchase</th>
                <th class="text-center border-full">Receiving Qty</th>
                <th class="text-center border-full">Total Receiving</th>
                <th class="text-center border-full">Sale Qty</th>
                <th class="text-center border-full">Total Sale</th>
                <th class="text-center border-full">Return Qty</th>
                <th class="text-center border-full">Total Return</th>
                <th class="text-center border-full">Closing Qty</th>
                <th class="text-center border-full">Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lists as $data)
                <tr>
                    <td class="text-center border-full">{{ $data->sku }}</td>
                    <td class="text-center border-full">{{ $data->opening_qty }}</td>
                    <td class="text-center border-full">{{ 'Rp. ' . number_format($data->opening_balance, 2, ',', '.') }}</td>
                    <td class="text-center border-full">{{ $data->purchase_qty }}</td>
                    <td class="text-center border-full">{{ 'Rp. ' . number_format($data->total_purchase, 2, ',', '.') }}</td>
                    <td class="text-center border-full">{{ $data->receiving_qty }}</td>
                    <td class="text-center border-full">{{ 'Rp. ' . number_format($data->total_receiving, 2, ',', '.') }}</td>
                    <td class="text-center border-full">{{ $data->sale_qty }}</td>
                    <td class="text-center border-full">{{ 'Rp. ' . number_format($data->total_sale, 2, ',', '.') }}</td>
                    <td class="text-center border-full">{{ $data->return_qty }}</td>
                    <td class="text-center border-full">{{ 'Rp. ' . number_format($data->total_return, 2, ',', '.') }}</td>
                    <td class="text-center border-full">{{ $data->opening_qty + $data->receiving_qty - $data->sale_qty + $data->return_qty }}</td>
                    <td class="text-center border-full">
                        {{ 'Rp. ' . number_format($data->opening_balance + $data->total_receiving - $data->total_sale + $data->total_return, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
{{-- 
            <tr>
                <th class="text-right" colspan="7">TOTAL &nbsp;</th>
                <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('grand_total'), 2, ',', '.') }}</th>
                <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('total_paid'), 2, ',', '.') }}</th>
                <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('total_cost'), 2, ',', '.') }}</th>
                <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('unpaid'), 2, ',', '.') }}</th>
                <th class="text-center border-full">{{ 'Rp. ' . number_format(collect($sales_order)->sum('retur'), 2, ',', '.') }}</th>
            </tr> --}}

        </tbody>
    </table>

    <table class="mt-25">
        <tr>
            <td width="50%"></td>
            <td class="text-center" width="50%"><b>Stock Valuation Report |</b> Printed {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </td>
        </tr>
    </table>
</body>

</html>
