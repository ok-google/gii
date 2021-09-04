@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.inventory.mutation_display.index') }}">Mutation Display</a>
    <span class="breadcrumb-item active">Show</span>
  </nav>
  <div id="alert-block"></div>

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Show Mutation Display</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutation_display->code }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse_from">Warehouse From</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutation_display->warehouse_from_attribute->name }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse_to">Warehouse To</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutation_display->warehouse_to_attribute->name }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="status">Status</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutation_display->status() }}</div>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.inventory.mutation_display.index') }}">
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
            <th class="text-center">Product</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($mutation_display->details as $detail)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $detail->product->code }}</td>
              <td>{{ $detail->product->name }}</td>
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
