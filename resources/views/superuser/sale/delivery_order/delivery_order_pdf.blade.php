<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $delivery_order->code }}</title>
    @include('superuser.asset.css-pdf')
    <style>
    .page-break {
        page-break-after: always;
    }
    </style>
  </head>
  <body style="font-size: 0.7em;">
    @php
    $i = 1;    
    @endphp
    @foreach ($delivery_order->details as $detail)
      @php
      $i++;    
      $product_count = count($detail->sales_order->sales_order_details);
      @endphp
      <table>
        <tr>
          <td width="55%" style="vertical-align: top">
            <table>
              <tr>
                <td class="text-bold">{{ $detail->sales_order->store_name }}</td>
              </tr>
              <tr>
                <td>{{ $detail->sales_order->store_phone }}</td>
              </tr>
              <tr>
                <td class="text-bold">{{ $detail->code }}</td>
              </tr>
              <tr>
                <td class="text-bold">{{ $detail->delivery_order->code }}</td>
              </tr>
            </table>
          </td>
          <td width="45%">
            <table style="border-collapse: collapse;table-layout: fixed">
              <tr>
                <td class="border-full text-center text-bold" style="white-space: nowrap; overflow: hidden;" width="30%" colspan="2">
                  @if ($detail->sales_order->marketplace_order == $detail->sales_order::MARKETPLACE_ORDER['Non Marketplace'])
                    {{ $detail->sales_order->ekspedisi->name }}
                  @else
                    {{ $detail->sales_order->ekspedisi_marketplace }}
                  @endif
                </td>
              </tr>
              <tr>
                <td class="border-full text-right" width="15%">
                  Berat :
                </td>
                <td class="border-full text-left" width="15%">
                  &nbsp; {{ $detail->sales_order->weight ?? '-' }}
                </td>
              </tr>
              <tr>
                <td class="border-full text-right" width="15%">
                  COD :
                </td>
                <td class="border-full text-left" width="15%">
                  &nbsp; -
                </td>
              </tr>
              <tr>
                <td class="border-full text-right" width="15%">
                  Discount :
                </td>
                <td class="border-full text-left" width="15%">
                  &nbsp; {{ $detail->sales_order->discout ?? '-' }}
                </td>
              </tr>
              <tr>
                <td class="border-full text-right" width="15%">
                  Batas Kirim :
                </td>
                <td class="border-full text-left" style="padding-left:7px;" width="15%">
                  {{ $detail->sales_order->batas_kirim ? date('d-m-Y | H:i', strtotime($detail->sales_order->batas_kirim)) : '-' }}
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <table class="text-center">
        <tr>
          <td>
            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG( $detail->sales_order->resi, 'C128',strlen($detail->sales_order->resi) > 16 ? 1.4 : 2,37,array(1,1,1))}}" alt="barcode"/>
          </td>
        </tr>
        <tr>
          <td class="text-bold">{{ $detail->sales_order->resi }}</td>
        </tr>
      </table>
      <table class="text-center">
        <tr>
          <td>
            <img src="data:image/png;base64,{{DNS1D::getBarcodePNG( $detail->sales_order->code, 'C128',1,37)}}" alt="barcode"/>
          </td>
        </tr>
        <tr>
          <td class="text-bold">{{ $detail->sales_order->code }}</td>
        </tr>
      </table>

      <section class="mt-10">
        <div class="border-full" style="overflow: hidden; height: 41px; padding-left: 2px; padding-right: 2px;">
          <b>Penerima :</b> 
          @if ($detail->sales_order->marketplace_order == $detail->sales_order::MARKETPLACE_ORDER['Non Marketplace'])
            {{ $detail->sales_order->customer->name }}
          @else
            {{ $detail->sales_order->customer_marketplace }}
          @endif
          @if ($detail->sales_order->no_hp_marketplace)
            [ {{ $detail->sales_order->no_hp_marketplace }} ]
          @endif
          <br>
          @if ($detail->sales_order->marketplace_order == $detail->sales_order::MARKETPLACE_ORDER['Non Marketplace'])
            {{ $detail->sales_order->customer->address }}
          @else
            {{ $detail->sales_order->address_marketplace }}
          @endif
        </div>
      </section>

      <table class="mt-10" style="border-collapse: collapse;table-layout: fixed;width: 100%;white-space: nowrap;">
        <tr class="text-bold text-center">
            <td class="border-full" style="width: max-content;">SKU</td>
            <td class="border-full" style="">Product</td>
            <td class="border-full" style="width: 10%;">Qty</td>
        </tr>
        @php
        $l = 0;   
        @endphp
        @foreach ($detail->sales_order->sales_order_details as $sales_order_detail)
          <tr>
            <td class="border-full text-left" style="white-space: nowrap;{{ strlen($sales_order_detail->product->code) > 22 ? 'font-size: 0.8em;': '' }}">{{ $sales_order_detail->product->code }}</td>
            <td class="border-full text-left" style="white-space: nowrap; overflow: hidden;{{ strlen($sales_order_detail->product->name) > 25 ? 'font-size: 0.8em;': '' }}">{{ $sales_order_detail->product->name }}</td>
            <td class="border-full text-center">{{ $sales_order_detail->quantity }}</td>
          </tr>
          @php
          $l++;     
          @endphp
          @if ($product_count > 10)
            @if ($l == 12)
                @break
            @endif
          @endif
        @endforeach
      </table>

      @if ($product_count <= 10)
        <section class="mt-10">
          <div class="border-full" style="overflow: hidden; height: 27px; padding-left: 2px; padding-right: 2px;">
            <b>Note :</b> {{ $detail->sales_order->description ?? '' }}
          </div>
        </section>
        <section>
          <div style="position: fixed; bottom: 10px; left: 300px">
            1 of 1
          </div>
        </section>
      @else
        <section>
          <div style="position: fixed; bottom: 10px; left: 300px">
            1 of 2
          </div>
        </section>
      @endif
      
      @if ($product_count > 10)
        <div class="page-break"></div>

        <table>
          <tr>
            <td width="50%" style="vertical-align: top">
              <table>
                <tr>
                  <td class="text-bold">{{ $detail->sales_order->store_name }}</td>
                </tr>
                <tr>
                  <td>{{ $detail->sales_order->store_phone }}</td>
                </tr>
              </table>
            </td>
            <td width="50%">
              <table>
                <tr>
                  <td>{{ $detail->sales_order->resi }}</td>
                </tr>
                <tr>
                  <td>{{ $detail->sales_order->code }}</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <table class="mt-10" style="border-collapse: collapse;table-layout: fixed;width: 100%;white-space: nowrap;">
          <tr class="text-bold text-center">
              <td class="border-full" style="width: max-content;">SKU</td>
              <td class="border-full" style="">Product</td>
              <td class="border-full" style="width: 10%;">Qty</td>
          </tr>
          <tr class="text-bold text-center">
            <td class="" style="height: 5px"></td>
            <td class=""></td>
            <td class=""></td>
          </tr>
          @php
          $p = 0;   
          @endphp
          @foreach ($detail->sales_order->sales_order_details as $sales_order_detail)
            @php
            $p++;     
            @endphp
            @if ($p > 12)
              <tr>
                <td class="border-full text-left" style="white-space: nowrap;{{ strlen($sales_order_detail->product->code) > 22 ? 'font-size: 0.8em;': '' }}">{{ $sales_order_detail->product->code }}</td>
                <td class="border-full text-left" style="white-space: nowrap; overflow: hidden;{{ strlen($sales_order_detail->product->name) > 25 ? 'font-size: 0.8em;': '' }}">{{ $sales_order_detail->product->name }}</td>
                <td class="border-full text-center">{{ $sales_order_detail->quantity }}</td>
              </tr>
            @endif
          @endforeach
        </table>

        <section class="mt-10">
          <div class="border-full" style="overflow: hidden; height: 27px; padding-left: 2px; padding-right: 2px;">
            <b>Note :</b> {{ $detail->sales_order->description ?? '' }}
          </div>
        </section>
        <section>
          <div style="position: fixed; bottom: 10px; left: 300px">
            2 of 2
          </div>
        </section>

      @endif
      
      @if ($i <= count($delivery_order->details))
        <div class="page-break"></div>
      @endif
    @endforeach

  </body>
</html>
