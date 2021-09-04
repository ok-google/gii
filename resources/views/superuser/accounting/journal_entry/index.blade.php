@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <a class="breadcrumb-item" href="{{ route('superuser.accounting.journal_entry.index') }}">Journal Entry</a>
  {{-- <span class="breadcrumb-item active">Create</span> --}}
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.accounting.journal_entry.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Journal Entry</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="date">Date <span class="text-danger">*</span></label>
        <div class="col-md-3">
          <input type="date" class="form-control" id="date" name="date" {{ $min_date ? 'min='.$min_date : '' }} {{ $to_date ? 'max='.$to_date : '' }}>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Transaction <span class="text-danger">*</span></label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="debet_coa">Debet Entry</label>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="debet_coa" name="debet_coa" data-placeholder="Select COA">
            @foreach($coa as $item)
              <option></option>
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="debet_entry" name="debet_entry">
        </div>
        <div class="col-md-1 text-right">
          <a href="#" id="debet_add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              INSERT
            </button>
          </a>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="credit_coa">Credit Entry</label>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="credit_coa" name="credit_coa" data-placeholder="Select COA">
            @foreach($coa as $item)
              <option></option>
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="credit_entry" name="credit_entry">
        </div>
        <div class="col-md-1 text-right">
          <a href="#" id="credit_add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              INSERT
            </button>
          </a>
        </div>
      </div>
    </div>
    <div class="block-content">
      <table id="datatable" class="table table-striped table-vcenter table-responsive">
        <thead>
          <tr>
            <th class="text-center">Counter</th>
            <th class="text-center">Transaction</th>
            <th class="text-center">Debet</th>
            <th class="text-center">Credit</th>
            <th class="text-center"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="subtotal">Total</label>
        <div class="col-md-3 col-form-label text-center">
          <input type="hidden" class="form-control" id="subtotal_debet" name="subtotal_debet">
          <span id="subtotal_debet_text">Rp. 0</span>
        </div>
        <div class="col-md-3 col-form-label text-center">
          <input type="hidden" class="form-control" id="subtotal_credit" name="subtotal_credit">
          <span id="subtotal_credit_text">Rp. 0</span>
        </div>
        <div class="col-md-1">
        </div>
      </div>
    </div>
    
    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.accounting.journal.index') }}">
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

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'ppb', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "25%"},
          {name: 'paid', orderable: false, searcable: false, width: "25%"},
          {name: 'action', orderable: false, searcable: false, width: "9%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1;
  
    $('#debet_add').on( 'click', function (e) {
      e.preventDefault();
      var debet_coa = $('#debet_coa').select2('data');

      if(debet_coa[0]['id'] && $('#date').val() && $('#name').val() && $('#debet_entry').val() ) {
        table.row.add([
                    counter,
                    '<input type="hidden" name="date_detail[]" value="'+$('#date').val()+'"><input type="hidden" name="name_detail[]" value="'+$('#name').val()+'"><input type="hidden" name="coa_id[]" value="'+debet_coa[0]['id']+'"><input type="hidden" name="debet[]" value="'+$('#debet_entry').val()+'"><input type="hidden" name="credit[]" value=""><span>'+$('#name').val()+'</span>',
                    '<span>Rp. '+Number($('#debet_entry').val()).toLocaleString("id-ID", {maximumFractionDigits:2})+'</span>',
                    '',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
        counter++;

        $('#name').val('')
        $('#debet_entry').val('')
        // $('#select_total').val('')
        $('#debet_coa').val(null).trigger("change")
        grandTotal()
      }
    });

    $('#credit_add').on( 'click', function (e) {
      e.preventDefault();
      var credit_coa = $('#credit_coa').select2('data');

      if(credit_coa[0]['id'] && $('#date').val() && $('#name').val() && $('#credit_entry').val() ) {
        table.row.add([
                    counter,
                    '<input type="hidden" name="date_detail[]" value="'+$('#date').val()+'"><input type="hidden" name="name_detail[]" value="'+$('#name').val()+'"><input type="hidden" name="coa_id[]" value="'+credit_coa[0]['id']+'"><input type="hidden" name="debet[]" value=""><input type="hidden" name="credit[]" value="'+$('#credit_entry').val()+'"><span>'+$('#name').val()+'</span>',
                    '',
                    '<span>Rp. '+Number($('#credit_entry').val()).toLocaleString("id-ID", {maximumFractionDigits:2})+'</span>',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
        counter++;

        $('#name').val('')
        $('#credit_entry').val('')
        // $('#select_total').val('')
        $('#credit_coa').val(null).trigger("change")
        grandTotal()
      }
    });

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      grandTotal()
    });

    function grandTotal() {
      var subtotal_debet = 0;
      $('input[name="debet[]"]').each(function(){
        subtotal_debet += Number($(this).val());
      });
      $('#subtotal_debet').val(subtotal_debet);
      $('#subtotal_debet_text').text('Rp. '+subtotal_debet.toLocaleString('id-ID', {maximumFractionDigits:2}));

      var subtotal_credit = 0;
      $('input[name="credit[]"]').each(function(){
        subtotal_credit += Number($(this).val());
      });
      $('#subtotal_credit').val(subtotal_credit);
      $('#subtotal_credit_text').text('Rp. '+subtotal_credit.toLocaleString('id-ID', {maximumFractionDigits:2}));
    }

  })
</script>
@endpush
