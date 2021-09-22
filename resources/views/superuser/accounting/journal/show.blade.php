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
          <div class="col-md-2">
            <a href="{{ route('superuser.accounting.journal.index') }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          <div class="col-md-10 text-right">
            @if ($journal_periode->id == $journal_periodes[0]->id)
            <a href="javascript:saveConfirmation('{{ route('superuser.accounting.journal.unpost', $journal_periode->id) }}')">
              <button type="button" class="btn bg-gd-corporate border-0 text-white">
                <i class="fa fa-undo mr-10"></i> UNPOST
              </button>
            </a>
            @endif
             <a href="{{ route('superuser.report.general_ledger.show', $journal_periode->id) }}">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                General Ledger <i class="fa fa-sticky-note-o ml-10"></i>
              </button>
            </a>
            <a href="#">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                Download C/F <i class="fa fa-sticky-note-o ml-10"></i>
              </button>
            </a>
            <a href="{{ route('superuser.report.profit_loss_report.show', $journal_periode->id) }}" target="_blank">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                Download P/L <i class="fa fa-sticky-note-o ml-10"></i>
              </button>
            </a> 
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
              @foreach($journal_periodes as $periode)
              <option value="{{ $periode->id }}" {{ $periode->id == $journal_periode->id ? 'selected':'' }}>{{ \Carbon\Carbon::parse( $periode->from_date )->format('d/m/Y') }} &nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp; {{ \Carbon\Carbon::parse( $periode->to_date )->format('d/m/Y') }}</option>
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
      <tbody>
       
      </tbody>
      <tfoot>
        <tr>
          <th class="text-left" colspan="3" id="action_table"></th>
          <th class="text-center" id="debet_total"></th>
          <th class="text-center" id="credit_total"></th>
        </tr>
      </tfoot>
    </table>
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
  
          return data.data;
        }
      },
      order: [
        [0, 'asc']
      ],
      "columnDefs": [
        { className: "text-left", "targets": [ 1, 2 ] }
      ],
      "columns": [
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
      pageLength: 10000,
      lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
      ],
      dom: 'Bfrtip',
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
