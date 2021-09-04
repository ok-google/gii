@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.inventory.recondition.index') }}">Recondition</a>
    <span class="breadcrumb-item active">Create</span>
  </nav>
  <div id="alert-block"></div>

  <form class="ajax" data-action="{{ route('superuser.inventory.recondition.store') }}" data-type="POST"
    enctype="multipart/form-data">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Create Recondition</h3>
      </div>
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
          <div class="col-md-7">
            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <hr>
        <div class="form-group row">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-right" for="select_sku">Select SKU</label>
              <div class="col-md-7">
                <select class="js-select2 form-control" id="select_sku" name="select_sku" data-placeholder="Select SKU">
                  <option></option>
                  @foreach ($list_sku as $item)
                    <option value="{{ $item->id }}">{{ $item->code }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-md-10 text-right">
                <button id="add" type="button" class="btn bg-gd-sea border-0 text-white">
                  <i class="fa fa-plus mr-10"></i> ADD
                </button>
              </div>
            </div>
          </div>
        </div>

        <input type="hidden" name="warehouse_reparation_id" value="{{ $warehouse_reparation_id }}">
        <div class="form-group row pt-30">
          <div class="col-md-6">
            <a href="{{ route('superuser.inventory.recondition.index') }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          @if ($warehouse_reparation_id)
            <div class="col-md-6 text-right">
              <button type="submit" class="btn bg-gd-corporate border-0 text-white">
                Submit <i class="fa fa-arrow-right ml-10"></i>
              </button>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div id="list-result">
      {{-- <div class="block block-themed block-rounded block-result">
        <div class="block-header bg-earth-dark">
          <h3 class="block-title">SH-06767 / Kaos Oblong Hitam</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option btn-remove-block">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>
        <div class="block-content">
          <table class="table table-striped table-vcenter table-responsive">
            <thead>
              <tr>
                <th class="text-center">Date In</th>
                <th class="text-center">Quantity</th>
                <th class="text-center">Keterangan</th>
                <th class="text-center">Recondition</th>
                <th class="text-center">Disposal</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div> --}}
    </div>
  </form>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.moment')

@push('scripts')
  <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2()

      $('#add').on('click', function(e) {
        e.preventDefault();

        var select_sku = $('#select_sku').val();

        var productexist = $('#product-' + select_sku + '_wrapper').length;

        if (productexist) {
          Codebase.helpers('notify', {
            align: 'center',
            from: 'top',
            type: 'danger',
            icon: 'fa fa-times mr-5',
            message: 'Product has been added!'
          });
        }

        if (select_sku && productexist == 0) {

          $.ajax({
            url: '{{ route('superuser.inventory.recondition.search_sku') }}?product=' + select_sku,
            type: 'GET',
            beforeSend: function() {
              $('#add').attr('disabled', true);
              Codebase.layout('header_loader_on')
            },
            complete: function() {
              $('#add').attr('disabled', false);
              Codebase.layout('header_loader_off')
            }
          }).done(function(response) {
            var html =
              '<div class="js-animation-object animated fadeIn block block-themed block-rounded block-result"><div class="block-header bg-earth-dark"><h3 class="block-title">' +
              response.title +
              '</h3><div class="block-options"><button type="button" class="btn-block-option btn-remove-block"><i class="fa fa-trash"></i></button></div></div><div class="block-content"><table id="product-' +
              response.id +
              '" class="table table-striped table-vcenter table-responsive"><thead><tr><th class="text-center">Date In</th><th class="text-center">In From</th><th class="text-center">Quantity</th><th class="text-center">Keterangan</th><th class="text-center">Recondition</th><th class="text-center">Disposal</th></tr></thead><tbody>';
            $.each(response.list, function(index, value) {
              html += '<tr>';
              html += '<td class="text-center">' + value.date_in + '</td>';
              html += '<td class="text-center">' + value.type_text + '</td>';
              html += '<td class="text-center"><input type="hidden" name="type[]" value="' + value.type +
                '"><input type="hidden" name="parent_id[]" value="' + value.parent_id +
                '"><input type="hidden" name="product_id[]" value="' + value.product_id +
                '"><input type="hidden" name="quantity[]" value="' + value.quantity + '">' + value
                .quantity + '</td>';
              html += '<td class="text-center">' + value.keterangan + '</td>';
              html +=
                '<td class="text-center"><input type="number" class="form-control text-center" name="quantity_recondition[]" min="0" value="0"></td>';
              html +=
                '<td class="text-center"><input type="number" class="form-control text-center" name="quantity_disposal[]" min="0" value="0"></td>';
              html += '</tr>';
            });

            html += '</tbody></table></div></div>';

            $('#list-result').prepend(html);
            $('#product-' + response.id).DataTable({
              paging: false,
              searching: false,
              columns: [{
                  width: '15%',
                  render: function(data, type, row) {
                    if (data && data != 'null') {
                      return moment(data).format('DD/MM/YYYY')
                    } else {
                      return '-';
                    }
                  }
                },
                {
                  width: '10%'
                },
                {
                  width: '10%'
                },
                {
                  width: '35%'
                },
                {
                  width: '15%'
                },
                {
                  width: '15%'
                }
              ]
            });
          });

        }
      });

      $('body').on('click', '.btn-remove-block', function() {
        $(this).parents('.block-result').remove();
      });

    });

  </script>
@endpush
