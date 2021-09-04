@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.inventory.recondition.index') }}">Recondition</a>
    <span class="breadcrumb-item active">Edit</span>
  </nav>
  <div id="alert-block"></div>

  <form class="ajax" data-action="{{ route('superuser.inventory.recondition.update', $recondition->id) }}"
    data-type="POST" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="ids_delete" value="">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Edit Recondition</h3>
      </div>
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
          <div class="col-md-7">
            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $recondition->code }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span
              class="text-danger">*</span></label>
          <div class="col-md-7">
            <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
              <option></option>
              @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}"
                  {{ $warehouse->id == $recondition->warehouse_id ? 'selected' : '' }}>{{ $warehouse->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row pt-30">
          <div class="col-md-6">
            <a href="{{ route('superuser.inventory.recondition.index') }}">
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

    <div id="list-result">
      @foreach ($collect as $key => $value)
        <div class="js-animation-object animated fadeIn block block-themed block-rounded block-result">
          <div class="block-header bg-earth-dark">
            <h3 class="block-title">{{ $value['title'] }}</h3>
            <div class="block-options"><button type="button" class="btn-block-option btn-remove-block"><i
                  class="fa fa-trash"></i></button></div>
          </div>
          <div class="block-content">
            <table id="product-{{ $key }}" class="table table-striped table-vcenter table-responsive">
              <thead>
                <tr>
                  <th class="text-center">Date In</th>
                  <th class="text-center">In From</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-center">Keterangan</th>
                  <th class="text-center">Recondition</th>
                  <th class="text-center">Disposal</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($value['list'] as $list)
                  <tr>
                    <td class="text-center">{{ $list['date_in'] }}</td>
                    <td class="text-center">{{ $list['type_text'] }}</td>
                    <td class="text-center">
                      <input type="hidden" name="id[]" value="{{ $list['id'] }}">
                      <input type="hidden" name="type[]" value="{{ $list['type'] }}">
                      <input type="hidden" name="parent_id[]" value="{{ $list['parent_id'] }}">
                      <input type="hidden" name="product_id[]" value="{{ $key }}">
                      <input type="hidden" name="quantity[]" value="{{ $list['quantity'] }}">
                      {{ $list['quantity'] }}
                    </td>
                    <td class="text-center">{{ $list['keterangan'] }}</td>
                    <td class="text-center">
                      <input type="number" class="form-control text-center" name="quantity_recondition[]" min="0"
                        value="{{ $list['quantity_recondition'] }}" required>
                    </td>
                    <td class="text-center"><input type="number" class="form-control text-center"
                        name="quantity_disposal[]" min="0" value="{{ $list['quantity_disposal'] }}" required></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endforeach
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

      $('.table').DataTable({
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


      $('body').on('click', '.btn-remove-block', function() {

        ids_delete = $('input[name="ids_delete"]').val();
        var collect_delete = '';
        $(this).parents('.block-result').find('input[name^="id[]"]').each(function() {
          collect_delete = $(this).val() + ',' + collect_delete;
        });
        
        $('input[name="ids_delete"]').val(collect_delete + ids_delete);

        $(this).parents('.block-result').remove();
      });

    });

  </script>
@endpush
