@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product.index') }}">Product</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Product</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.product.update', $product) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">SKU <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $product->code }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Product Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">
          Images
          <i class="fa fa-question-circle" data-toggle="popover" data-placement="left" title="Images" data-content="Standart size (200 x 200 px)"></i>
        </label>
        <div class="col-md-7">
          <input type="file" id="images" name="images[]" data-max-file-size="2000" accept="image/png, image/jpeg" multiple data-src="{{ $product->images_url }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="brand_reference">Brand <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="brand_reference" name="brand_reference" data-placeholder="Select Brand">
            <option></option>
            @foreach($brand_references as $brand_reference)
            <option value="{{ $brand_reference->id }}" {{ ($brand_reference->id == $product->brand_reference_id ) ? 'selected' : '' }}>{{ $brand_reference->name }}</option>
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
            <option value="{{ $sub_brand_reference->id }}" {{ ($sub_brand_reference->id == $product->sub_brand_reference_id ) ? 'selected' : '' }}>{{ $sub_brand_reference->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Unit <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="quantity" name="quantity" min="0" value="{{ $product->quantity }}" step="0.0001">
        </div>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="unit" name="unit" data-placeholder="Select Unit">
            <option></option>
            @foreach($units as $unit)
            <option value="{{ $unit->id }}" {{ ($unit->id == $product->unit_id ) ? 'selected' : '' }}>{{ $unit->abbreviation }} / {{ $unit->name }}</option>
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
            <option value="{{ $category->id }}" {{ ($category->id == $product->category_id ) ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Product Type <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type">
            <option></option>
            @foreach($product_types as $type)
            <option value="{{ $type->id }}" {{ ($type->id == $product->type_id ) ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="non_stock">Non Stock</label>
        <div class="col-md-7">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="non_stock" name="non_stock" @if ($product->non_stock == '1')
            checked              
          @endif>
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

@section('modal')
@if($product_image_trash->isNotEmpty())
<div class="modal fade" id="modal-trash" tabindex="-1" role="dialog" aria-labelledby="modal-trash" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-slideup" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Trash</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
              <i class="si si-close"></i>
            </button>
          </div>
        </div>
        <div class="block-content">
          <div class="row">
            @foreach($product_image_trash as $image_trash)
            <div class="col-md-3">
              <img class="img-fluid img-trash" src="{{ $image_trash->image_url }}">
              <div class="mt-10 mb-10 text-center">
                <a href="{{ route('superuser.master.product_img.restore', [$product->id, $image_trash->id]) }}">
                  <button type="button" class="btn btn-lg btn-circle btn-alt-warning" >
                    <i class="fa fa-undo"></i>
                  </button>
                </a>
                <a href="{{ route('superuser.master.product_img.destroy', [$product->id, $image_trash->id]) }}">
                  <button type="button" class="btn btn-lg btn-circle btn-alt-danger">
                    <i class="fa fa-trash"></i>
                  </button>
                </a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="{{ route('superuser.master.product_img.restore_all', $product->id) }}">
          <button type="button" class="btn btn-alt-warning">Restore All</button>
        </a>
        <a href="{{ route('superuser.master.product_img.destroy_all', $product->id) }}">
          <button type="button" class="btn btn-alt-danger">Remove All</button>
        </a>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    let ipc = [];
    let images = JSON.parse('{{ $product->images }}'.replace(/&quot;/g,'"'));

    images.forEach(img => {
      ipc.push({
        caption: img.image,
        key: img.id,
        url: base_url+ '/superuser/master/product_img/delete/' +img.id
      })
    });

    $('#images').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      overwriteInitial: false,
      browseLabel: '',
      removeLabel: '',
      initialPreview: $('#images').data('src'),
      initialPreviewAsData: true,
      fileActionSettings: {
        showDrag: false,
        showRemove: (ipc.length > 1) || false
      },
      initialPreviewConfig: ipc
    });

    // TODO: change style to swal / other
    // $("#images").on("filebeforedelete", function(e) {
    //   e.preventDefault();
    //   e.stopImmediatePropagation();
    
    //   var abort = true;
    
    //   Swal.fire({
    //     title: 'Are you sure you want to delete this image?',
    //     type: 'warning',
    //     showCancelButton: true,
    //     allowOutsideClick: false,
    //     allowEscapeKey: false,
    //     allowEnterKey: false,
    //     backdrop: false,
    //   }).then(result => {
    //     if (result.value) {
    //       abort = false;
    //     }
    //   }).then(() => { return abort });
    // });

    $('#images').on('filedeleted', function(event, key, jqXHR, data) {
      redirect('reload()');
    });

  })
</script>
@endpush
