@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item active">Master COA</span>
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
    <a href="{{ route('superuser.accounting.coa.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
    <a href="{{ route('superuser.accounting.coa.export') }}" class="ml-10">
      <button type="button" class="btn btn-outline-info min-width-125">Export</button>
    </a>

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage" style="display: none;">Manage</button>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Level 1</th>
          <th class="text-center">Level 2</th>
          <th class="text-center">Level 3</th>
          <th class="text-center">Group</th>
          <th class="text-center">Code</th>
          <th class="text-center">COA</th>
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

@include('superuser.component.modal-manage-purchase-order-detail', [
  'import_template_url' => route('superuser.accounting.coa.import_template'),
  'import_url' => route('superuser.accounting.coa.import'),
  // 'export_url' => route('superuser.accounting.coa.export')
])

@endsection

@push('plugin-styles')
<link rel="stylesheet" href="{{ url('https://cdn.datatables.net/rowgroup/1.1.2/css/rowGroup.dataTables.min.css') }}">
@endpush

@push('scripts')
<script src="{{ url('https://cdn.datatables.net/rowgroup/1.1.2/js/dataTables.rowGroup.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.accounting.coa.json') }}';

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
      {data: 'parent_level_1'},
      {data: 'parent_level_2'},
      {data: 'parent_level_3'},
      {data: 'group'},
      {data: 'code'},
      {data: 'name'},
      {data: 'action', orderable: false, searcable: false}
    ],
    // order: [[2, 'asc'], [1, 'asc'], [0, 'asc']],
    // rowGroup: {
    //     dataSrc: [ 2, 1, 0 ]
    // },
    // columnDefs: [ {
    //     targets: [ 0, 1, 2 ],
    //     visible: false
    // } ]
    // order: [["group", "asc"]],
    order: [ [3, 'asc']],
    rowGroup: {
      dataSrc: [ "group"]
    },
    columnDefs: [ {
        targets: [ 3],
        visible: false
    } ],
    pageLength: 20,
    lengthMenu: [
      [20, 50, 100],
      [20, 50, 100]
    ],
  });
});
</script>
@endpush
