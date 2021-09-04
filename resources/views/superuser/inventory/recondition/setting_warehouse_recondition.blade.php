@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Setting Warehouse Recondition</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.inventory.recondition.store_setting') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Select a warehouse first before using the recondition menu </h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse" aria-describedby="warehouseHelp">
            <option></option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
          </select>
          <small id="warehouseHelp" class="form-text text-muted">Please note that these settings cannot be changed.</small>
        </div>
        
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Submit 
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()
  
  });
</script>
@endpush
