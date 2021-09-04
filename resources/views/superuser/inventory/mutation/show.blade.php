@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Inventory</span>
  <a class="breadcrumb-item" href="{{ route('superuser.inventory.mutation.index') }}">Mutation</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div id="alert-block"></div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Mutation</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $mutation->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $mutation->warehouse->name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="status">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $mutation->status() }}</div>
      </div>
    </div>
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.inventory.mutation.index') }}">
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
    <h3 class="block-title">Barcode</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Barcode</th>
          <th class="text-center">SKU</th>
          <th class="text-center">Product</th>
          <th class="text-center">Quantity</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($mutation->mutation_details as $detail)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $detail->receiving_detail_colly->code }}</td>
            <td>{{ $detail->receiving_detail_colly->receiving_detail->product->code }}</td>
            <td>{{ $detail->receiving_detail_colly->receiving_detail->product->name }}</td>
            <td><input type="hidden" class="form-control" name="edit[]" value="old"><input type="hidden" class="form-control" name="id[]" value="{{ $detail->receiving_detail_colly->id }}">{{ $detail->receiving_detail_colly->quantity_mutation }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({})
  
  });
</script>
@endpush
