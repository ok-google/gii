@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.supplier.index') }}">Supplier</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Supplier</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Provinsi</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->text_provinsi }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kota</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->text_kota }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kecamatan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->text_kecamatan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kelurahan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->text_kelurahan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Zipcode</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->zipcode }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Email</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->email }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Phone</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->phone }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Fax</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->fax }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Website</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->website }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Owner Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->owner_name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $supplier->status() }}</div>
      </div>
    </div>
    <hr>
    <div class="block-header">
      <h3 class="block-title">COA Setting</h3>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">HEAD OFFICE</label>
      <div class="col-md-7">
        @foreach ($coa_head_office as $item)
          @if ($item->id == $coa_head_office_selected)
            <div class="form-control-plaintext">{{ $item->code }} / {{ $item->name }}</div>
            @break
          @endif
        @endforeach
      </div>
    </div>
    @foreach ($branches as $branch)
    <div class="row">
      <label class="col-md-3 col-form-label text-right">{{ $branch->name }}</label>
      <div class="col-md-7">
        @foreach ($coa_branch[$branch->id] as $item)
          @if ($item->id == $coa_branch_selected[$branch->id])
            <div class="form-control-plaintext">{{ $item->code }} / {{ $item->name }}</div>
            @break
          @endif
        @endforeach
      </div>
    </div>
    @endforeach
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="javascript:history.back()">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($supplier->status != $supplier::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.supplier.destroy', $supplier->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.supplier.edit', $supplier->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable()
  })
</script>
@endpush
