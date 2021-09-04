@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product.index') }}">Product</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Product</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">SKU</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Product Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Images</label>
      <div class="col-md-7">
        <div class="form-material">
          @foreach ($product->images as $img)
          <a href="{{ $img->image_url }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
            <img src="{{ $img->image_url }}" class="img-fluid img-show-small">
          </a>
          @endforeach
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Brand</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.brand_reference.show', $product->brand_reference_id) }}">
            {{ $product->brand_reference->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Sub Brand</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.sub_brand_reference.show', $product->sub_brand_reference_id) }}">
            {{ $product->sub_brand_reference->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Unit</label>
      <div class="col-md-2">
        <div class="form-control-plaintext">
          {{ $product->quantity }} 
          <a href="{{ route('superuser.master.unit.show', $product->unit_id) }}">
            {{ $product->unit->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Product Category</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.product_category.show', $product->category_id) }}">
            {{ $product->category->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Product Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.product_type.show', $product->type_id) }}">
            {{ $product->type->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Non Stock</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->non_stock == '1' ? 'YES' : 'NO' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->status() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="javascript:history.back()">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($product->status != $product::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.product.destroy', $product->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.product.edit', $product->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Min Stock ({{ $product->min_stocks->count() }})</h3>

    <a href="{{ route('superuser.master.product.min_stock.create', [$product->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Warehouse</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Unit</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($product->min_stocks as $min_stock)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $min_stock->warehouse->name }}</td>
          <td class="text-center">{{ $min_stock->quantity }}</td>
          <td class="text-center">{{ $min_stock->unit->name }}</td>
          <td class="text-center">
            <a href="{{ route('superuser.master.product.min_stock.edit', [$product->id, $min_stock->id]) }}">
              <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit">
                <i class="fa fa-pencil"></i>
              </button>
            </a>
            <a href="javascript:deleteConfirmation('{{ route('superuser.master.product.min_stock.destroy', [$product->id, $min_stock->id]) }}')">
              <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-times"></i>
              </button>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable()

    $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
  })
</script>
@endpush
