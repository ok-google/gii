<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $sales_order->code }}</title>
    @include('superuser.asset.css-pdf')
  </head>
  <body style="font-size: 0.7em;">
    <table>
      <tr>
        {{-- <td width="20%"><img width="150px" src="superuser_assets/media/master/company/IAX3P14R71QVM4UPNK.jpg"></td> --}}
        <td width="20%"><img width="150px" src="{{ $company->logo_url ?? img_holder() }}"></td>
        <td width="50%">
            <table>
                <tr>
                  <td class="text-bold">{{ $company->name ?? '' }}</td>
                </tr>
                <tr>
                  <td>{{ $company->address . ', ' . $company->text_provinsi . ', ' . $company->text_kota }}</td>
                </tr>
                <tr>
                  <td>No Telp : {{ $company->phone ?? '-' }} &nbsp;&nbsp;&nbsp;&nbsp; Email : {{ $company->email ?? '-' }}</td>
                </tr>
            </table>
        </td>
        <td width="30%">
          <table class="text-center">
            <tr>
              <td class="text-bold" style="font-size: 5em; text-decoration: underline;">SO</td>
            </tr>
            <tr>
              <td class="text-bold">{{ $sales_order->code }} | {{ Carbon\Carbon::parse($sales_order->created_at)->format('j-m-Y') }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <table class="mt-25">
      <tr>
        <td width="10%">
            Customer
        </td>
        <td width="1%">:</td>
        <td width="30%">
          @if ($sales_order->marketplace_order == $sales_order::MARKETPLACE_ORDER['Non Marketplace'])
              {{ $sales_order->customer->name }}
            @else
              {{ $sales_order->customer_marketplace }}
            @endif 
        </td>
        <td width="59%"></td>
      </tr>
      <tr style="vertical-align: top">
        <td width="10%">
          Address
        </td>
        <td width="1%">:</td>
        <td width="30%">
          @if ($sales_order->marketplace_order == $sales_order::MARKETPLACE_ORDER['Non Marketplace'])
            {{ $sales_order->customer->address }}
          @else
            {{ $sales_order->address_marketplace }}
          @endif
        </td>
        <td width="59%"></td>
      </tr>
      <tr>
        <td width="10%">
          Ekspedisi
        </td>
        <td width="1%">:</td>
        <td width="30%">
          @if ($sales_order->marketplace_order == $sales_order::MARKETPLACE_ORDER['Non Marketplace'])
            {{ $sales_order->ekspedisi->name }}
          @else
            {{ $sales_order->ekspedisi_marketplace }}
          @endif
        </td>
        <td width="59%"></td>
      </tr>
    </table>

    <table class="mt-10" style="border-collapse: collapse;">
      <tr class="text-bold text-center">
          <td class="border-full">SKU</td>
          <td class="border-full">Product</td>
          <td class="border-full">Qty</td>
          <td class="border-full">Price</td>
          <td class="border-full">Discount</td>
          <td class="border-full">Total</td>
      </tr>
      @foreach ($sales_order->sales_order_details as $detail)
        <tr class="text-center">
          <td class="border-full">{{ $detail->product->code }}</td>
          <td class="border-full">{{ $detail->product->name }}</td>
          <td class="border-full">{{ $detail->quantity }}</td>
          <td class="border-full">{{ $sales_order->price_format($detail->price) }}</td>
          <td class="border-full"></td>
          <td class="border-full">{{ $sales_order->price_format($detail->quantity * $detail->price) }}</td>
      </tr>
      @endforeach
      <tr class="text-bold text-center">
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td class="text-left">Sub Total</td>
          <td class="border-full">{{ $sales_order->price_format($sales_order->total) }}</td>
      </tr>
      <tr class="text-bold text-center">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td class="text-left">Tax</td>
        <td class="border-full">{{ $sales_order->price_format($sales_order->tax) }}</td>
      </tr>
      <tr class="text-bold text-center">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td class="text-left">Delivery Cost</td>
        <td class="border-full">{{ $sales_order->price_format($sales_order->shipping_fee) }}</td>
      </tr>
      <tr class="text-bold text-center">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td class="text-left">Grand Total</td>
        <td class="border-full">{{ $sales_order->price_format($sales_order->grand_total) }}</td>
      </tr>
    </table>

    <table class="mt-25">
        <tr>
            <td width="40%"></td>
            <td  width="45%" class="text-center">Customer</td>
            <td width="15%"></td>
        </tr>
    </table>
    <table class="mt-50">
        <tr>
            <td width="40%"></td>
            <td  width="45%" class="text-center">
              @if ($sales_order->marketplace_order == $sales_order::MARKETPLACE_ORDER['Non Marketplace'])
                {{ $sales_order->customer->name }}
              @else
                {{ $sales_order->customer_marketplace }}
              @endif  
            </td>
            <td width="15%"></td>
        </tr>
    </table>
  </body>
</html>
