@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Transaction Report</span>
    <span class="breadcrumb-item active">Receiving Report</span>
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

  <div class="form-group row">
    <div class="col-md-9">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="supplier">Supplier :</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="supplier" name="supplier" data-placeholder="Select Supplier">
                <option value="all">All</option>
                @foreach ($supplier as $item)
                  <option value="{{ $item->supplier_id }}">{{ $item->supplier->name }}</option>
                @endforeach
              </select>
            </div>
            <label class="col-md-2 col-form-label text-left" for="product">SKU :</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="product" name="product" data-placeholder="Select SKU">
                <option value="all">All</option>
                @foreach ($products as $item)
                  <option value="{{ $item->id }}">{{ $item->code }}</option>
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

  <div class="block">
    <div class="block-content block-content-full">
      <table class="datatable table table-striped table-vcenter table-responsive display nowrap">
        <thead>
          <tr>
            <th class="text-center">Supplier</th>
            <th class="text-center">PPB No</th>
            <th class="text-center">Notes</th>
            <th class="text-center">SKU</th>
            <th class="text-center">Product</th>
            <th class="text-center">PPB Qty</th>
            <th class="text-center">RI Qty</th>
            <th class="text-center">Colly Qty</th>
            <th class="text-center">HPP</th>
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
  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2()

      let datatableUrl = '{{ route('superuser.transaction_report.receiving_report.json') }}';

      let firstDatatableUrl = datatableUrl + '?supplier=all&product=all';

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
        columns: [
          {
            data: 'supplier',
            name: 'master_supplier.name'
          },
          {
            data: 'ppb',
            name: 'ppb.code'
          },
          {
            data: 'description',
            name: 'ppb.description'
          },
          {
            data: 'sku',
            name: 'master_products.code'
          },
          {
            data: 'product',
            name: 'master_products.name'
          },
          {
            data: 'ppb_qty',
            searchable: false
          },
          {
            data: 'ri_qty',
            searchable: false
          },
          {
            data: 'colly_qty',
            searchable: false
          },
          {
            data: 'hpp',
            render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            searchable: false
          }
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
        @if($superuser->can('receiving report-print'))
        buttons: [{
            extend: 'pdfHtml5',
            text: '<i class="fa fa-file-pdf-o"></i>',
            titleAttr: 'PDF',
            title: 'Receiving Report',
            orientation: 'landscape',
            pageSize: 'LEGAL',
            "action": newexportaction
          },
          {
            extend: 'excelHtml5',
            text: '<i class="fa fa-file-excel-o"></i>',
            titleAttr: 'Excel',
            title: 'Receiving Report',
            "action": newexportaction
          }
        ]
        @else
        buttons: []
        @endif
      });

      $('#btn-filter').on('click', function(e) {
        e.preventDefault();

        var supplier = $('#supplier').val();
        var product = $('#product').val();

        let newDatatableUrl = datatableUrl + '?supplier=' + supplier + '&product=' + product;
        datatable.ajax.url(newDatatableUrl).load();
      })

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
