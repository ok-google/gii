@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item active">Daily Cash / Bank Report</span>
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
  <label class="col-md-2 col-form-label text-right" for="coa">Select COA</label>
  <div class="col-md-3">
    <select class="js-select2 form-control" id="coa" name="coa" data-placeholder="Select COA">
      <option></option>
      @foreach($coas as $coa)
      <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }}</option>
      @endforeach
    </select>
  </div>
  {{-- <div class="form-group row"> --}}
    <label class="col-md-1 col-form-label text-left" for="period">Period :</label>
    <div class="col-md-4">
      <div class="input-group">
        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"
              aria-hidden="true"></i></span></div><input type="text" class="form-control pull-right" id="datesearch"
          name="datesearch" placeholder="Select period"
          value="{{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->format('d/m/Y') }}">
      </div>
    </div>
  {{-- </div> --}}

  {{-- <label class="col-md-2 col-form-label text-right" for="date">Select Date</label>
  <div class="col-md-3">
    <input class="form-control" type="date" id="date" name="date" max="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}">
  </div> --}}
</div>
<div class="block">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Date</th>
          <th class="text-left">Transaction</th>
          <th class="text-center">Debet</th>
          <th class="text-center">Credit</th>
          <th class="text-center">Balance</th>
        </tr>
      </thead>
      <tfoot>
        <tr id="result-sa" style="display: none">
          <th class="text-center" colspan="4">Saldo Akhir</th>
          <th class="text-center" id="saldo_akhir"></th>
        </tr>
      </tfoot>
    </table>
  </div>
  <div class="block-content pt-0" id="download" style="display: none">
    <div class="form-group row">
      <div class="col-md-12 text-center">
          <a id="download-to" href="#" target="_blank">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              Download <i class="fa fa-sticky-note-o ml-10"></i>
            </button>
          </a>
      </div>
    </div>
  </div>

</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@section('modal')

@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
@include('superuser.asset.plugin.daterangepicker')
<script type="text/javascript">
function convertDateToSQL(obj){

  var newSD = new Date(obj);
  var year = newSD.getFullYear();
  var month = ((parseInt(newSD.getMonth())+1).toString().length == 1 ? "0"+(parseInt(newSD.getMonth())+1) : (parseInt(newSD.getMonth())+1));
  var date = (newSD.getDate().toString().length == 1 ? "0"+newSD.getDate() : newSD.getDate());
  return year+"-"+month+"-"+date;
}
$(document).ready(function() {
  $('.js-select2').select2()

  let datatableUrl = '{{ route('superuser.accounting.daily_report.json') }}';

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
      
      var coa = $('#coa').val();
      let newDatatableUrl = datatableUrl + '?coa='+ coa +'&from=' + start_date + '&to=' + end_date;
      $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
    // alert('aa')
    }
  });


  $('#date,#coa').on('change', function(){
    $('#result-sa').hide();
    $('#download').hide();

    var sd = $('#datesearch').data('daterangepicker').startDate._d;
    var newSD = convertDateToSQL(sd);
    var ed = $('#datesearch').data('daterangepicker').endDate._d;
    var newED = convertDateToSQL(ed);
    // alert(+"="+endDate)
    var coa = $('#coa').val();
    var date   = $('#date').val();
    // if( coa && date ) { REMOVE BY dani
    //   let newDatatableUrl = datatableUrl+'?coa='+coa+'&date='+date;
    //   $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
    // }
    if( coa) {
      let newDatatableUrl = datatableUrl + '?coa='+ coa +'&from=' + newSD + '&to=' + newED;
      $('#datatable').DataTable().ajax.url(newDatatableUrl).load();
    }
  });

  var datatable = $('#datatable').DataTable({
    processing: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    order: [
      [0, 'asc']
    ],
    "columnDefs": [
      { className: "text-left", "targets": [ 1, 2 ] }
    ],
    "columns": [
      null,
      null,
      null,
      null,
      null,
      { "visible": false },
    ],
    paging:   false,
    info: false,
    ordering: false,
    searching: false,
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100]
    ],
    dom: 'Bfrtip',
    "footerCallback": function ( row, data, start, end, display ) {
      var api = this.api(), data;

      var sd = $('#datesearch').data('daterangepicker').startDate._d;
      var from = convertDateToSQL(sd);
      var ed = $('#datesearch').data('daterangepicker').endDate._d;
      var to = convertDateToSQL(ed);
      
      saldo_akhir = api
          .row( ':last' )
          .data();

      if(saldo_akhir) {
        $('#saldo_akhir').html( saldo_akhir[4] );
        $('#result-sa').show();
        $('#download').show();
        $('#download-to').attr("href", '{{ route('superuser.accounting.daily_report.pdf') }}/'+$('#coa').val()+'/'+from+'/'+to);
      }
    }
  });
});
</script>
@endpush
