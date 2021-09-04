@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Customer</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Customer</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.customer.update', $customer) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $customer->code }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $customer->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="store">Store <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="store" name="store" value="{{ $customer->store }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Image</label>
        <div class="col-md-7">
          <input type="file" id="image_store" name="image_store" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $customer->img_store }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address">{{ $customer->address }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type">
            <option></option>
            @foreach($customer_types as $type)
            <option value="{{ $type->id }}" data-grosir="{{ $type->grosir_address }}" {{ ($type->id == $customer->type_id) ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row" id="address-do" {{ ($customer->address_do == null) ? 'style=display:none' : '' }}>
        <label class="col-md-3 col-form-label text-right" for="address_do">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address_do" name="address_do">{{ $customer->address_do }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="category">Category <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="category" name="category" data-placeholder="Select Category">
            <option></option>
            @foreach($customer_categories as $category)
            <option value="{{ $category->id }}" {{ ($category->id == $customer->category_id) ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Provinsi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="provinsi" name="provinsi" data-placeholder="Select Provinsi" data-value="{{ $customer->provinsi }}">
            <option></option>
          </select>
          <input type="hidden" name="text_provinsi">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kota</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kota" name="kota" data-placeholder="Select Kota" data-value="{{ $customer->kota }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kota">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kecamatan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kecamatan" name="kecamatan" data-placeholder="Select Kecamatan" data-value="{{ $customer->kecamatan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kecamatan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kelurahan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kelurahan" name="kelurahan" data-placeholder="Select Kelurahan" data-value="{{ $customer->kelurahan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kelurahan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="zipcode">Zipcode</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="zipcode" name="zipcode" value="{{ $customer->zipcode }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="email">Email</label>
        <div class="col-md-6">
          <input type="email" class="form-control" id="email" name="email" value="{{ $customer->email }}">
        </div>
        <div class="col-md-1 text-center">
          <label class="css-control css-control-primary css-checkbox">
            <input type="checkbox" class="css-control-input" name="notification_email" {{ ($customer->notification_email) ? 'checked' : '' }}>
            <span class="css-control-indicator"></span>
          </label>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Phone</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone" value="{{ $customer->phone }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="fax">Fax</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="fax" name="fax" value="{{ $customer->fax }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="website">Website</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="website" name="website" value="{{ $customer->website }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="owner_name">Owner Name</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ $customer->owner_name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="plafon_piutang">Plafon Piutang <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="plafon_piutang" name="plafon_piutang" min="0" value="{{ $customer->plafon_piutang }}" step="0.0001">
        </div>
      </div>
      <hr>
      <div class="block-header">
        <h3 class="block-title text-center">COA Setting</h3>
      </div>
      <div class="form-group row">
        <div class="col-md-3"></div>
        <label class="col-md-3 col-form-label text-center">Debet</label>
        <div class="col-md-1"></div>
        <label class="col-md-3 col-form-label text-center">Credit</label>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">HEAD OFFICE</label>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="coa_head_office_id" name="coa_head_office_id" data-placeholder="Select COA">
            <option></option>
            @foreach ($coa_head_office as $item)
              <option value="{{ $item->id }}" {{ $item->id == $coa_head_office_selected ? 'selected' : '' }}>{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="coa_penjualan_head_office_id" name="coa_penjualan_head_office_id" data-placeholder="Select COA">
            <option></option>
            @foreach ($coa_head_office as $item)
              <option value="{{ $item->id }}" {{ $item->id == $coa_penjualan_head_office_selected ? 'selected' : '' }}>{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      @foreach ($branches as $branch)
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">{{ $branch->name }}</label>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="coa_branch_id[{{$branch->id}}]" name="coa_branch_id[]" data-placeholder="Select COA">
            <option></option>
            @foreach ($coa_branch[$branch->id] as $item)
              <option value="{{ $item->id }}" {{ $item->id == $coa_branch_selected[$branch->id] ? 'selected' : '' }}>{{ $item->name }}</option>
            @endforeach
          </select>
          <input type="hidden" name="branch_id[]" value="{{$branch->id}}">
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="coa_penjualan_branch_id[{{$branch->id}}]" name="coa_penjualan_branch_id[]" data-placeholder="Select COA">
            <option></option>
            @foreach ($coa_branch[$branch->id] as $item)
              <option value="{{ $item->id }}" {{ $item->id == $coa_penjualan_branch_selected[$branch->id] ? 'selected' : '' }}>{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      @endforeach
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
@include('superuser.asset.plugin.select2-chain-indonesian-teritory')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('#image_store').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      initialPreview: $('#image_store').data('src'),
      initialPreviewAsData: true,
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
      initialPreviewConfig: [
      {
          caption: '{{ $customer->image_store }}'
      }
    ]
    });

    $('.js-select2').select2()

    $('select[name=type]').on('select2:select', function () {
      if ( $('#type').find(':selected').data('grosir') == '1' ) {
        $('#address-do').slideDown()
      } else {
        $('#address-do').slideUp()
      }
    })

  })
</script>
@endpush
