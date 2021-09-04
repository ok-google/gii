@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Marketplace Receipt</span>
</nav>
@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif
<div class="block">
  <div class="block-content">
    <div class="form-group row">
      <div class="col-md-6">
        @if($superuser->can('marketplace receipt-create'))
        <button type="button" class="btn btn-outline-info ml-10 min-width-125" data-toggle="modal" data-target="#modal-manage">Import</button>
        @endif
      </div>
      <div class="col-md-6 text-right" id="reset" style="display: none">
        <a href="javascript:deleteConfirmation('{{ route('superuser.finance.marketplace_receipt.destroy', $superuser->id) }}')">
          <button type="button" class="btn btn-outline-primary min-width-125">Reset</button>
        </a>
      </div>
    </div>
  </div>
  <hr>
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Invoice</th>
          <th class="text-center">Tgl Pencairan</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Total</th>
          <th class="text-center">Payment</th>
          <th class="text-center">Cost 1</th>
          <th class="text-center">Cost 2</th>
          <th class="text-center">Cost 3</th>
          <th class="text-center">Paid</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th class="text-right" colspan="3">Total</th>
          <th class="text-center" id="total"></th>
          <th class="text-center" id="payment"></th>
          <th class="text-center" id="cost_1"></th>
          <th class="text-center" id="cost_2"></th>
          <th class="text-center" id="cost_3"></th>
          <th></th>
        </tr>
        <tr id="btn-input" style="display: none">
          <th class="text-center" colspan="9">
            <button type="button" class="btn btn-outline-info ml-10 min-width-125" data-toggle="modal" data-target="#modal-manage-mr">INPUT</button>
          </th>
        </tr>
        <tr id="wrong-total" style="display: none">
          <th class="text-center" colspan="9" style="color: red">
            Please make sure the payment or cost not empty!
          </th>
        </tr>
      </tfoot>
    </table>
  </div>
  
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage-purchase-order-detail', [
  'import_template_url' => route('superuser.finance.marketplace_receipt.import_template'),
  'import_url' => route('superuser.finance.marketplace_receipt.import')
])

@include('superuser.component.modal-manage-marketplace-receipt')

@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.finance.marketplace_receipt.json') }}';

  $('#datatable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'code'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'customer'},
      {data: 'total'},
      {data: 'payment'},
      {data: 'cost_1'},
      {data: 'cost_2'},
      {data: 'cost_3'},
      {data: 'paid'},
      {data: 'total_data', visible: false},
      {data: 'payment_data', visible: false},
      {data: 'cost_1_data', visible: false},
      {data: 'cost_2_data', visible: false},
      {data: 'cost_3_data', visible: false},
    ],
    order: [
      [1, 'desc']
    ],
    paging:   false,
    info: false,
    ordering: false,
    searching: false,
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20],
      [5, 15, 20]
    ],
    "footerCallback": function ( row, data, start, end, display ) {
      var api = this.api(), data;
      
      // Remove the formatting to get integer data for summation
      var intVal = function ( i ) {
          return typeof i === 'string' ?
              i.replace(/[\Rp.,]/g, '')*1 :
              typeof i === 'number' ?
                  i : 0;
      };

      total = api
          .column( 9 )
          .data()
          .reduce( function (a, b) {
              return intVal(a) + intVal(b);
          }, 0 );

      payment = api
          .column( 10 )
          .data()
          .reduce( function (a, b) {
              return intVal(a) + intVal(b);
          }, 0 );

      cost_1 = api
          .column( 11 )
          .data()
          .reduce( function (a, b) {
              return intVal(a) + intVal(b);
          }, 0 );

      cost_2 = api
          .column( 12 )
          .data()
          .reduce( function (a, b) {
              return intVal(a) + intVal(b);
          }, 0 );

      cost_3 = api
          .column( 13 )
          .data()
          .reduce( function (a, b) {
              return intVal(a) + intVal(b);
          }, 0 );

      var from_date = $('#from_date').val();
      var to_date   = $('#to_date').val();

      var numFormat = $.fn.dataTable.render.number( '\.', ',', 2).display;
      // Update footer
      $( '#total' ).html('Rp. '+numFormat(total));
      $( '#payment' ).html('Rp. '+numFormat(payment));
      $( '#cost_1' ).html('Rp. '+numFormat(cost_1));
      $( '#cost_2' ).html('Rp. '+numFormat(cost_2));
      $( '#cost_3' ).html('Rp. '+numFormat(cost_3));
      
      if(total == null || total == 0) {
        $('#btn-input').hide();
        $('#wrong-total').hide();
        $('#reset').hide();
      } else if(payment == null && cost_1 == null && cost_2 == null && cost_3 == null ){
        $('#btn-input').hide();
        $('#wrong-total').show();
        $('#reset').show();
      } else {
        $('#btn-input').show();
        $('#wrong-total').hide();
        $('#reset').show();
        
        // FILL MODAL
        $('#payment_modal').html('Rp. '+numFormat(payment));
        $('#cost_1_modal').html('Rp. '+numFormat(cost_1));
        $('#cost_2_modal').html('Rp. '+numFormat(cost_2));
        $('#cost_3_modal').html('Rp. '+numFormat(cost_3));
        $('#total_modal').html('Rp. '+numFormat(payment+cost_1+cost_2+cost_3));

        if(cost_1 == null || cost_1 == 0) {
          $('#coa_cost_1').prop('required',false);
        }
        if(cost_2 == null || cost_2 == 0) {
          $('#coa_cost_2').prop('required',false);
        }
        if(cost_3 == null || cost_3 == 0) {
          $('#coa_cost_3').prop('required',false);
        }
      }
    }
  });
});
</script>
@endpush
