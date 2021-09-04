<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
        DP-{{ \Carbon\Carbon::parse($from_date)->isoFormat('DDMMYY') }}/{{ \Carbon\Carbon::parse($to_date)->isoFormat('DDMMYY') }}
    </title>
    @include('superuser.asset.css-pdf')
</head>

<body style="font-size: 0.7em;">
    <table>
        <tr>
            <td class="text-center text-bold" style="font-size: 1.3em;">Delivery Progress</td>
        </tr>
    </table>

    <table class="mt-25">
        <tr>
            <td width="25%">Marketplace : {{ $marketplace_text }}</td>
            <td width="25%">Store : {{ $store_text }}</td>
            <td width="25%">Status : {{ $status }}</td>
            <td class="text-right" width="25%">Period : {{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="mt-10" style="border-collapse: collapse;">
        <thead>
            <tr>
                <th class="text-center border-full">Create Date</th>
                <th class="text-center border-full">Shop</th>
                <th class="text-center border-full">Invoice No</th>
                <th class="text-center border-full">No Pack</th>
                <th class="text-center border-full">AWB</th>
                <th class="text-center border-full">Item Qty</th>
                <th class="text-center border-full">Order Date</th>
                <th class="text-center border-full">Approved Date</th>
                <th class="text-center border-full">Packing Date</th>
                <th class="text-center border-full">DO Validation</th>
                <th class="text-center border-full">Return</th>
                <th class="text-center border-full">User</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales_order as $data)
                <tr>
                    <td class="text-center border-full">{{ \Carbon\Carbon::parse($data->create_date)->format('d/m/Y H:i') }}</td>
                    <td class="text-center border-full">{{ $data->store_name }}</td>
                    <td class="text-center border-full">{{ $data->code }}</td>
                    <td class="text-center border-full">{{ $data->no_pack ? $data->no_pack : '-' }}</td>
                    <td class="text-center border-full">{{ $data->resi }}</td>
                    <td class="text-center border-full">{{ $data->quantity }}</td>
                    <td class="text-center border-full">{{ $data->order_date ? \Carbon\Carbon::parse($data->order_date)->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center border-full">{{ $data->approved_date ? \Carbon\Carbon::parse($data->approved_date)->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center border-full">{{ $data->packing_date ? \Carbon\Carbon::parse($data->packing_date)->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center border-full">{{ $data->do_validation_date ? \Carbon\Carbon::parse($data->do_validation_date)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="text-center border-full">{{ $data->return_date ? \Carbon\Carbon::parse($data->return_date)->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center border-full">{{ $data->scan_by }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="mt-25">
      <tr>
        <td width="50%"></td>
        <td class="text-center" width="50%"><b>Delivery Progress |</b> Printed {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </td>
      </tr>
    </table>
</body>

</html>
