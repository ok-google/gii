@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Transaction Report</span>
    <span class="breadcrumb-item active">Purchase Report</span>
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

  <form id="form" target="_blank" action="{{ route('superuser.transaction_report.purchase_report.pdf') }}"
    enctype="multipart/form-data" method="POST">
    @csrf
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-1 col-form-label text-left" for="supplier">Supplier :</label>
          <div class="col-md-2">
            <select class="js-select2 form-control" id="supplier" name="supplier" data-placeholder="Select Supplier">
              <option value="all">All</option>
              @foreach ($supplier as $item)
                <option value="{{ $item->supplier_id }}">{{ $item->supplier->name }}</option>
              @endforeach
            </select>
          </div>
          <label class="col-md-1 col-form-label text-left" for="status">Status :</label>
          <div class="col-md-2">
            <select class="js-select2 form-control" id="status" name="status" data-placeholder="Select Status">
              <option value="all">All</option>
              <option value="paid">Paid Off</option>
              <option value="debt">Debt</option>
            </select>
          </div>
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
    <div class="block-content block-content-full">
      <table class="datatable table table-striped table-vcenter table-responsive">
        <thead>
          <tr>
            <th>#</th>
            <th class="text-center">Date</th>
            <th class="text-center">Supplier</th>
            <th class="text-center">Invoice No</th>
            <th class="text-center">Debt</th>
            <th class="text-center">Payment</th>
            <th class="text-center">Outstanding Debt</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        {{-- <tfoot>
          <tr>
            <th class="text-right" colspan="2" id="action_table">TOTAL</th>
            <th class="text-center" id="debet_total">{{ $item['total']['debet'] }}</th>
            <th class="text-center" id="credit_total">{{ $item['total']['credit'] }}</th>
          </tr>
          <tr>
            <th class="text-right" colspan="2" id="action_table">SALDO AKHIR</th>
            <th class="text-center" id="debet_total">{{ $item['saldoakhir']['debet'] }}</th>
            <th class="text-center" id="credit_total">{{ $item['saldoakhir']['credit'] }}</th>
          </tr>
        </tfoot> --}}
      </table>
    </div>
  </div>

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.daterangepicker')

@section('modal')

@endsection

@push('scripts')
  {{-- <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
  --}}
  <script type="text/javascript">
    var start_date;
    var end_date;

    function format(d) {
      return d[7];
    }

    $.fn.dataTable.ext.search.push(
      function(settings, data, dataIndex) {
        var supplier = $('#supplier').select2('data')[0]['text'];
        var supplier_data = data[2];

        var status = $('#status').val();
        var outstanding_debt_data = data[6];

        if ((supplier == 'All' || supplier == supplier_data) && (status == 'all' || (status == 'paid' &&
            outstanding_debt_data == 0) || (status == 'debt' && outstanding_debt_data > 0))) {
          return true;
        }
        return false;
      }
    );

    $(document).ready(function() {
      $('.js-select2').select2()

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
          datatable.ajax.url(newDatatableUrl).load();
        }
      });

      let datatableUrl = '{{ route('superuser.transaction_report.purchase_report.json') }}';
      let firstDatatableUrl = datatableUrl+'?from={{ \Carbon\Carbon::now()->format('Y-m-d') }}&to={{ \Carbon\Carbon::now()->format('Y-m-d') }}';

      var datatable = $('.datatable').DataTable({
        processing: true,
        ajax: {
          "url": firstDatatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ _token: "{{csrf_token()}}"}
        },
        columns: [{
            "class": "details-control",
            "orderable": false,
            "data": null,
            "defaultContent": ""
          },
          null,
          null,
          null,
          {
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. ')
          },
          {
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. ')
          },
          {
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. ')
          },
          {
            "visible": false
          },
          // {
          //   "visible": false
          // },
        ],
        // paging: false,
        // info: false,
        // ordering: false,
        // searching: false,
        order: [
          [1, 'asc']
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
            title: 'Purchase Report',
            exportOptions: {
              columns: [1, 2, 3, 4, 5, 6]
            }
          }
        ]
      });

      $('#supplier, #status').on('change', function() {
        datatable.draw();
      });

      // Add event listener for opening and closing details
      $('.datatable tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = datatable.row(tr);

        if (row.child.isShown()) {
          // This row is already open - close it
          row.child.hide();
          tr.removeClass('shown');
        } else {
          // Open this row
          row.child(format(row.data())).show();
          tr.addClass('shown');
        }
      });

    });

  </script>
@endpush
