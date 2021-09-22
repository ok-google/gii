@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Accounting</span>
    <span class="breadcrumb-item active">Journal</span>
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
<div class="form-group row">
  <div class="col-md-8">
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-1 col-form-label text-left" for="s_from_date">Period</label>
          <div class="col-md-5">
            <input class="form-control" type="date" id="s_from_date" value="{{ $from_date }}" {{ $min_date ? 'min='.$min_date : '' }} {{ $to_date ? 'max='.$to_date : '' }}>
          </div>
          <label class="col-md-1 col-form-label text-center" for="s_to_date">To</label>
          <div class="col-md-5">
            <input class="form-control" type="date" id="s_to_date" value="{{ $to_date }}" {{ $min_date ? 'min='.$min_date : '' }} {{ $to_date ? 'max='.$to_date : '' }}>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <div class="col-md-12">
            <select class="js-select2 form-control" id="periode" name="periode" data-placeholder="Select Period">
              <option></option>
              @foreach($journal_periode as $periode)
              <option value="{{ $periode->id }}">{{ \Carbon\Carbon::parse( $periode->from_date )->format('d/m/Y') }} &nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp; {{ \Carbon\Carbon::parse( $periode->to_date )->format('d/m/Y') }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<div class="block">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Date</th>
          <th class="text-left">Chart of Account</th>
          <th class="text-left">Transaction</th>
          <th class="text-center">Debet</th>
          <th class="text-center">Credit</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th class="text-left" colspan="3" id="action_table"></th>
          <th class="text-center" id="debet_total"></th>
          <th class="text-center" id="credit_total"></th>
        </tr>
      </tfoot>
    </table>
  </div>
  <div class="block-content pt-0">
    <div class="form-group row">
      <div class="col-md-6">
          <a id="posting" href="{{ route('superuser.accounting.journal.index') }}">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              Posting <i class="fa fa-sticky-note-o ml-10"></i>
            </button>
          </a>
      </div>
    </div>
  </div>

</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
@include('superuser.asset.plugin.select2')

@section('modal')

@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
var totalDebet;
var totalCredit;

$(document).ready(function() {
  $('.js-select2').select2()

  let datatableUrl = '{{ route('superuser.accounting.journal.json') }}';
  let firstDatatableUrl = datatableUrl+'?from_date={{ $from_date }}&to_date={{ $to_date }}';

  $('#s_from_date,#s_to_date').on('change', function(){
    $('#posting').hide();
    var from_date = $('#s_from_date').val();
    var to_date   = $('#s_to_date').val();
    if( from_date && to_date ) {
      let newDatatableUrl = datatableUrl+'?from_date='+from_date+'&to_date='+to_date;
      $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
    }
  });

  var datatable = $('#datatable').DataTable({
    "language": {
          "processing": "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                           </span>",
        },
    processing: true,
    serverSide: true,
    ajax: {
      "url": firstDatatableUrl,
      "dataType": "json",
      "type": "GET",
      dataSrc: function ( data ) {
        totalDebet = data.totalDebet;
        totalCredit = data.totalCredit;

        if(data.canposting == 'yes') {
          $('#from_date').val( $('#s_from_date').val() );
          $('#to_date').val( $('#s_to_date').val() );
          $('#posting').show();

          $('#posting').attr("href", 'javascript:saveConfirmation(\'{{ route('superuser.accounting.journal.posting') }}?from_date='+$('#s_from_date').val()+'&to_date='+$('#s_to_date').val()+'\')');
        } else {
          $('#posting').hide();
        }

        return data.data;
      }
    },
    order: [
      [0, 'asc']
    ],
    columnDefs  : [
      { className: "text-left", "targets": [ 1, 2 ] }
    ],
    columns: [
      {
        data: 'created_date',
        name: 'journal.created_at'
      },
      {
        data: 'code',
        name: 'master_coa.code'
      },
      {
        data: 'transaction',
        name: 'journal.name'
      },
      {
        data: 'debet'
      },
      {
        data: 'credit'
      },
    ],
    paging: true,
    info: false,
    ordering: false,
    searching: true,
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100, 1000, 10000],
      [10, 25, 50, 100, 1000, 10000]
    ],
    dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      drawCallback: function( settings ) {
        var api = this.api();
  
        $( api.column( 3 ).footer() ).html(totalDebet);
        $( api.column( 4 ).footer() ).html(totalCredit);
      },
      @if($superuser->can('all stock-print'))
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fa fa-file-excel-o"></i>',
          titleAttr: 'Excel',
          title: 'Journal'
        }
      ]
      @else
      buttons: []
      @endif
      });

  $('.js-select2').on('select2:select', function (e) {
    window.location.href = '{{ route('superuser.accounting.journal.index') }}/'+$(this).val();
  });
});
</script>
@endpush
