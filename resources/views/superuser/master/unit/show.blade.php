@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.unit.index') }}">Unit</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Unit</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $unit->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Unit</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $unit->abbreviation }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $unit->status() }}</div>
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
      @if($unit->status != $unit::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.unit.destroy', $unit->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.unit.edit', $unit->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

{{-- <div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Customer ({{ $customer_category->customer->count() }})</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Code</th>
          <th class="text-center">Name</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($customer_category->customer as $customer)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $customer->code }}</td>
          <td class="text-center">{{ $customer->name }}</td>
          <td class="text-center">
            <a href="{{ route('superuser.master.customer.show', $customer->id) }}">
              <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                <i class="fa fa-eye"></i>
              </button>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div> --}}
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
