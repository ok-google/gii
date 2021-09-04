@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.inventory.product_conversion.index') }}">Product Conversion</a>
    <span class="breadcrumb-item active">Show</span>
  </nav>
  <div id="alert-block"></div>

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Show Product Conversion</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $product_conversion->code }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $product_conversion->warehouse->name }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="status">Status</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $product_conversion->status() }}</div>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.inventory.product_conversion.index') }}">
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
      <h3 class="block-title">Product</h3>
    </div>
    <div class="block-content">
      <table id="datatable" class="table table-striped table-vcenter table-responsive">
        <thead>
          <tr>
            <th class="text-center">#</th>
            <th class="text-center">SKU</th>
            <th class="text-center">CONVERT TO</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($product_conversion->details as $detail)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $detail->product_from_rel->code }}</td>
              <td>{{ $detail->product_to_rel->code }}</td>
              <td>{{ $detail->qty }}</td>
              <td>{{ $detail->description }}</td>
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
