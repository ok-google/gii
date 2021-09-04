@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.sale.buy_back.index') }}">Buy Back</a>
  <span class="breadcrumb-item active">Show</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Buy Back</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $buy_back->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right">Invoice</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $buy_back->sales_order->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="coa">Inventory</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $buy_back->warehouse->name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="supplier">Is Disposal?</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $buy_back->disposal == 0 ? 'No' : 'Yes' }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="status">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $buy_back->status() }}</div>
      </div>
    </div>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Counter</th>
          <th class="text-center">SKU</th>
          <th class="text-center">Product Name</th>
          <th class="text-center">Sell Price</th>
          <th class="text-center">Buy Back Price</th>
          <th class="text-center">Buy Back Qty</th>
          <th class="text-center">Buy Back Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($buy_back->details as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><span>{{ $item->sales_order_detail->product->code }}</span></td>
              <td><span>{{ $item->sales_order_detail->product->name }}</span></td>
              <td><span>Rp. {{ number_format( $item->sales_order_detail->price, 2, ".", ",") }}</span></td>
              <td><span>Rp. {{ number_format( $item->buy_back_price, 2, ".", ",") }}</span></td>
              <td><span>{{ $item->buy_back_qty}}</span></td>
              <td><span>Rp. {{ number_format( $item->buy_back_total, 2, ".", ",") }}</span></td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
  <div class="block-content">
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.sale.buy_back.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
  
    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
        ],
        'order' : [[0,'desc']]
    })

  })
</script>
@endpush
