@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Inventory</span>
  <span class="breadcrumb-item active">Mutation Display</span>
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
<form id="form" target="_blank" action="{{ route('superuser.inventory.mutation_display.json') }}" enctype="multipart/form-data" method="POST">
  @csrf
  <div class="block">
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-1 col-form-label text-left" for="period">Period :</label>
        <div class="col-md-3">
          <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"
                  aria-hidden="true"></i></span></div><input type="text" class="form-control pull-right" id="datesearch"
              name="datesearch" placeholder="Select period"
              value="{{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->format('d/m/Y') }}">
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.inventory.mutation_display.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Code</th>
          <th class="text-center">Warehouse From</th>
          <th class="text-center">Warehouse To</th>
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

@section('modal')

@endsection

@push('scripts')
@include('superuser.asset.plugin.daterangepicker')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.inventory.mutation_display.json') }}';

  $('#datesearch').daterangepicker({
    autoUpdateInput: false
  });

  $('#datesearch').data('daterangepicker').setStartDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
  $('#datesearch').data('daterangepicker').setEndDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');

  $('#datesearch').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    start_date = picker.startDate.format('YYYY-MM-DD');
    end_date = picker.endDate.format('YYYY-MM-DD');

    if (start_date && end_date) {
      let newDatatableUrl = datatableUrl + '?from=' + start_date + '&to=' + end_date;
      $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
    // alert('aa')
    }
  });
  
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
      {data: 'code'},
      {data: 'warehouse_from'},
      {data: 'warehouse_to'},
      {data: 'status'},
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
