@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Transaction Report</span>
    <span class="breadcrumb-item active">Stock Valuation</span>
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

  <form id="form" target="_blank" action="{{ route('superuser.transaction_report.stock_valuation.export') }}"
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
              <label class="col-md-2 col-form-label text-left" for="category">Category :</label>
              <div class="col-md-3">
                <select class="js-select2 form-control" id="category" name="category[]" data-placeholder="Select Category" multiple="multiple" required>
                  <option value="all">All</option>
                  @foreach($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="warehouse">Warehouse :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="warehouse" name="warehouse[]" data-placeholder="Select Warehouse" multiple="multiple" required>
                  <option value="all">All</option>
                  @foreach ($warehouses as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                  @endforeach
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
      <table class="datatable table table-striped table-vcenter table-responsive-sm table-bordered display nowrap">
        <thead>
          <tr>
            <th class="text-center" rowspan="2" style="vertical-align: middle;border-bottom: 1px solid rgb(232, 232, 232);">SKU</th>
            <th class="text-center" rowspan="2" style="vertical-align: middle;border-bottom: 1px solid rgb(232, 232, 232);">Product</th>
            <th class="text-center" rowspan="2" style="vertical-align: middle;border-bottom: 1px solid rgb(232, 232, 232);">Opening Qty</th>
            <th class="text-center" rowspan="2" style="vertical-align: middle;border-bottom: 1px solid rgb(232, 232, 232);">Opening Balance</th>
            <th class="text-center" colspan="2">Purchasing</th>
            <th class="text-center" colspan="2">Receiving</th>
            <th class="text-center" colspan="2">Sale</th>
            <th class="text-center" colspan="2">Return</th>
            <th class="text-center" rowspan="2" style="vertical-align: middle;border-bottom: 1px solid rgb(232, 232, 232);">Closing Qty</th>
            <th class="text-center" rowspan="2" style="vertical-align: middle;border-bottom: 1px solid rgb(232, 232, 232);">Closing Balance</th>
          </tr>
          <tr>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Purch. Qty</th>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Total Purch.</th>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Receiving Qty</th>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Total Receiving</th>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Sale Qty</th>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Total Sale</th>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Return Qty</th>
            <th class="text-center" style="border-bottom: 1px solid rgb(232, 232, 232);border-top: 1px solid rgb(232, 232, 232);">Total Return</th>
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
  <script type="text/javascript">
    var start_date = '{{ \Carbon\Carbon::now()->format('Y-m-d') }}';
    var end_date = '{{ \Carbon\Carbon::now()->format('Y-m-d') }}';
    
    $(document).ready(function() {
      
      $('.js-select2').select2()

      $('#category, #warehouse').val('all').trigger('change');

      $('#datesearch').daterangepicker({
        autoUpdateInput: false
      });

      $('#datesearch').data('daterangepicker').setStartDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
      $('#datesearch').data('daterangepicker').setEndDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');

      $('#datesearch').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        start_date = picker.startDate.format('YYYY-MM-DD');
        end_date = picker.endDate.format('YYYY-MM-DD');
      });

      let datatableUrl = '{{ route('superuser.transaction_report.stock_valuation.json') }}';

      let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
        '&category=all&warehouse=all';

      var datatable = $('.datatable').DataTable({
        "language": {
          "processing": "<span class='fa-stack fa-lg'>\n\
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
            data: 'sku',
            name: 'master_products.code'
          },
          {
            data: 'name',
            name: 'master_products.name'
          },
          {
            data: 'opening_qty',
            searchable: false
          },
          {
            data: 'opening_balance',
            searchable: false
          },
          {
            data: 'purchase_qty',
            searchable: false
          },
          {
            data: 'total_purchase',
            searchable: false
          },
          {
            data: 'receiving_qty',
            searchable: false
          },
          {
            data: 'total_receiving',
            searchable: false
          },
          {
            data: 'sale_qty',
            searchable: false
          },
          {
            data: 'total_sale',
            searchable: false
          },
          {
            data: 'return_qty',
            searchable: false
          },
          {
            data: 'total_return',
            searchable: false
          },
          {
            data: 'closing_qty',
            searchable: false
          },
          {
            data: 'closing_balance',
            searchable: false
          },

        ],
        // paging: false,
        // info: false,
        ordering: false,
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
        @if($superuser->can('stock valuation-print'))
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
        ]
        @else
        buttons: []
        @endif
      });

      $('#btn-filter').on('click', function(e) {
        e.preventDefault();

        var category = $('#category').val();
        var warehouse = $('#warehouse').val();

        let newDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
          '&category=' + category +'&warehouse=' + warehouse;
        datatable.ajax.url(newDatatableUrl).load();
      });

      $('#category, #warehouse').on('select2:select', function (e) {
          var data = e.params.data.id;
          if(data == 'all') {
            $(this).val('all').trigger('change');
          } else {
            var all = $(this).val();
            const index = all.indexOf('all');
            if (index > -1) {
              all.splice(index, 1);
            }
            $(this).val(all).trigger('change');
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

    });

  </script>
@endpush
