@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Transaction Report</span>
    <span class="breadcrumb-item active">Daily Cash/Bank Recapitulation</span>
  </nav>
  @if ($errors->any())
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

  <form id="form" target="_blank" action="{{ route('superuser.transaction_report.daily_recapitulation.pdf') }}"
    enctype="multipart/form-data" method="POST">
    @csrf 
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-left" for="coa">COA Account:</label>
          <div class="col-md-3">
            <select class="js-select2 form-control" id="coa" name="coa" data-placeholder="Select COA Account">
              <option value="all" data-code="all">All</option>
              @foreach ($coas as $item)
                <option value="{{ $item->id }}" data-code="{{ $item->code }}">{{ $item->code }} - {{ $item->name }}</option>
              @endforeach
            </select>
          </div>
          <label class="col-md-1 col-form-label text-right" for="date">Date :</label>
          <div class="col-md-2">
            <input type="date" class="form-control" id="date" name="date" max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
              value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
          </div>
        </div>
      </div>
    </div>
  </form>

  <div class="block">
    <div class="block-content block-content-full">
      <table class="datatable table table-striped table-vcenter table-responsive table-sm">
        <thead>
          <tr>
            <th class="text-center">COA</th>
            <th class="text-center">Account</th>
            <th class="text-center">Beginning Balance</th>
            <th class="text-center">Debet</th>
            <th class="text-center">Credit</th>
            <th class="text-center">Ending Balance</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
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
  {{-- <script src="{{ asset('utility/superuser/js/form.js') }}"></script> --}}
  <script type="text/javascript">
    
    $.fn.dataTable.ext.search.push(
      function(settings, data, dataIndex) {
        var coa = $('#coa').find(':selected').data('code');
        var coa_data = data[0];

        if (coa == 'all' || coa == coa_data) {
          return true;
        }
        return false;
      }
    );

    $(document).ready(function() {
      $('.js-select2').select2()

      var date = $('#date').val();

      let datatableUrl = '{{ route('superuser.transaction_report.daily_recapitulation.json') }}';
      let firstDatatableUrl = datatableUrl+'?date='+date;

      $('#date').on('change', function(){
        date = $('#date').val();
        
        if( date ) {
          let newDatatableUrl = datatableUrl+'?date='+date;
          datatable.ajax.url(newDatatableUrl).load();
        }
      });

      var datatable = $('.datatable').DataTable({
        processing: true,
        ajax: {
          "url": firstDatatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ _token: "{{csrf_token()}}"}
        },
        columns: [
          null,
          null,
          null,
          null,
          null,
          null,
        ],
        // paging: false,
        // info: false,
        // ordering: false,
        // searching: false,
        order: [
          [0, 'asc']
        ],
        pageLength: 10,
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100]
        ],
        dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
          buttons: [
          {
            text: '<i class="fa fa-file-pdf-o"></i>',
            action: function(e, dt, node, config) {
              $('#form').submit();
            }
          },
          {
            extend: 'excelHtml5',
            text: '<i class="fa fa-file-excel-o"></i>',
            titleAttr: 'Excel',
            title: 'Daily Cash/Bank Recapitulation'
          }
        ]
      });

      $('#coa').on('change', function() {
        datatable.draw();
      });

    });

  </script>
@endpush
