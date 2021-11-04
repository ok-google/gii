@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <a class="breadcrumb-item" href="{{ route('superuser.finance.receipt_invoice.index') }}">Cash/Bank Receipt (Inv)</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.finance.receipt_invoice.store') }}" data-type="POST" enctype="multipart/form-data">
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
            <th class="text-center"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="subtotal_total">Grand Total</label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="subtotal_total" name="subtotal_total" readonly>
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="subtotal_paid" name="subtotal_paid" readonly>
        </div>
        <div class="col-md-1">
        </div>
      </div>
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
<<<<<<< Updated upstream
                total: item.total
=======
<<<<<<< HEAD
                total: item.total,
                marketplace: item.marketplace,
                grand_total: item.grand_total
=======
                total: item.total
>>>>>>> main
>>>>>>> Stashed changes
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
          {name: 'action', orderable: false, searcable: false, width: "9%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1;
  
    $('#add').on( 'click', function (e) {
      e.preventDefault();
      var select_so = $('#select_so').select2('data');

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
          table.row.add([
                      counter,
                      '<input type="hidden" name="so_id[]" value="'+select_so[0]['id']+'"><span>'+select_so[0]['text']+'</span>',
                      '<input type="number" class="form-control" name="total[]" value="'+total+'" required readonly>',
                      '<input type="number" class="form-control" name="paid[]" min="1" max="'+total+'" value="" required>',
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
