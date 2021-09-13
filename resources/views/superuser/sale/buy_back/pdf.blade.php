<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $data->code }}</title>
    @include('superuser.asset.css-pdf')
    <style type="text/css">
        @font-face {
            font-family: 'Broadway';
            src: url('{{ asset('superuser_assets/fonts/broadway/BROADW.TTF') }}') format('truetype');
        }

        .table-body {
            border: 1px solid black;
            border-collapse: collapse;
        }

        .padding-between {
            padding-top: 8px;
            padding-bottom: 8px;
        }

    </style>
</head>

<body style="font-size: 0.7em;">
    <table class="table-body">
        <tr>
            <td>
                <table style="border-collapse: collapse">
                    <tr>
                        <td colspan="2" style="background: rgb(243, 199, 207); font-size: 20px;font-family: 'Broadway', sans-serif;" class="text-center">NOTA
                            RETUR
                            PENJUALAN</td>
                    </tr>
                    <tr>
                        <td width="60%" style="line-height: 16px;padding-top: 10px; padding-left: 40px; vertical-align: top;">
                            {{ $data->sales_order->code }}<br>
                            {{ $data->sales_order->order_date ? \Carbon\Carbon::parse($data->sales_order->order_date)->format('d/m/Y H:i') : '-' }}<br>
                            {{ $data->sales_order->customer_name() }}
                        </td>
                        <td style="line-height: 16px; padding-top: 10px; padding-bottom: 10px;">
                            {{ $data->code }}<br>
                            {{ \Carbon\Carbon::parse($data->acc_at)->format('d/m/Y H:i') }}<br>
                            {{ $data->warehouse->name }}<br>
                            DISPOSAL <span style="margin-left: 30px;">{{ $data->disposal ? 'YES' : 'NO' }}</span>
                        </td>
                    </tr>
                </table>
                <table style="border-collapse: collapse;text-align: center;margin-left: -1px;margin-right: -1px;">
                    <thead>
                        <tr>
                            <th class="border-full padding-between" width="3%">NO</th>
                            <th class="border-full padding-between">SKU</th>
                            <th class="border-full padding-between">PRODUCT</th>
                            <th class="border-full padding-between">SELL PRICE</th>
                            <th class="border-full padding-between">BUY BACK PRICE</th>
                            <th class="border-full padding-between">BUY BACK QTY</th>
                            <th class="border-full padding-between">BUY BACK TOTAL</th>
                            <th class="border-full padding-between" width="25%">DESCRIPTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->details as $item)
                            <tr>
                                <td class="border-full">{{ $loop->iteration }}.</td>
                                <td class="border-full">{{ $item->sales_order_detail->product->code }}</td>
                                <td class="border-full">{{ $item->sales_order_detail->product->name }}</td>
                                <td class="border-full">{{ $item->sales_order_detail->price }}</td>
                                <td class="border-full">{{ $item->buy_back_price }}</td>
                                <td class="border-full">{{ $item->buy_back_qty }}</td>
                                <td class="border-full">{{ $item->buy_back_total }}</td>
                                <td class="border-full"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table style="margin-top: 10px;text-align: center;">
                    <tr>
                        <td width="15%">Created by,</td>
                        <td width="15%">Approved by,</td>
                        <td width="30%"></td>
                        <td width="15%">Checked by,</td>
                        <td width="15%">Received by,</td>
                        <td width="10%"></td>
                    </tr>
                    <tr>
                        <td style="padding-top: 50px; padding-bottom: 20px">{{ $data->createdBySuperuser() }}</td>
                        <td colspan="4"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
