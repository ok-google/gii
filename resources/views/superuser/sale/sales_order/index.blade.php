@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <span class="breadcrumb-item active">Sales Order</span>
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

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      <div class="alert alert-success alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Successful Import</h3>
        //@foreach (session()->get('collect_success') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
    <div class="col pr-0">
      <div class="alert alert-danger alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Failed Import</h3>
        @foreach (session()->get('collect_error') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

  <form id="form" target="_blank" action="{{ route('superuser.transaction_report.purchase_report.pdf') }}"
    enctype="multipart/form-data" method="POST">
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
    @if($superuser->can('sales order-create'))
    <a href="{{ route('superuser.sale.sales_order.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
    @endif
    <div class="pull-right">
      <label class="css-control css-control-primary css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="default" checked>
        <span class="css-control-indicator"></span> Default
      </label>
      <label class="css-control css-control-success css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="acc">
        <span class="css-control-indicator"></span> Approved
      </label>
      <label class="css-control css-control-warning css-radio">
        <input type="radio" class="css-control-input" name="show-control" value="all">
        <span class="css-control-indicator"></span> All
      </label>
    </div>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center"></th>
          <th class="text-center">Created at</th>
          <th class="text-center">Code</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Store</th>
          <th class="text-center">Ekspedisi</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
  <div class="block-content block-content-full">
    <form class="ajax" id="form_bulk_acc" data-action="{{ route('superuser.sale.sales_order.bulk_action') }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="bulk_acc_ids">
      <input type="hidden" name="bulk_type">
      @if($superuser->can('sales order-acc'))
      {{-- <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Bulk Action
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <a class="dropdown-item bulk-click" href="#" bulk-type="approve"> <i class="fa fa-check"></i> Approve</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item bulk-click" href="#" bulk-type="delete"><i class="fa fa-trash"></i> Delete</a>
        </div>
      </div> --}}
      <button type="button" class="btn bg-gd-corporate border-0 text-white bulk-click" id="approve2" bulk-type="approve">
        Approve <i class="fa fa-arrow-right ml-10"></i>
      </button>
      <button type="button" class="btn btn-danger border-0 text-white bulk-click" id="delete-bulk" bulk-type="delete">
        Delete <i class="fa fa-trash ml-10"></i>
      </button>
      @endif
    </form>
    
    {{-- <button type="button" class="btn btn-outline-info ml-10" id="approve">Approve</button> --}}
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage-sales-order', [
  'import_template_url' => route('superuser.sale.sales_order.import_template'),
  'import_url' => route('superuser.sale.sales_order.import'),
  'export_url' => route('superuser.sale.sales_order.export')
])

@endsection

@push('plugin-styles')
<link rel="stylesheet" href="{{ url('https://cdn.datatables.net/select/1.3.1/css/select.dataTables.min.css') }}">
@endpush

@push('scripts')
<script src="{{ url('https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
@include('superuser.asset.plugin.daterangepicker')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.sale.sales_order.json') }}';
  
  let showControl = $('input[type=radio][name=show-control]');
  let valShow = "default";
  showControl.change(function() {
    let newDatatableUrl = datatableUrl+'?show='+this.value;
    valShow = this.value;
    $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
  });

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
      let newDatatableUrl = datatableUrl + '?from=' + start_date + '&to=' + end_date +'&show='+valShow;
      $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
    // alert('aa')
    }
  });
  
  var table = $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'id', width: '3%'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'code'},
      {data: 'customer_marketplace'},
      {data: 'store_name'},
      {data: 'ekspedisi_marketplace'},
      {data: 'status'},
      {data: 'action', orderable: false, searcable: false}
    ],
    columnDefs: [ {
        orderable: false,
        searcable: false,
        // data: null,
        defaultContent: '',
        className: 'select-checkbox',
        targets:   0
    } ],
    select: {
        style: 'os',
        selector: 'td:not(:last-child)',
    },
    order: [
      [1, 'desc']
    ],
    pageLength: 10,
    lengthMenu: [
      [10, 30, 100, -1],
      [10, 30, 100, 'All']
    ],
    "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });
  

  $('#approve').on('click', function (e) {
    e.preventDefault();
    var selected = '';

    table.rows( { selected: true } ).every( function ( rowIdx, tableLoop, rowLoop ) {
      var data = this.node();
      var id = data.querySelector('.sales_order_id').textContent;
      selected = selected + id + ",";
    });

    $('input[name="bulk_acc_ids"]').val(selected);
    bulkaccConfirmation('');

  })

  $('.bulk-click').on('click', function (e) {
    e.preventDefault();
    var selected = '';
    var type = $(this).attr('bulk-type');

    // alert(type);return;
    table.rows( { selected: true } ).every( function ( rowIdx, tableLoop, rowLoop ) {
      var data = this.node();
      var id = data.querySelector('.sales_order_id').textContent;
      selected = selected + id + ",";
    });
    
    $('input[name="bulk_acc_ids"]').val(selected);
    $('input[name="bulk_type"]').val(type);
    bulkaccConfirmation(type.toUpperCase());

  })

  function bulkaccConfirmation(position) {
    Swal.fire({
      title: 'Are you sure to '+position+'?',
      type: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      backdrop: false,
    }).then(result => {
      if (result.value) {
        $('#form_bulk_acc').submit();
      }
    });
  }
    
});
</script>
@endpush
