@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <a class="breadcrumb-item" href="{{ route('superuser.finance.receipt_invoice.index') }}">Cash/Bank Receipt (Inv)</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" id="form-ajax" data-action="{{ route('superuser.finance.receipt_invoice.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Create Cash/Bank Receipt (Inv)</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_date">Select Date <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="date" class="form-control" id="select_date" name="select_date">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="coa">Select Account</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="coa" name="coa" data-placeholder="Select COA">
            @foreach($coa as $item)
              <option></option>
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      {{-- <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Select Customer</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Customer">
            @foreach($customers as $item)
              <option></option>
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address</label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address" readonly></textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Note</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="note" name="note"></textarea>
        </div>
      </div> --}}
    </div>
    <hr>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_so">Select Invoice</label>
        <div class="col-md-7">
          <select class="form-control" id="select_so" name="select_so" data-placeholder="Select Invoice">
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_total">Total</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="select_total" name="select_total" readonly>
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
            <th class="text-center">Invoice</th>
            <th class="text-center">Total</th>
            <th class="text-center">Paid</th>
            <th class="text-center">Type Payment</th>
            <th class="text-center"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tbody>
          <tr>
            <td >Grand Total</td>
            <td><input type="text" class="form-control" id="subtotal_total" name="subtotal_total" readonly></td>
            <td><input type="text" class="form-control" id="subtotal_paid" name="subtotal_paid" readonly></td>
            <td colspan="2">&nbsp;</td>
          </tr>
        </tbody>
      </table>
      {{-- <div class="form-group row ">
        <label class="col-md-2 col-form-label text-right" for="subtotal_total">Grand Total</label>
        <div class="col-md-3 ml-0">
          <input type="text" class="form-control" id="subtotal_total" name="subtotal_total" readonly>
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="subtotal_paid" name="subtotal_paid" readonly>
        </div>
        <div class="col-md-1">
        </div>
      </div> --}}
      <hr>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="total_receipt">Total Payment</label>
        <div class="col-md-6">
          <input type="text" class="form-control" id="total_receipt" name="total_receipt" readonly>
        </div>
        {{-- <div class="col-md-1">
        </div> --}}
      </div>
    </div>
    
    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.finance.receipt_invoice.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="button" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="mp_payment" name="mp_payment" value="">
  <input type="hidden" id="mp_cost1" name="mp_cost1" value="">
  <input type="hidden" id="mp_cost2" name="mp_cost2" value="">
  <input type="hidden" id="mp_cost3" name="mp_cost3" value="">
  <input type="hidden" id="mp_total" name="mp_total" value=""> 
  <input type="hidden" id="mp_coa_payment" name="mp_coa_payment" value="">
  <input type="hidden" id="mp_coa_cost1" name="mp_coa_cost1" value="">
  <input type="hidden" id="mp_coa_cost2" name="mp_coa_cost2" value="">
  <input type="hidden" id="mp_coa_cost3" name="mp_coa_cost3" value="">
  <input type="hidden" id="mp_coa_credit" name="mp_coa_credit" value=""> 
</form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@include('superuser.component.modal-manage-marketplace-receipt')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    $("#submit-table").click(function(e){
      // e.preventDefault();
      // alert("asd");
      var type_cost = $(".type");
      let payment = 0; let cost_1 = 0; let cost_2 = 0; let cost_3 = 0;
      type_cost.each(function(){
        var ths = $(this).val();
        var paid = $(this).parent().parent().find(".paid").val();
        if(ths == "payment"){
          payment += parseFloat(paid);
        }else if(ths == "cost_1"){
          cost_1 += parseFloat(paid);
        }else if(ths == "cost_2"){
          cost_2 += parseFloat(paid);
        }else if(ths == "cost_3"){
          cost_3 += parseFloat(paid);
        }
      });
      var total = parseFloat(payment)+parseFloat(cost_1)+parseFloat(cost_2)+parseFloat(cost_3);

      //assign to modal
      $("#payment_modal").html(payment);
      $("#cost_1_modal").html(cost_1);
      $("#cost_2_modal").html(cost_2);
      $("#cost_3_modal").html(cost_3);
      $("#total_modal").html(total);

      //assign to input
      $("#mp_payment").val(payment);
      $("#mp_cost1").val(cost_1);
      $("#mp_cost2").val(cost_2);
      $("#mp_cost3").val(cost_3);
      $("#mp_total").val(total);
      
      $("#modal-manage-mr").modal('show');
    });

    $("#form-mr").submit(false);

    $("#process-mp-receipt").click(function(){
      $("#form-ajax").submit();
    })

    function addLoadSpiner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          $("#img_" + el[0].id).css('display', 'block');
        }else {
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

    $('#customer').on('select2:select', function (e) {
      table.clear().draw();
      // $.ajax({
      //   url: '{{ route('superuser.finance.receipt_invoice.get_sales_order') }}',
      //   data: {id:$(this).val() , _token: "{{csrf_token()}}"},
      //   type: 'POST',
      //   cache: false,
      //   dataType: 'json',
      //   beforeSend: function () {
      //     addLoadSpiner($('#select_so'))
      //   },
      //   complete: function () {
      //     hideLoadSpinner($('#select_so'))
      //   },
      //   success: function(json) {
      //     $('#select_so').empty().trigger('change');
      //     $('#address').val('');
          
      //     if (json.code == 200) {
      //       let ph = new Option('', '', false, false);
      //       $('#select_so').append(ph).trigger('change');

      //       for (i = 0; i < Object.keys(json.data).length; i++) {
      //         let newOption = '<option value="'+ json.data[i].so_id +'" data-total="'+ json.data[i].total +'">'+ json.data[i].code +'</option>';
      //         $('#select_so').append(newOption).trigger('change');
      //       }

      //       $('#address').val(json.address);
      //     }
      //   }
      // });
    });

    $('#select_so').select2({
    allowClear: true,
    placeholder: 'Select Invoice',
    ajax: {
      url: '{{ route('superuser.finance.receipt_invoice.get_sales_order') }}',
      type: 'POST',
      dataType: 'json',
      data: function(params) {
        return {
          q: (params.term??''), 
          _token: "{{csrf_token()}}"
        }
      },
      processResults: function (data) {
        // Transforms the top-level key of the response object from 'items' to 'results'
        return {
          results: $.map(data, function (item) {
            //console.log(item)
              return {
                text: item.code,
                id: item.id,
                total: item.total,
                marketplace: item.marketplace,
                grand_total: item.grand_total
              }
          })
        };
      }
    }
  }).on('select2:select', function (e) {
      var data = e.params.data;
      // console.log(data)
      var total = data.total;
      $('#select_total').val(total);
    });

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'invoice', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "25%"},
          {name: 'paid', orderable: false, searcable: false, width: "25%"},
          {name: 'type', orderable: false, searcable: false, width: "25%"},
          {name: 'action', orderable: false, searcable: false, width: "9%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1;
  
    $('#add').on( 'click', function (e) {
      e.preventDefault();
      var select_so = $('#select_so').select2('data');
      // console.log('aaa',select_so[0]['marketplace'])
      // return;
      var name_credit = $('#name_credit').val() ?? '';
      var total = $('#select_total').val() ?? '';

      var duplicate = 0;
      $('input[name="so_id[]"]').each( function  () {
        if($(this).val() == select_so[0]['id']) {
          duplicate = 1;
        } 
      });

      if(duplicate == 1) {
        alert('Invoice is already in the table.')
      } else {
        if(select_so[0]['id']) {
          var type = '<select class="form-control type" name="type[]"><option value=""></option><option value="payment">Payment</option><option value="cost_1">Cost 1</option><option value="cost_2">Cost 2</option><option value="cost_3">Cost 3</option></select>';
          if(select_so[0]['marketplace'] == 0){
            type = '';
          }
          table.row.add([
                      counter,
                      '<input type="hidden" name="grand_total[]" value="'+select_so[0]['grand_total']+'"><input type="hidden" name="inv_code[]" value="'+select_so[0]['text']+'"><input type="hidden" name="marketplace[]" value="'+select_so[0]['marketplace']+'"><input type="hidden" name="so_id[]" value="'+select_so[0]['id']+'"><span>'+select_so[0]['text']+'</span>',
                      '<input type="number" class="form-control" name="total[]" value="'+total+'" required readonly>',
                      '<input type="number" class="form-control paid" name="paid[]" min="1" max="'+total+'" value="" required>',
                      type,
                      '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw( false );
          counter++;

          $('#select_total').val('')
          $('#select_so').val(null).trigger("change")
          grandTotal()
        }
      } 
    });

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      grandTotal()
    });

    $('#datatable tbody').on( 'keyup', 'input[name="paid[]"]', function (e) {
      grandTotal();
    });

    function grandTotal() {
      var subtotal_total = 0;
      $('input[name="total[]"]').each(function(){
        subtotal_total += Number($(this).val());
      });
      $('#subtotal_total').val(subtotal_total);

      var subtotal_paid = 0;
      $('input[name="paid[]"]').each(function(){
        subtotal_paid += Number($(this).val());
      });
      $('#subtotal_paid').val(subtotal_paid);
      $('#total_receipt').val('Rp. '+subtotal_paid.toLocaleString('id-ID', {maximumFractionDigits:2}));
      
    }

  })
</script>
@endpush
