@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Inventory</span>
  <a class="breadcrumb-item" href="{{ route('superuser.inventory.stock_adjusment.index') }}">Stock Adjusment</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.inventory.stock_adjusment.update', $stock_adjusment->id) }}" data-type="POST" enctype="multipart/form-data">
  <input type="hidden" name="_method" value="PUT">
  <input type="hidden" name="ids_delete" value="">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Edit Stock Adjusment</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $stock_adjusment->code }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Select Inventory <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Inventory" data-value="{{ $stock_adjusment->warehouse_id }}" disabled>
            @foreach($warehouses_display as $item)
              <option></option>
              <option value="{{ $item->id }}" {{ $stock_adjusment->warehouse_id == $item->id ? 'selected': '' }}>{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="minus">Minus</label>
        <div class="col-md-7">
          <div class="form-check">
            <input class="form-check-input" style="margin-top: 10px" type="checkbox" id="minus" name="minus" {{ $stock_adjusment->minus == 1 ? 'checked': '' }}>
          </div>
        </div>
      </div>
    </div>
    <hr>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_sku">Select SKU</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="select_sku" name="select_sku" data-placeholder="Select SKU">
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="product_name">Product Name</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="product_name" name="product_name" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="qty">Qty</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="qty" name="qty">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Description</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="description" name="description">
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-10 text-right">
          <a href="#" id="add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              <i class="fa fa-plus mr-10"></i> ADD
            </button>
          </a>
        </div>
      </div>
    </div>
    <hr>
    <div class="block-content">
      <table id="datatable" class="table table-striped table-vcenter table-responsive">
        <thead>
          <tr>
            <th class="text-center">Counter</th>
            <th class="text-center">SKU</th>
            <th class="text-center">Product Name</th>
            <th class="text-center">Qty</th>
            <th class="text-center">Price</th>
            <th class="text-center">Total</th>
            <th class="text-center">Description</th>
            <th class="text-center"></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($stock_adjusment->details as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <input type="hidden" name="col_product_id[]" value="{{ $item->product_id }}">
                  <input type="hidden" class="form-control" name="edit[]" value="{{ $item->id }}">
                  <span>{{ $item->product->code }}</span>
                </td>
                <td>
                  <span>{{ $item->product->name }}</span>
                </td>
                <td>
                  <input type="number" class="form-control" name="col_qty[]" min="1" value="{{ $item->qty }}" required>
                </td>
                <td>
                  <input type="number" class="form-control" name="col_price[]" min="0" value="{{ $item->price }}">
                </td>
                <td>
                  <i>calculation after acc</i>
                </td>
                <td>
                  <input type="text" class="form-control" name="col_description[]" value="{{ $item->description }}">
                </td>
                <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
              </tr>
          @endforeach
        </tbody>
      </table>
      <div class="form-group row">
        {{-- <label class="col-md-3 col-form-label" for="select_sku">Select SKU</label> --}}
        <small class="col-md-12 form-text text-muted text-right">* Price will be used if HPP is not available, otherwise it will be ignored.</small>
      </div>
      <hr>
    </div>
    
    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.inventory.stock_adjusment.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    function addLoadSpiner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          $("#img_" + el[0].id).css('display', 'block');
        }               
        else {
          var img = $('<img class="ddloading">');
          img.attr('id', "img_" + el[0].id);
          img.attr('src', 'http://ajaxloadingimages.net/gif/image?imageid=aero-spinner&forecolor=000000&backcolor=ffffff&transparent=true');
          img.css({ 'display': 'inline-block', 'width': '25px', 'height': '25px', 'position': 'absolute', 'left': '50%', 'margin-top': '5px' });
          img.prependTo(el[0].nextElementSibling);
        }
        el.prop("disabled", true);               
      }
    }

    function hideLoadSpinner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          setTimeout(function () {
            $("#img_" + el[0].id).css('display', 'none');
            el.prop("disabled", false);
          }, 500);                  
        }
      }
    }

    $('#warehouse').on('select2:select', function (e) {
      // table.clear().draw();
      $.ajax({
        url: '{{ route('superuser.inventory.stock_adjusment.get_sku') }}',
        data: {id:$(this).val() , _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        beforeSend: function () {
          addLoadSpiner($('#select_sku'))
        },
        complete: function () {
          hideLoadSpinner($('#select_sku'))
        },
        success: function(json) {
          $('#select_sku').empty().trigger('change');
          clearForm();
          
          if (json.code == 200) {
            let ph = new Option('', '', false, false);
            $('#select_sku').append(ph).trigger('change');
            
            for (i = 0; i < Object.keys(json.data).length; i++) {
              let newOption = '<option value="'+ json.data[i].product_id +'" data-product_name="'+ json.data[i].product_name +'">'+ json.data[i].sku +'</option>';
              $('#select_sku').append(newOption).trigger('change');
            }
          }
        }
      });
    });

    $('#select_sku').on('select2:select', function (e) {
      $('#product_name').val( $(this).find(':selected').data('product_name') );
    });

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: null, orderable: false},
          {name: 'action', orderable: false, searcable: false, width: "9%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1;
  
    $('#add').on( 'click', function (e) {
      e.preventDefault();

      var product_id = $('#select_sku').val() ?? '';
      var product_name = $('#product_name').val() ?? '';
      var sku = $('#select_sku').select2('data')[0].text ?? '';
      var qty = $('#qty').val() ?? '';
      var description = $('#description').val() ?? '';

      var duplicate = 0;
      $('input[name="col_product_id[]"]').each( function  () {
        if($(this).val() == product_id) {
          duplicate = 1;
        } 
      });

      if(duplicate == 1) {
        alert('SKU is already in the table.')
      } else {
        if(product_id) {
          table.row.add([
                      counter,
                      '<input type="hidden" name="col_product_id[]" value="'+product_id+'"><input type="hidden" class="form-control" name="edit[]" value=""><span>'+sku+'</span>',
                      '<span>'+product_name+'</span>',
                      '<input type="number" class="form-control" name="col_qty[]" min="1" value="'+qty+'" required>',
                      '<input type="number" class="form-control" name="col_price[]" min="0" value="">',
                      '<i>calculation after acc</i>',
                      '<input type="text" class="form-control" name="col_description[]" value="'+description+'">',
                      '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw( false );
          counter++;

          clearForm();
        }
      }
    });

    function clearForm() {
      $('#select_sku').val(null).trigger("change");

      $('#product_name').val('');
      $('#qty').val('');
      $('#description').val('');
    }

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();

      parent = $(this).parents('tr');
      edit = parent.find('input[name="edit[]"]').val();
      if(edit) {
        ids_delete = $('input[name="ids_delete"]').val();
        $('input[name="ids_delete"]').val(edit+','+ids_delete);
      }

      table.row( $(this).parents('tr') ).remove().draw();
    });

    let data_val = $('#warehouse').data('value')
    if (data_val > 0) {
      $('#warehouse').val(data_val);
      $('#warehouse').trigger('select2:select');
    }

  })
</script>
@endpush
