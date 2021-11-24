@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Inventory</span>
  <span class="breadcrumb-item active">Stock Adjusment</span>
</nav>
@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif





<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.inventory.stock_adjusment.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Code</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.daterangepicker')

@push('scripts')
@include('superuser.inventory.stock_adjusment.js')
@endpush

@section('modal')

@include('superuser.component.modal-manage-stock-adjusment', [
  'import_template_url' => route('superuser.inventory.stock_adjusment.import_template'),
  'import_url' => route('superuser.inventory.stock_adjusment.import'),
  'export_url' => route('superuser.inventory.stock_adjusment.export')
])

@endsection