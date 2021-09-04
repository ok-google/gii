@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Purchase Order (PPB)</span>
  <span class="breadcrumb-item">{{ $purchase_order->code }}</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Purchase Order (PPB)</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">PPB Number</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Supplier</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->supplier->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Transaction Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->transaction_type() }}</div>
      </div>
    </div>
    @if ($purchase_order->coa_id)
    <div class="row">
      <label class="col-md-3 col-form-label text-right">COA Cash</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->coa->name }}</div>
      </div>
    </div>  
    @endif
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kurs (IDR)</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->kurs ? $purchase_order->price_format($purchase_order->kurs) : '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Tax</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->tax ? $purchase_order->tax.'%' : '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Sea Freight</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->ekspedisi_sea_freight->name ?? '' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Local Freight</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->ekspedisi_local_freight->name ?? '' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->status() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="javascript:history.back()">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Product ({{ $purchase_order->details->count() }})</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive table-sm display nowrap">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Order Date</th>
          <th class="text-center">SKU</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Unit Price (RMB)</th>
          <th class="text-center">Local Freight Cost (RMB)</th>
          <th class="text-center">Total Price (RMB)</th>
          <th class="text-center">Kurs (IDR)</th>
          <th class="text-center">Sea Freight (IDR)</th>
          <th class="text-center">Local Freight (IDR)</th>
          <th class="text-center">Total Price (IDR)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase_order->details as $detail)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $detail->order_date ? date('d/m/Y', strtotime($detail->order_date)) : '-' }}</td>
          <td class="text-center">{{ $detail->product->code }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->quantity) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->unit_price) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->local_freight_cost) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->total_price_rmb) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->kurs) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->sea_freight) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->local_freight) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->total_price_idr) }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="6" class="text-right">Total RMB:</th>
          <th class="text-center"></th>
          <th colspan="3" class="text-right">Total IDR:</th>
          <th class="text-center"></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({
      scrollX: true,
      "footerCallback": function ( row, data, start, end, display ) {
          var api = this.api(), data;

          // Remove the formatting to get integer data for summation
          var intVal = function ( i ) {
              return typeof i === 'string' ?
                  i.replace(/[\$,]/g, '')*1 :
                  typeof i === 'number' ?
                      i : 0;
          };

          var numFormat = $.fn.dataTable.render.number( '\,', '.', 2).display;

          // Total over all pages
          total = api
              .column( 6 )
              .data()
              .reduce( function (a, b) {
                  return intVal(a) + intVal(b);
              }, 0 );

          // Update footer
          $( api.column( 6 ).footer() ).html(
            numFormat(total)
          );

          // Total over all pages
          total_idr = api
              .column( 10 )
              .data()
              .reduce( function (a, b) {
                  return intVal(a) + intVal(b);
              }, 0 );

          // Update footer
          $( api.column( 10 ).footer() ).html(
            numFormat(total_idr)
          );
      },
      "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    })

    $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
  })
</script>
@endpush
