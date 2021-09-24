@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.sale.buy_back.index') }}">Buy Back</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.sale.buy_back.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Buy Back</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="sales_order">Select Invoice <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control js-select2-so" id="sales_order" name="sales_order" data-placeholder="Select Invoice">
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Select Inventory <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Inventory">
            @foreach($warehouses_id as $item)
              <option></option>
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="disposal">Is Disposal?</label>
        <div class="col-md-7">
          <div class="form-check">
            <input class="form-check-input" style="margin-top: 10px" type="checkbox" id="disposal" name="disposal">
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
        <label class="col-md-3 col-form-label text-right" for="sell_price">Sell Price</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="sell_price" name="sell_price" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="buy_back_price">Buy Back Price</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="buy_back_price" name="buy_back_price">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="buy_back_qty">Buy Back Qty</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="buy_back_qty" name="buy_back_qty">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="buy_back_total">Buy Back Total</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="buy_back_total" name="buy_back_total" readonly>
        </div>
      </div>
      <input type="hidden" class="form-control" id="sales_order_detail_id" name="sales_order_detail_id">
      <input type="hidden" class="form-control" id="sku" name="sku">
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
            <th class="text-center">Sell Price</th>
            <th class="text-center">Buy Back Price</th>
            <th class="text-center">Buy Back Qty</th>
            <th class="text-center">Buy Back Total</th>
            <th class="text-center"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <hr>
    </div>
    
    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.sale.buy_back.index') }}">
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

    $(".js-select2-so").select2({
      ajax: {
        url: '{{ route('superuser.sale.buy_back.search_so') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}"
          };
        },
        cache: true
      },
      minimumInputLength: 3,
    });

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

    $('#sales_order').on('select2:select', function (e) {
      table.clear().draw();
      $.ajax({
        url: '{{ route('superuser.sale.buy_back.get_sku') }}',
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
              let newOption = '<option value="'+ json.data[i].sales_order_detail_id +'" data-sku="'+ json.data[i].sku +'" data-product_name="'+ json.data[i].product_name +'" data-sell_price="'+ json.data[i].sell_price +'" data-quantity="'+ json.data[i].quantity +'">'+ json.data[i].sku +'</option>';
              $('#select_sku').append(newOption).trigger('change');
            }
          }
        }
      });
    });

    $('#select_sku').on('select2:select', function (e) {
      var data = e.params.data;
      
      $('#product_name').val( $(this).find(':selected').data('product_name') );
      $('#sell_price').val( $(this).find(':selected').data('sell_price') );
      $('#sales_order_detail_id').val( $(this).val() );
      $('#sku').val( $(this).find(':selected').data('sku') );
      $('#buy_back_qty').prop('placeholder', $(this).find(':selected').data('quantity') );
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

      var sales_order_detail_id = $('#sales_order_detail_id').val() ?? '';
      var product_name = $('#product_name').val() ?? '';
      var sku = $('#sku').val() ?? '';
      var sell_price = $('#sell_price').val() ?? '';
      var buy_back_price = $('#buy_back_price').val() ?? '';
      var buy_back_qty = $('#buy_back_qty').val() ?? '';
      var buy_back_total = $('#buy_back_total').val() ?? '';

      var max_quantity = $('#buy_back_qty').prop('placeholder');

      var duplicate = 0;
      $('input[name="col_so_detail[]"]').each( function  () {
        if($(this).val() == sales_order_detail_id) {
          duplicate = 1;
        } 
      });

      if(duplicate == 1) {
        alert('SKU is already in the table.')
      } else {
        if(sales_order_detail_id) {
          table.row.add([
                      counter,
                      '<input type="hidden" name="col_so_detail[]" value="'+sales_order_detail_id+'"><span>'+sku+'</span>',
                      '<span>'+product_name+'</span>',
                      '<span>'+sell_price+'</span>',
                      '<input type="number" class="form-control" name="col_buy_back_price[]" min="1" value="'+buy_back_price+'" required>',
                      '<input type="number" class="form-control" name="col_buy_back_qty[]" min="1" max="'+max_quantity+'" placeholder="'+max_quantity+'" value="'+buy_back_qty+'" required>',
                      '<input type="number" class="form-control" name="col_buy_back_total[]" min="1" value="'+buy_back_total+'" required readonly>',
                      '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw( false );
          counter++;

          clearForm();
        }
      }
    });

    function clearForm() {
      $('#select_sku').val(null).trigger("change");

      $('#sales_order_detail_id').val('');
      $('#product_name').val('');
      $('#sku').val('');
      $('#sell_price').val('');
      $('#buy_back_price').val('');
      $('#buy_back_qty').val('');
      $('#buy_back_qty').prop('placeholder', '');
      $('#buy_back_total').val('');
    }

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();
    });

    $('#datatable tbody').on( 'keyup', 'input[name="col_buy_back_price[]"], input[name="col_buy_back_qty[]"]', function (e) {
      var col_buy_back_price = $(this).parents('tr').find('input[name="col_buy_back_price[]"]').val();
      var col_buy_back_qty = $(this).parents('tr').find('input[name="col_buy_back_qty[]"]').val();

      $(this).parents('tr').find('input[name="col_buy_back_total[]"]').val(col_buy_back_price * col_buy_back_qty);
    });

    $('body').on( 'keyup', 'input[name="buy_back_price"], input[name="buy_back_qty"]', function (e) {
      $('#buy_back_total').val( $('#buy_back_price').val() * $('#buy_back_qty').val() );
    });

  })
</script>
@endpush
