@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Transaction Report</span>
    <span class="breadcrumb-item active">Sales Report</span>
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

  <form id="form" target="_blank" action="{{ route('superuser.transaction_report.sales_report.export') }}"
    enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="download_type" id="download_type" value="">
    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="period">Period :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"
                        aria-hidden="true"></i></span></div><input type="text" class="form-control pull-right"
                    id="datesearch" name="datesearch" placeholder="Select period"
                    value="{{ \Carbon\Carbon::now()->format('d/m/Y') }} - {{ \Carbon\Carbon::now()->format('d/m/Y') }}">
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="marketplace">Marketplace :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="marketplace" name="marketplace" data-placeholder="Select Marketplace">
                  <option value="all">All</option>
                  @foreach (\App\Entities\Sale\SalesOrder::MARKETPLACE_ORDER as $key => $value)
                    <option value="{{ $value }}">{{ $key }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="status">Status :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="status" name="status" data-placeholder="Select Status">
                  <option value="all">All</option>
                  <option value="paid">Paid</option>
                  <option value="debt">Unpaid</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <div class="col-md-12 text-center">
                <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <div class="block">
    <div class="block-content block-content-full">
      <table class="datatable table table-striped table-vcenter table-responsive table-sm display nowrap">
        <thead>
          <tr>
            <th>#</th>
            <th class="text-center">Create Date</th>
            <th class="text-center">Order Date</th>
            <th class="text-center">MP Receipt Code</th>
            <th class="text-center">Marketplace</th>
            <th class="text-center">Store</th>
            <th class="text-center">Customer</th>
            <th class="text-center">Invoice No</th>
            <th class="text-center">Receivable</th>
            <th class="text-center">Paid</th>
            <th class="text-center">Cost</th>
            <th class="text-center">Unpaid</th>
            <th class="text-center">Retur</th>
            <th></th>
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
@include('superuser.asset.plugin.daterangepicker')

@section('modal')

@endsection

@push('scripts')
  {{-- <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
  --}}
  <script type="text/javascript">
    var start_date = {{ \Carbon\Carbon::now()->format('Y-m-d') }};
    var end_date = {{ \Carbon\Carbon::now()->format('Y-m-d') }};
    var print_date = "SR-{{ \Carbon\Carbon::now()->format('dmy') }}-{{ \Carbon\Carbon::now()->format('dmy') }}";
    function format(d) {
      return d['detail'];
    }
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
        print_date = "SR-"+picker.startDate.format('DDMMYY')+"-"+picker.endDate.format('DDMMYY');
      });
      let datatableUrl = '{{ route('superuser.transaction_report.sales_report.json') }}';
      let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
        '&marketplace=all&status=all';
      var datatable = $('.datatable').DataTable({
        language: {
          processing: "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                           </span>",
        },
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
          "url": firstDatatableUrl,
          "dataType": "json",
          "type": "GET",
          "data": {
            _token: "{{ csrf_token() }}"
          }
        },
        columns: [{
            "class": "details-control",
            "orderable": false,
            "data": null,
            "defaultContent": "",
            searchable: false
          },
          {
            data: 'create_date',
            searchable: false
          },
          {
            data: 'order_date',
            searchable: false
          },
          {
            data: 'kode_pelunasan',
          },
          {
            data: 'marketplace_order',
            searchable: false
          },
          {
            data: 'store_name',
            name: 'sales_order.store_name'
          },          
          {
            data: 'customer_name',
            name: 'sales_order.customer_marketplace'
          },
          {
            data: 'code',
            name: 'sales_order.code'
          },
          {
            data: 'grand_total',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
          },
          {
            data: 'total_paid',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
          },
          {
            data: 'total_cost',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
          },
          {
            data: 'unpaid',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
          },
          {
            data: 'retur',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
          },
          {
            data: 'detail',
            "visible": false,
            searchable: false
          },
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
        buttons: [{
            text: '<i class="fa fa-file-pdf-o"></i>',
            action: function(e, dt, node, config) {
              $('#download_type').val('pdf');
              $('#form').submit();
            }
          },
          {
            text: '<i class="fa fa-file-excel-o"></i>',
            action: function(e, dt, node, config) {
              $('#download_type').val('excel');
              $('#form').submit();
            }
          },
          // {
          //   extend: 'excelHtml5',
          //   text: '<i class="fa fa-file-excel-o"></i>',
          //   titleAttr: 'Excel',
          //   title: 'Sales Report',
          //   filename: function () { return getExportFileName()},
          //   exportOptions: {
          //     columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
          //   },
          //   action: newexportaction
          // }
        ]
      });
      $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var marketplace = $('#marketplace').val();
        var status = $('#status').val();
        let newDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
          '&marketplace=' + marketplace + '&status=' + status;
        datatable.ajax.url(newDatatableUrl).load();
      })
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
      function newexportaction(e, dt, button, config) {
        var self = this;
        var oldStart = dt.settings()[0]._iDisplayStart;
        dt.one('preXhr', function(e, s, data) {
          // Just this once, load all data from the server...
          data.start = 0;
          data.length = 2147483647;
          dt.one('preDraw', function(e, settings) {
            // Call the original action function
            if (button[0].className.indexOf('buttons-copy') >= 0) {
              $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-excel') >= 0) {
              $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-csv') >= 0) {
              $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
              $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-print') >= 0) {
              $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
            }
            dt.one('preXhr', function(e, s, data) {
              // DataTables thinks the first item displayed is index 0, but we're not drawing that.
              // Set the property to what it was before exporting.
              settings._iDisplayStart = oldStart;
              data.start = oldStart;
            });
            // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
            setTimeout(dt.ajax.reload, 0);
            // Prevent rendering of the full data to the DOM
            return false;
          });
        });
        // Requery the server with the new one-time export settings
        dt.ajax.reload();
      }
      function getExportFileName(){
        return print_date;
      }
    });
  </script>
@endpush