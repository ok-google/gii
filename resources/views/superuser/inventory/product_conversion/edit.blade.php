@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.inventory.product_conversion.index') }}">Product Conversion</a>
    <span class="breadcrumb-item active">Edit</span>
  </nav>
  <div id="alert-block"></div>

  <form class="ajax" data-action="{{ route('superuser.inventory.product_conversion.update', $product_conversion->id) }}"
    data-type="POST" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="ids_delete" value="">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Edit Product Conversion</h3>
      </div>
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
          <div class="col-md-7">
            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)"
              value="{{ $product_conversion->code }}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse" name="warehouse"
              data-placeholder="Select Warehouse" disabled>
              <option></option>
              @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}"
                  {{ $warehouse->id == $product_conversion->warehouse_id ? 'selected' : '' }}>{{ $warehouse->name }}
                </option>
              @endforeach
            </select>
            <small class="form-text text-muted font-italic">Select warehouse first before choose product</small>
          </div>
        </div>
        <div class="form-group row pt-30">
          <div class="col-md-6">
            <a href="{{ route('superuser.inventory.product_conversion.index') }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          <div class="col-md-6 text-right">
            <button type="submit" class="btn bg-gd-corporate border-0 text-white">
              Submit <i class="fa fa-arrow-right ml-10"></i>
            </button>
          </div>
        </div>

      </div>
    </div>

    <div class="block">
      <div class="block-header block-header-default">
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
              <th class="text-center">Convert To</th>
              <th class="text-center">Quantity</th>
              <th class="text-center">Keterangan</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($product_conversion->details as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <input type="hidden" class="form-control" name="edit[]" value="{{ $item->id }}">
                  <select class="js-select2 form-control js-ajax" id="product_from[{{ $loop->iteration }}]" name="product_from[]"
                    data-placeholder="Select SKU" style="width:100%" required>
                    <option value="{{ $item->product_from }}" selected>{{ $item->product_from_rel->code }}</option>
                  </select>
                </td>
                <td>
                  <select class="js-select2 form-control js-ajax js-ajax-product-to" id="product_to[{{ $loop->iteration }}]" name="product_to[]"
                    data-placeholder="Select SKU" style="width:100%" required>
                    <option value="{{ $item->product_to }}" selected>{{ $item->product_to_rel->code }}</option>
                  </select>
                </td>
                <td>
                  <input type="number" class="form-control text-center" name="qty[]" value="{{ $item->qty }}" required>
                </td>
                <td>
                  <input type="text" class="form-control" name="description[]" value="{{ $item->description }}">
                </td>
                <td>
                  <a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger"
                      title="Delete"><i class="fa fa-trash"></i></button></a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </form>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
  <script src="{{ asset('utility/superuser/js/form.js') }}"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2()

      initailizeSelect2();

      var table = $('#datatable').DataTable({
        paging: false,
        bInfo: false,
        searching: false,
        columns: [{
            name: 'counter',
            "visible": false
          },
          {
            name: 'product_from',
            orderable: false,
            width: "25%"
          },
          {
            name: 'product_to',
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

      var counter = 1000;

      $('a.row-add').on('click', function(e) {
        e.preventDefault();

        table.row.add([
          counter,
          '<input type="hidden" class="form-control" name="edit[]" value=""><select class="js-select2 form-control js-ajax" id="product_from[' +
          counter +
          ']" name="product_from[]" data-placeholder="Select SKU" style="width:100%" required></select>',
          '<select class="js-select2 form-control js-ajax-product-to" id="product_to[' + counter +
          ']" name="product_to[]" data-placeholder="Select SKU" style="width:100%" required></select>',
          '<input type="number" class="form-control text-center" name="qty[]" required>',
          '<input type="text" class="form-control" name="description[]">',
          '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
        ]).draw(false);

        initailizeSelect2();
        counter++;
      });

      function initailizeSelect2() {
        $(".js-ajax").select2({
          ajax: {
            url: '{{ route('superuser.inventory.product_conversion.search_sku') }}',
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

          $(this).parents('tr').find('input[name="qty[]"]').attr({
            "max": e.params.data.stock,
            "min": 0,
            "placeholder": e.params.data.stock
          });
        });

        $(".js-ajax-product-to").select2({
          ajax: {
            url: '{{ route('superuser.inventory.product_conversion.search_sku') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                q: params.term,
                warehouse: 'all-product',
                _token: "{{ csrf_token() }}"
              };
            },
            cache: true
          },
          minimumInputLength: 3,
        });

      };

      $('#warehouse').on('select2:select', function(e) {
        table.clear().draw();
      });

      $('#datatable tbody').on('click', '.row-delete', function(e) {
        e.preventDefault();

        parent = $(this).parents('tr');
        edit = parent.find('input[name="edit[]"]').val();
        if (edit) {
          ids_delete = $('input[name="ids_delete"]').val();
          $('input[name="ids_delete"]').val(edit + ',' + ids_delete);
        }

        table.row($(this).parents('tr')).remove().draw();
      });


    });

  </script>
@endpush
