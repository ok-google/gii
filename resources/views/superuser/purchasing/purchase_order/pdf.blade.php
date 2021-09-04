<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $purchase_order->code }}</title>
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
              <td class="text-bold" style="font-size: 5em; text-decoration: underline;">PPB</td>
            </tr>
            <tr>
              <td class="text-bold">{{ $purchase_order->code }} | {{ Carbon\Carbon::parse($purchase_order->created_at)->format('j-m-Y') }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <table class="mt-25">
        <tr>
            <td width="40%">
                <table>
                    <tr>
                        <td width="20%">Supplier</td>
                        <td width="80%">: {{ $purchase_order->supplier->name }}</td>
                    </tr>
                    <tr>
                        <td width="20%">Address</td>
                        <td width="80%">: {{ $purchase_order->supplier->address ?? ''}}{{ $purchase_order->supplier->text_province ? ', '. $purchase_order->supplier->text_province : ''}}{{ $purchase_order->supplier->text_kota ? ', '. $purchase_order->supplier->text_kota : ''}}</td>
                    </tr>
                </table>
            </td>
            <td width="30%">
                <table>
                    <tr>
                        <td width="25%" class="text-right">Sea Freight</td>
                        <td width="75%">: {{ $purchase_order->ekspedisi_sea_freight->name ?? '-'}}</td>
                    </tr>
                    <tr>
                        <td width="25%" class="text-right">Local Freight</td>
                        <td width="75%">: {{ $purchase_order->ekspedisi_local_freight->name ?? '-'}}</td>
                    </tr>
                </table>
            </td> 
            <td width="30%">
                <table>
                    <tr>
                        <td width="20%" class="text-right">Payment</td>
                        <td width="80%">: {{ $purchase_order->transaction_type == '1' ? 'Cash' : 'Tempo'}}</td>
                    </tr>
                    <tr>
                        <td width="20%" class="text-right">IDR Rate</td>
                        <td width="80%">: {{ $purchase_order->price_format($purchase_order->kurs) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="mt-10" style="border-collapse: collapse;">
        <tr class="text-bold text-center">
            <td class="border-full">SKU</td>
            <td class="border-full">Product</td>
            <td class="border-full">Qty</td>
            <td class="border-full">Unit Price</td>
            <td class="border-full">Freight Cost (RMB)</td>
            <td class="border-full">Total Price (RMB)</td>
            <td class="border-full">Kurs (IDR)</td>
            <td class="border-full">Sea Freight (IDR)</td>
            <td class="border-full">Local Freight (IDR)</td>
            <td class="border-full">Tax</td>
            <td class="border-full">Total Price (IDR)</td>
        </tr>
        @foreach ($purchase_order->details as $detail)
          <tr class="text-center">
            <td class="border-full">{{ $detail->product->code }}</td>
            <td class="border-full">{{ $detail->product->name }}</td>
            <td class="border-full">{{ $detail->quantity }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->unit_price) }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->local_freight_cost) }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->total_price_rmb) }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->kurs) }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->sea_freight) }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->local_freight) }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->total_tax) }}</td>
            <td class="border-full">{{ $purchase_order->price_format($detail->total_price_idr) }}</td>
        </tr>
        @endforeach
        <tr class="text-bold text-center">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right">Grand Total (RMB)</td>
            <td class="border-full">{{ $purchase_order->price_format($purchase_order->grand_total_rmb) }}</td>
            <td></td>
            <td></td>
            <td class="text-right" colspan="2">Grand Total (IDR)</td>
            <td class="border-full">{{ $purchase_order->price_format($purchase_order->grand_total_idr) }}</td>
        </tr>
    </table>

    <table class="mt-25">
        <tr>
            <td width="40%"></td>
            <td  width="22.5%" class="text-center">Creator</td>
            <td  width="22.5%" class="text-center">Acknowledge</td>
            <td width="15%"></td>
        </tr>
    </table>
    <table class="mt-50">
        <tr>
            <td width="40%"></td>
            <td  width="22.5%" class="text-center">{{ $purchase_order->createdBySuperuser() }}</td>
            <td  width="22.5%" class="text-center">{{ $purchase_order->accBySuperuser() }}</td>
            <td width="15%"></td>
        </tr>
    </table>

    {{-- <table class="border-full mt-25">
      <tr>
        <td class="text-center">DETAIL</td>
      </tr>
    </table>

    <table class="border-left border-bottom border-right">
      <tr>
        <td width="50%" class="border-right">
          <table>
            <tr>
              <td class="text-bold">Gaji Pokok</td>
              <td class="text-bold">{{ rupiah($data[5]) }}</td>
            </tr>
            <tr>
              <td>{!! ($data[6] > 0) ? 'Tunjangan Transport' : '&nbsp;' !!}</td>
              <td>{!! ($data[6] > 0) ? rupiah($data[6]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>{!! ($data[7] > 0) ? 'Tunjangan Kehadiran' : '&nbsp;' !!}</td>
              <td>{!! ($data[7] > 0) ? rupiah($data[7]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>{!! ($data[9] > 0) ? 'Tunjangan Jabatan / Keahlian' : '&nbsp;' !!}</td>
              <td>{!! ($data[9] > 0) ? rupiah($data[9]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>{!! ($data[8] > 0) ? 'Tunjangan Performance' : '&nbsp;' !!}</td>
              <td>{!! ($data[8] > 0) ? rupiah($data[8]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>{!! ($data[16] > 0) ? 'On Call' : '&nbsp;' !!}</td>
              <td>{!! ($data[16] > 0) ? rupiah($data[16]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>{!! ($data[20] > 0) ? 'Pengembalian SP' : '&nbsp;' !!}</td>
              <td>{!! ($data[20] > 0) ? rupiah($data[20]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>{!! ($data[11] > 0) ? 'Koreksi' : '&nbsp;' !!}</td>
              <td>{!! ($data[11] > 0) ? rupiah($data[11]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </table>
        </td>
        <td width="50%">
          <table>
            <tr>
              <td class="text-bold">Lembur</td>
              <td>{!! ($data[13] > 0) ? 'L1' : '&nbsp;' !!}</td>
              <td>{!! ($data[13] > 0) ? rupiah($data[13]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>{!! ($data[14] > 0) ? 'L2' : '&nbsp;' !!}</td>
              <td>{!! ($data[14] > 0) ? rupiah($data[14]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>{!! ($data[15] > 0) ? 'L3' : '&nbsp;' !!}</td>
              <td>{!! ($data[15] > 0) ? rupiah($data[15]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>BPJS Kesehatan</td>
              <td>&nbsp;</td>
              <td>{{ rupiah($data[19]) }}</td>
            </tr>
            <tr>
              <td>BPJS JHT</td>
              <td>&nbsp;</td>
              <td>{{ rupiah($data[17]) }}</td>
            </tr>
            <tr>
              <td>BPJS Pensiun</td>
              <td>&nbsp;</td>
              <td>{{ rupiah($data[18]) }}</td>
            </tr>
            <tr>
              <td>{!! ($data[10] > 0) ? 'Unpaid Leave / Absen' : '&nbsp;' !!}</td>
              <td>&nbsp;</td>
              <td>{!! ($data[10] > 0) ? rupiah($data[10]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>{!! ($data[12] > 0) ? 'Koreksi' : '&nbsp;' !!}</td>
              <td>&nbsp;</td>
              <td>{!! ($data[12] > 0) ? rupiah($data[12]) : '&nbsp;' !!}</td>
            </tr>
            <tr>
              <td>{!! ($data[21] > 0) ? 'Potongan SP' : '&nbsp;' !!}</td>
              <td>&nbsp;</td>
              <td>{!! ($data[21] > 0) ? rupiah($data[21]) : '&nbsp;' !!}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <table class="border-left border-bottom border-right">
      <tr>
        <td width="20%">
          Total Gross
        </td>
        <td width="80%">
          {{ rupiah($data[22]) }}
        </td>
      </tr>
      <tr>
        <td width="20%">
          Total Pengurangan
        </td>
        <td width="80%">
          {{ rupiah($data[23]) }}
        </td>
      </tr>
    </table>

    <table class="border-full mt-25">
      <tr>
        <td width="20%">
          GAJI NET
        </td>
        <td width="80%" class="text-bold">
          {{ rupiah($data[24]) }}
        </td>
      </tr>
      <tr>
        <td width="20%">
          TERBILANG
        </td>
        <td width="80%" class="text-italic">
          #{{ terbilang($data[24]) }}#
        </td>
      </tr>
    </table> --}}
  </body>
</html>
