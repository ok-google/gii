@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <a class="breadcrumb-item" href="{{ route('superuser.finance.payment.index') }}">Cash/Bank Payment</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.finance.payment.update', $payment->id) }}" data-type="POST" enctype="multipart/form-data">
  <input type="hidden" name="_method" value="PUT">
  <input type="hidden" name="ids_delete" value="">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Edit Cash/Bank Payment</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="transaction">Transaction <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="transaction" name="transaction" onkeyup="nospaces(this)" value="{{ $payment->code }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_date">Select Date <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="date" class="form-control" id="select_date" name="select_date" value="{{ $payment->select_date ? date('Y-m-d', strtotime($payment->select_date)) : '' }}">
        </div>
      </div>
    </div>
    
    <div class="form-group row">
      <div class="col-md-6">
        <div class="block-header block-header-default">
          <h3 class="block-title text-center">Debet</h3>
        </div>
        <div class="block-content">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="coa_debet">Account</label>
            <div class="col-md-7">
              <select class="js-select2 select2-debet form-control" id="coa_debet" name="coa_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="total_debet">Total</label>
            <div class="col-md-7">
              <input type="number" class="form-control" id="total_debet" name="total_debet">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-10 text-right">
              <a href="#" id="add_debet">
                <button type="button" class="btn bg-gd-sea border-0 text-white">
                  <i class="fa fa-plus mr-10"></i> ADD
                </button>
              </a>
            </div>
          </div>
        </div>
        <hr>
        <div class="block-content">
          <table id="datatable_debet" class="table table-striped table-vcenter table-responsive">
            <thead>
              <tr>
                <th class="text-center">Counter</th>
                <th class="text-center">Account</th>
                <th class="text-center">Total</th>
                <th class="text-center"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($payment_debet as $item)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <input type="hidden" name="coa_debet_detail[]" value="{{ $item->coa_id }}">
                      <input type="hidden" class="form-control" name="edit_debet_detail[]" value="{{ $item->id }}">
                      <span>{{ $item->coa->name }}</span>
                    </td>
                    <td>
                      <input type="number" class="form-control" name="total_debet_detail[]" value="{{ $item->total }}" required>
                    </td>
                    <td>
                      <a href="#" class="row-delete-debet">
                        <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                          <i class="fa fa-trash"></i>
                        </button>
                      </a>
                    </td>
                  </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th class="text-center"></th>
                <th class="text-right">Grand Total</th>
                <th class="text-center">
                  <input type="number" class="form-control" id="subtotal_debet" name="subtotal_debet" step=".01" readonly>
                </th>
                <th class="text-center"></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="col-md-6">
        <div class="block-header block-header-default">
          <h3 class="block-title text-center">Credit</h3>
        </div>
        <div class="block-content">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="coa_credit">Account</label>
            <div class="col-md-7">
              <select class="js-select2 select2-credit form-control" id="coa_credit" name="coa_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="total_credit">Total</label>
            <div class="col-md-7">
              <input type="number" class="form-control" id="total_credit" name="total_credit">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-10 text-right">
              <a href="#" id="add_credit">
                <button type="button" class="btn bg-gd-sea border-0 text-white">
                  <i class="fa fa-plus mr-10"></i> ADD
                </button>
              </a>
            </div>
          </div>
        </div>
        <hr>
        <div class="block-content">
          <table id="datatable_credit" class="table table-striped table-vcenter table-responsive">
            <thead>
              <tr>
                <th class="text-center">Counter</th>
                <th class="text-center">Account</th>
                <th class="text-center">Total</th>
                <th class="text-center"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($payment_credit as $item)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <input type="hidden" name="coa_credit_detail[]" value="{{ $item->coa_id }}">
                      <input type="hidden" class="form-control" name="edit_credit_detail[]" value="{{ $item->id }}">
                      <span>{{ $item->coa->name }}</span>
                    </td>
                    <td><input type="number" class="form-control" name="total_credit_detail[]" value="{{ $item->total }}" required></td>
                    <td><a href="#" class="row-delete-credit"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
                  </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th class="text-center"></th>
                <th class="text-right">Grand Total</th>
                <th class="text-center">
                  <input type="number" class="form-control" id="subtotal_credit" name="subtotal_credit" step=".01" readonly>
                </th>
                <th class="text-center"></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
    
    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.finance.payment.index') }}">
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
    grandtotalDebet();
    grandtotalCredit();

    // CREDIT
    $('.select2-credit').select2()

    var table_credit = $('#datatable_credit').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'coa', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "35%"},
          {name: 'action', orderable: false, searcable: false, width: "9%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1000;
  
    $('#add_credit').on( 'click', function (e) {
      e.preventDefault();
      var select2_credit = $('.select2-credit').select2('data'); 
      var total_credit = $('#total_credit').val() ?? '';

      if(select2_credit[0]['id']) {
        table_credit.row.add([
                    counter,
                    '<input type="hidden" name="coa_credit_detail[]" value="'+select2_credit[0]['id']+'"><input type="hidden" class="form-control" name="edit_credit_detail[]" value=""><span>'+select2_credit[0]['text']+'</span>',
                    '<input type="number" class="form-control" name="total_credit_detail[]" value="'+total_credit+'" required>',
                    '<a href="#" class="row-delete-credit"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
        counter++;

        $('#total_credit').val('')
        $('.select2-credit').val(null).trigger("change")
        grandtotalCredit()
      }
      
    });

    $('#datatable_credit tbody').on( 'click', '.row-delete-credit', function (e) {
      e.preventDefault();
      parent = $(this).parents('tr');
      edit = parent.find('input[name="edit_credit_detail[]"]').val();
      if(edit) {
        ids_delete = $('input[name="ids_delete"]').val();
        $('input[name="ids_delete"]').val(edit+','+ids_delete);
      }

      table_credit.row( $(this).parents('tr') ).remove().draw();

      grandtotalCredit()
    });

    $('#datatable_credit tbody').on( 'keyup', 'input[name="total_credit_detail[]"]', function (e) {
      grandtotalCredit();
    });

    function grandtotalCredit() {
      var subtotal = 0;
      $('input[name="total_credit_detail[]"]').each(function(){
        subtotal += Number($(this).val());
      });

      if(!Number.isInteger(subtotal)) {
        subtotal = parseFloat(subtotal).toFixed(2);
      }

      $('#subtotal_credit').val(subtotal);
    }

    // DEBET
    $('.select2-debet').select2()

    var table_debet = $('#datatable_debet').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'coa', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "35%"},
          {name: 'action', orderable: false, searcable: false, width: "9%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter2 = 1000;
  
    $('#add_debet').on( 'click', function (e) {
      e.preventDefault();
      var select2_debet = $('.select2-debet').select2('data'); 
      var total_debet = $('#total_debet').val() ?? '';

      if(select2_debet[0]['id']) {
        table_debet.row.add([
                    counter2,
                    '<input type="hidden" name="coa_debet_detail[]" value="'+select2_debet[0]['id']+'"><input type="hidden" class="form-control" name="edit_debet_detail[]" value=""><span>'+select2_debet[0]['text']+'</span>',
                    '<input type="number" class="form-control" name="total_debet_detail[]" value="'+total_debet+'" required>',
                    '<a href="#" class="row-delete-debet"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
        counter2++;

        $('#total_debet').val('')
        $('.select2-debet').val(null).trigger("change")
        grandtotalDebet()
      }
      
    });

    $('#datatable_debet tbody').on( 'click', '.row-delete-debet', function (e) {
      e.preventDefault();
      parent = $(this).parents('tr');
      edit = parent.find('input[name="edit_debet_detail[]"]').val();
      if(edit) {
        ids_delete = $('input[name="ids_delete"]').val();
        $('input[name="ids_delete"]').val(edit+','+ids_delete);
      }

      table_debet.row( $(this).parents('tr') ).remove().draw();
      grandtotalDebet();
    });

    $('#datatable_debet tbody').on( 'keyup', 'input[name="total_debet_detail[]"]', function (e) {
      grandtotalDebet();
    });

    function grandtotalDebet() {
      var subtotal = 0;
      $('input[name="total_debet_detail[]"]').each(function(){
        subtotal += Number($(this).val());
      });

      if(!Number.isInteger(subtotal)) {
        subtotal = parseFloat(subtotal).toFixed(2);
      }
      $('#subtotal_debet').val(subtotal);
    }

  })
</script>
@endpush
