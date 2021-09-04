@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Inventory</span>
  <a class="breadcrumb-item" href="{{ route('superuser.inventory.stock_adjusment.index') }}">Stock Adjusment</a>
  <span class="breadcrumb-item active">Show</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Stock Adjusment</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $stock_adjusment->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="coa">Inventory</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $stock_adjusment->warehouse->name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="supplier">Minus</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $stock_adjusment->minus == 0 ? 'No' : 'Yes' }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="status">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $stock_adjusment->status() }}</div>
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
            <th class="text-center">Qty</th>
            <th class="text-center">Price</th>
            <th class="text-center">Total</th>
            <th class="text-center">Description</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($stock_adjusment->details as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><span>{{ $item->product->code }}</span></td>
              <td><span>{{ $item->product->name }}</span></td>
              <td><span>{{ $item->qty}}</span></td>
              <td><span>Rp. {{ number_format( $item->price, 2, ".", ",") }}</span></td>
              <td><span>Rp. {{ number_format( $item->total, 2, ".", ",") }}</span></td>
              <td><span>{{ $item->description}}</span></td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  
  <div class="block-content">
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.inventory.stock_adjusment.index') }}">
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
