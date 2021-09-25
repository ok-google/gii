@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <a class="breadcrumb-item" href="{{ route('superuser.purchasing.receiving.index') }}">Receiving</a>
  <span class="breadcrumb-item active">New</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">New Receiving</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.purchasing.receiving.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
            <option></option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="pbm_date">PBM Date</span></label>
        <div class="col-md-7">
          <input type="date" class="form-control" id="pbm_date" name="pbm_date">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="no_container">No Container</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="no_container" name="no_container"></textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Note</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="note" name="note"></textarea>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.purchasing.receiving.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Next <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()
  })
</script>
@endpush
