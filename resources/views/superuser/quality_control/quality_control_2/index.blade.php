@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Quality Control</span>
    <span class="breadcrumb-item active">Quality Control Display</span>
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

  <div id="alert-block"></div>

  <form class="ajax" data-action="{{ route('superuser.quality_control.quality_control_2.store') }}" data-type="POST"
    enctype="multipart/form-data">
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-left" for="warehouse">Warehouse <span
              class="text-danger">*</span></label>
          <div class="col-md-3">
            <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouses_display as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
              @endforeach
            </select>
            <small class="form-text text-muted font-italic">Select warehouse first before choose product</small>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-left" for="warehouse_reparation">Warehouse Reparation <span
              class="text-danger">*</span></label>
          <div class="col-md-3">
            <select class="js-select2 form-control" id="warehouse_reparation" name="warehouse_reparation"
              data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouses_reparation as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <hr class="my-20">
      <div class="block">
        <div class="block-header">
          <h3 class="block-title">Add Product</h3>
          <a href="#" class="row-add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              <i class="fa fa-plus mr-10"></i> Row
            </button>
          </a>
        </div>
        <div class="block-content">
          <table id="datatable" class="table table-striped table-vcenter table-responsive">
            <thead>
              <tr>
                <th class="text-center">Counter</th>
                <th class="text-center">Select SKU</th>
                <th class="text-center">Product</th>
                <th class="text-center">Quantity</th>
                <th class="text-center">Keterangan</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
      <div class="block-content block-content-full">
        <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="approve">
          Submit <i class="fa fa-arrow-right ml-10"></i>
        </button>
      </div>
    </div>
  </form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
  <script src="{{ asset('utility/superuser/js/form.js') }}"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2()

      var table = $('#datatable').DataTable({
        paging: false,
        bInfo: false,
        searching: false,
        columns: [{
            name: 'counter',
            "visible": false
          },
          {
            name: 'sku',
            orderable: false,
            width: "25%"
          },
          {
            name: 'name',
            orderable: false,
            searcable: false
          },
          {
            name: 'quantity',
            orderable: false,
            searcable: false,
            width: "5%"
          },
          {
            name: 'keterangan',
            orderable: false,
            searcable: false
          },
          {
            name: 'action',
            orderable: false,
            searcable: false,
            width: "5%"
          }
        ],
        'order': [
          [0, 'desc']
        ]
      })

      var counter = 1;

      $('a.row-add').on('click', function(e) {
        e.preventDefault();

        table.row.add([
          counter,
          '<select class="js-select2 form-control js-ajax" id="sku[' + counter +
          ']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
          '<span class="name"></span>',
          '<input type="number" class="form-control text-center" name="quantity[]" required>',
          '<input type="text" class="form-control" name="keterangan[]">',
          '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
        ]).draw(false);

        initailizeSelect2();
        counter++;
      });

      function initailizeSelect2() {
        $(".js-ajax").select2({
          ajax: {
            url: '{{ route('superuser.inventory.mutation_display.search_sku') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                q: params.term,
                warehouse: $('#warehouse').val(),
                _token: "{{ csrf_token() }}"
              };
            },
            cache: true
          },
          minimumInputLength: 3,
        });

        $('.js-ajax').on('select2:select', function(e) {
          var name = e.params.data.name;
          $(this).parents('tr').find('.name').text(name);

          $(this).parents('tr').find('input[name="quantity[]"]').attr({
            "max": e.params.data.stock,
            "min": 0,
            "placeholder": e.params.data.stock
          });
        });

      };

      $('#warehouse').on('select2:select', function(e) {
        table.clear().draw();
      });

      $('#datatable tbody').on('click', '.row-delete', function(e) {
        e.preventDefault();
        table.row($(this).parents('tr')).remove().draw();

        if (typeof $('input[name="id[]"]').val() == 'undefined') {
          $('#submit-table').hide();
        }
      });


    });

  </script>
@endpush
