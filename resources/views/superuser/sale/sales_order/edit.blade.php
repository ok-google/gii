@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.sale.sales_order.index') }}">Sales Order</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.sale.sales_order.update', $sales_order->id) }}" data-type="POST" enctype="multipart/form-data">
  <input type="hidden" name="_method" value="PUT">
  <input type="hidden" name="ids_delete" value="">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Edit Sales Order</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $sales_order->code }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="marketplace_order">Marketplace Order <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="marketplace_order" name="marketplace_order" onkeyup="nospaces(this)" value="{{ $sales_order->marketplace_order() }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
            <option></option>
            @foreach($warehouses as $warehouse)
              <option value="{{ $warehouse->id }}" {{ ($warehouse->id == $sales_order->warehouse_id ) ? 'selected' : '' }}>{{ $warehouse->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      @if ($sales_order->marketplace_order == \App\Entities\Sale\SalesOrder::MARKETPLACE_ORDER['Offline'])
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Customer <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Customer">
            <option></option>
            @foreach($customers as $customer)
              <option value="{{ $customer->id }}" {{ ($customer->id == $sales_order->customer_id ) ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      @else
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Customer <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="customer" name="customer" value="{{ $sales_order->customer_marketplace }}">
        </div>
      </div>
      @endif

      @if ($sales_order->marketplace_order != \App\Entities\Sale\SalesOrder::MARKETPLACE_ORDER['Offline'])
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address_marketplace">Address</label>
        <div class="col-md-7">
          <textarea class="form-control" id="address_marketplace" name="address_marketplace">{{ $sales_order->address_marketplace }}</textarea>
        </div>
      </div>
      @endif

      @if ($sales_order->marketplace_order == \App\Entities\Sale\SalesOrder::MARKETPLACE_ORDER['Offline'])
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="ekspedisi" name="ekspedisi" data-placeholder="Select Ekspedisi">
            <option></option>
            @foreach($ekspedisis as $ekspedisi)
              <option value="{{ $ekspedisi->id }}" {{ ($ekspedisi->id == $sales_order->ekspedisi_id ) ? 'selected' : '' }}>{{ $ekspedisi->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      @else
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="ekspedisi" name="ekspedisi" value="{{ $sales_order->ekspedisi_marketplace }}">
        </div>
      </div>
      @endif
      
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="resi">Resi</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="resi" name="resi" value="{{ $sales_order->resi }}">
        </div>
      </div>

      <hr class="my-20">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="store_name">Store Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="store_name" name="store_name" value="{{ $sales_order->store_name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="store_phone">Store Phone <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="store_phone" name="store_phone" value="{{ $sales_order->store_phone }}">
        </div>
      </div>

      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.sale.sales_order.index') }}">
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
            <th class="text-center">Product</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Price</th>
            <th class="text-center">Total</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($sales_order->sales_order_details as $detail)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><select class="js-select2 form-control js-ajax" id="sku[{{ $loop->iteration }}]" name="sku[]" data-placeholder="Select SKU" style="width:100%" required><option value="{{ $detail->product_id }}">{{ $detail->product->code }}</option></select></td>
              <td><span class="name">{{ $detail->product->name }}</span><input type="hidden" class="form-control" name="edit[]" value="{{ $detail->id }}"></td>
              <td><input type="number" class="form-control" name="quantity[]" value="{{ $detail->quantity }}" required></td>
              <td><input type="number" class="form-control" name="price[]" value="{{ $detail->price }}" required></td>
              <td><input type="number" class="form-control" name="total[]" readonly value="{{ $detail->total }}"></td>
              <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="block-header block-header-default">
      <div class="container">
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="subtotal">IDR Sub Total</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="subtotal" name="subtotal" readonly value="{{ $sales_order->total }}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="tax">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="tax_checked" name="tax_checked" {{ $sales_order->tax != '0' && $sales_order->tax != '' ? 'checked' : ''}}>
              <label class="form-check-label" for="tax_checked">
                Tax
              </label>
            </div>
          </label>
          <div class="col-md-2">
            <input type="number" class="form-control" id="tax" name="tax" readonly value="{{ $sales_order->tax }}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="discount">IDR Discount</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="discount" name="discount" value="{{ $sales_order->discount }}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="shipping_fee">Courier</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="shipping_fee" name="shipping_fee" value="{{ $sales_order->shipping_fee }}">
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="grand_total">IDR Total</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="grand_total" name="grand_total" readonly value="{{ $sales_order->grand_total }}">
          </div>
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
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()
    initailizeSelect2();
  
    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'sku', orderable: false, width: "25%"},
          {name: 'name', orderable: false, searcable: false},
          {name: 'quantity', orderable: false, searcable: false, width: "5%"},
          {name: 'price', orderable: false, searcable: false, width: "17%"},
          {name: 'total', orderable: false, searcable: false, width: "17%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1000;
  
    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
                    '<span class="name"></span><input type="hidden" class="form-control" name="edit[]" value="">',
                    '<input type="number" class="form-control" name="quantity[]" readonly required>',
                    '<input type="number" class="form-control" name="price[]" readonly required>',
                    '<input type="number" class="form-control" name="total[]" readonly>',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
                  // $('.js-select2').select2()
                  initailizeSelect2();
      counter++;
    });

    function initailizeSelect2(){
      $(".js-ajax").select2({
        ajax: {
          url: '{{ route('superuser.sale.sales_order.search_sku') }}',
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

      $('.js-ajax').on('select2:select', function (e) {
        var name = e.params.data.name;
        $(this).parents('tr').find('.name').text(name);
        $(this).parents('tr').find('input[name="quantity[]"]').removeAttr('readonly');
        $(this).parents('tr').find('input[name="price[]"]').removeAttr('readonly');
      });

    };
    
  
    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();

      parent = $(this).parents('tr');
      edit = parent.find('input[name="edit[]"]').val();
      if(edit) {
        ids_delete = $('input[name="ids_delete"]').val();
        $('input[name="ids_delete"]').val(edit+','+ids_delete);
      }

      table.row( $(this).parents('tr') ).remove().draw();
      
      var subtotal = 0;
      $('input[name="total[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#subtotal').val(subtotal);

      $("#tax_checked").change();
      grandtotal();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="quantity[]"]', function (e) {
      var price = $(this).parents('tr').find('input[name="price[]"]').val();
      var total = $(this).val() * price;

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="price[]"]', function (e) {
      var quantity = $(this).parents('tr').find('input[name="quantity[]"]').val();
      var total = $(this).val() * quantity;

      $(this).parents('tr').find('input[name="total[]"]').val(total);
      $(this).parents('tr').find('input[name="total[]"]').change();
    });
    
    $('#datatable tbody').on( 'change', 'input[name="total[]"]', function (e) {
      var subtotal = 0;
      $('input[name="total[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#subtotal').val(subtotal);

      $("#tax_checked").change();
      grandtotal();
    });

    $("#tax_checked").change(function() {
        if(this.checked) {
          var tax = ($('#subtotal').val() * 10) / 100;

          $('#tax').val(tax);
        } else {
          $('#tax').val('');
        }
        grandtotal();
    });

    $("#discount").on('keyup', function() {
        grandtotal();
    });

    $("#shipping_fee").on('keyup', function() {
        grandtotal();
    });

    function grandtotal() {
      var subtotal = Number($('#subtotal').val());
      var tax = Number($('#tax').val());
      var discount = Number($('#discount').val());
      var shipping_fee = Number($('#shipping_fee').val());
      var grandtotal = subtotal + tax - discount + shipping_fee;

      $('#grand_total').val(grandtotal);
    }
  
    function delay(fn, ms) {
      let timer = 0
      return function(...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
    }
  
  });
</script>
@endpush
