@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item active">Purchase Order (PPB)</span>
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
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.purchasing.purchase_order.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">New</button>
    </a>

    {{-- <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button> --}}
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Latest Update</th>
          <th class="text-center">Edit Counter</th>
          <th class="text-center">PPB Number</th>
          <th class="text-center">Grand Total (RMB)</th>
          <th class="text-center">Grand Total (IDR)</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

{{-- @include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.warehouse.import_template'),
  'import_url' => route('superuser.master.warehouse.import'),
  'export_url' => route('superuser.master.warehouse.export')
]) --}}

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.purchasing.purchase_order.json') }}';

  $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'id'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'updated_by'},
      {data: 'edit_counter'},
      {data: 'code'},
      {data: 'grand_total_rmb'},
      {data: 'grand_total_idr'},
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [1, 'desc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ],
    "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });
});
</script>
@endpush
