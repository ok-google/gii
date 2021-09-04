@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product.index') }}">Product</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Product</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.product.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">SKU <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Product Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">
          Images
          <i class="fa fa-question-circle" data-toggle="popover" data-placement="left" title="Images" data-content="Standart size (200 x 200 px)"></i>
        </label>
        <div class="col-md-7">
          <input type="file" id="images" name="images[]" data-max-file-size="2000" accept="image/png, image/jpeg" multiple>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="brand_reference">Brand <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="brand_reference" name="brand_reference" data-placeholder="Select Brand">
            <option></option>
            @foreach($brand_references as $brand_reference)
            <option value="{{ $brand_reference->id }}">{{ $brand_reference->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="sub_brand_reference">Sub Brand <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="sub_brand_reference" name="sub_brand_reference" data-placeholder="Select Sub Brand">
            <option></option>
            @foreach($sub_brand_references as $sub_brand_reference)
            <option value="{{ $sub_brand_reference->id }}">{{ $sub_brand_reference->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Unit <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="0" step="0.0001">
        </div>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="unit" name="unit" data-placeholder="Select Unit">
            <option></option>
            @foreach($units as $unit)
            <option value="{{ $unit->id }}">{{ $unit->abbreviation }} / {{ $unit->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="category">Product Category <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="category" name="category" data-placeholder="Select Category">
            <option></option>
            @foreach($product_categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type">
            <option></option>
            @foreach($product_types as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="non_stock">Non Stock</label>
        <div class="col-md-7">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="non_stock" name="non_stock">
          </div>
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
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<div id="alert-block"></div>
@endsection

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    $('#images').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });
  })
</script>
@endpush
