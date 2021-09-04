<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $receiving->code }}</title>
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
                <tr>
                  <td>Note : {{ $receiving->description ?? '-' }}</td>
                </tr>
            </table>
        </td>
        <td width="30%">
          <table class="text-center">
            <tr>
              <td class="text-bold" style="font-size: 5em; text-decoration: underline;">RI</td>
            </tr>
            <tr>
              <td class="text-bold">{{ $receiving->code }} | {{ Carbon\Carbon::parse($receiving->created_at)->format('j-m-Y') }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table class="mt-10" style="border-collapse: collapse;">
        <tr class="text-bold text-center">
            <td class="border-full">Supplier</td>
            <td class="border-full">PPB No</td>
            <td class="border-full">SKU</td>
            <td class="border-full">Product</td>
            <td class="border-full">PPB Qty</td>
            <td class="border-full">RI Qty</td>
            <td class="border-full">Colly Qty</td>
        </tr>
        @foreach ($receiving->details as $detail)
          <tr class="text-center">
            <td class="border-full">{{ $detail->purchase_order->supplier->name }}</td>
            <td class="border-full">{{ $detail->purchase_order->code }}</td>
            <td class="border-full">{{ $detail->product->code }}</td>
            <td class="border-full">{{ $detail->product->name }}</td>
            <td class="border-full">{{ $detail->quantity }}</td>
            <td class="border-full">{{ $detail->total_quantity_ri }}</td>
            <td class="border-full">{{ $detail->total_quantity_colly }}</td>
        </tr>
        @endforeach
    </table>

    <table class="mt-25">
        <tr>
            <td width="40%">Note : {{ $receiving->description ?? '-' }}</td>
            <td  width="22.5%" class="text-center">Creator</td>
            <td  width="22.5%" class="text-center">Acknowledge</td>
            <td width="15%"></td>
        </tr>
    </table>
    <table class="mt-50">
        <tr>
            <td width="40%"></td>
            <td  width="22.5%" class="text-center">{{ $receiving->createdBySuperuser() }}</td>
            <td  width="22.5%" class="text-center">{{ $receiving->accBySuperuser() }}</td>
            <td width="15%"></td>
        </tr>
    </table>
  </body>
</html>
