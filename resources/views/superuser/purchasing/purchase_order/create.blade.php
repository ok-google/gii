@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <a class="breadcrumb-item" href="{{ route('superuser.purchasing.purchase_order.index') }}">Purchase Order (PPB)</a>
  <span class="breadcrumb-item active">New</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">New Purchase Order (PPB)</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.purchasing.purchase_order.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">PPB Number <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="supplier">Supplier <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="supplier" name="supplier" data-placeholder="Select Supplier">
            <option></option>
            @foreach($suppliers as $supplier)
            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address</label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address"></textarea>
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
        <label class="col-md-3 col-form-label text-right" for="transaction_type">Transaction Type <span class="text-danger">*</span></label>
        <div class="col-md-7 text-right">
          <div class="col-md-3 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="transaction_type" id="transaction_type1" value="1">
            <label class="form-check-label" for="transaction_type1">Tunai</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="transaction_type" id="transaction_type0" value="0">
            <label class="form-check-label" for="transaction_type0">Non Tunai</label>
          </div>
        </div>
      </div>
      <div class="form-group row" id="coa" style="display: none">
        <label class="col-md-3 col-form-label text-right" for="coa">Select Cash <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="coa" name="coa" data-placeholder="Select Cash">
            <option></option>
            @foreach($coas as $coa)
            <option value="{{ $coa->id }}">{{ $coa->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="kurs">Kurs</label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="kurs" name="kurs">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="tax">Tax (%)</label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="tax" name="tax" min="0">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="sea_freight">Ekspedisi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="sea_freight" name="sea_freight" data-placeholder="Select Ekspedisi">
            <option></option>
            @foreach($ekspedisis as $ekspedisi)
            <option value="{{ $ekspedisi->id }}">{{ $ekspedisi->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="javascript:history.back()">
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

    $('input[name=transaction_type]').on('click', function () {
      if (this.value == 1) {
        $('#coa').slideDown()
        $('.js-select2').select2()
      } else {
        $('#coa').slideUp()
      }
    })
  })
</script>
@endpush
