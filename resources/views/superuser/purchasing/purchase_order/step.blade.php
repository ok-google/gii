@extends('superuser.app')

@section('content')

@if ( $purchase_order->status() == 'DRAFT' )
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Purchasing</span>
    <span class="breadcrumb-item">Purchase Order (PPB)</span>
    <span class="breadcrumb-item">New</span>
    <span class="breadcrumb-item active">Add Product</span>
  </nav>
@else
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Purchasing</span>
    <span class="breadcrumb-item">Purchase Order (PPB)</span>
    <span class="breadcrumb-item">{{ $purchase_order->code }}</span>
    <span class="breadcrumb-item active">Edit Product</span>
  </nav>
@endif

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
  <div class="block-header block-header-default">
    <h3 class="block-title">New Purchase Order (PPB)</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">PPB Number</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Supplier</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->supplier->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Transaction Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->transaction_type() }}</div>
      </div>
    </div>
    @if ($purchase_order->coa_id)
    <div class="row">
      <label class="col-md-3 col-form-label text-right">COA Cash</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->coa->name }}</div>
      </div>
    </div>
    @endif
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kurs (IDR)</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->kurs ? $purchase_order->price_format($purchase_order->kurs) : '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Tax</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->tax ? $purchase_order->tax.'%' : '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->status() }}</div>
      </div>
    </div>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        @if ($purchase_order->status != $purchase_order::STATUS['DRAFT'])
        <a href="{{ route('superuser.purchasing.purchase_order.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
        @endif
      </div>
      @if ($purchase_order->status == $purchase_order::STATUS['DRAFT'])
      <div class="col-md-6 text-right">
        <a href="{{ route('superuser.purchasing.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="javascript:saveConfirmation('{{ route('superuser.purchasing.purchase_order.publish', $purchase_order->id) }}')">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Publish <i class="fa fa-check ml-10"></i>
          </button>
        </a>
      </div>
      @else
      <div class="col-md-6 text-right">
        <a href="{{ route('superuser.purchasing.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="javascript:saveConfirmation('{{ route('superuser.purchasing.purchase_order.save_modify', [$purchase_order->id, 'save']) }}')">
          <button type="button" class="btn bg-gd-corporate border-0 text-white">
            Save <i class="fa fa-check ml-10"></i>
          </button>
        </a>
        @if($superuser->can('purchase order-acc'))
        <a href="javascript:saveConfirmation2('{{ route('superuser.purchasing.purchase_order.save_modify', [$purchase_order->id, 'save-acc']) }}')">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            ACC <i class="fa fa-check ml-10"></i>
          </button>
        </a>
        @endif
      </div>
      @endif
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">{{ ( $purchase_order->status() == 'DRAFT' ? 'Add' : 'Edit' ) }} Product ({{ $purchase_order->details->count() }})</h3>
    
    <button type="button" class="btn btn-outline-info mr-10 min-width-125 pull-right" data-toggle="modal" data-target="#modal-manage">Import</button>
    
    <a href="{{ route('superuser.purchasing.purchase_order.detail.create', [$purchase_order->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center"><input type="checkbox" id="check-all"></th>
          <th class="text-center">SKU</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Unit Price (RMB)</th>
          <th class="text-center">Local Freight Cost (RMB)</th>
          <th class="text-center">Komisi (IDR)</th>
          <th class="text-center">Total Price (RMB)</th>
          <th class="text-center">Kurs (RMB)</th>
          <th class="text-center">Total Price (IDR)</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase_order->details as $detail)
        <tr>
          <td class="text-center"><input type="checkbox" class="data-check" value="{{ $detail->id }}"></td>
          <td class="text-center">{{ $detail->product->code }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->quantity) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->unit_price) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->local_freight_cost) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->komisi) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->total_price_rmb) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->kurs) }}</td>
          <td class="text-center">{{ $purchase_order->price_format($detail->total_price_idr) }}</td>
          <td class="text-center">
            <a href="{{ route('superuser.purchasing.purchase_order.detail.edit', [$purchase_order->id, $detail->id]) }}">
              <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit">
                <i class="fa fa-pencil"></i>
              </button>
            </a>
            <a href="javascript:deleteConfirmation('{{ route('superuser.purchasing.purchase_order.detail.destroy', [$purchase_order->id, $detail->id]) }}')">
              <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-times"></i>
              </button>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th colspan="5" style="text-align:right">Total RMB:</th>
          <th colspan="3"></th>
          <th style="text-align:right">Total IDR:</th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@section('modal')
  @include('superuser.component.modal-manage-purchase-order-detail', [
    'import_template_url' => route('superuser.purchasing.purchase_order.import_template'),
    'import_url' => route('superuser.purchasing.purchase_order.import', $purchase_order->id),
    // 'export_url' => route('superuser.purchasing.purchase_order.export')
  ])
@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {

    $('#datatable').DataTable({
        "footerCallback": function ( row, data, start, end, display ) {
          var api = this.api(), data;

          // Remove the formatting to get integer data for summation
          var intVal = function ( i ) {
              return typeof i === 'string' ?
                  i.replace(/[\$,]/g, '')*1 :
                  typeof i === 'number' ?
                      i : 0;
          };

          var numFormat = $.fn.dataTable.render.number( '\,', '.', 2).display;

          // Total over all pages
          total = api
              .column( 5 )
              .data()
              .reduce( function (a, b) {
                  return intVal(a) + intVal(b);
              }, 0 );

          // Update footer
          $( api.column( 5 ).footer() ).html(
            numFormat(total)
          );

          // Total over all pages
          total_idr = api
              .column( 9 )
              .data()
              .reduce( function (a, b) {
                  return intVal(a) + intVal(b);
              }, 0 );

          // Update footer
          $( api.column( 9 ).footer() ).html(
            numFormat(total_idr)
          );
      },
      "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-6"<"toolbar">><"col-sm-12 col-md-6"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      "columnDefs": [
            { 
                "targets": [ 0 ], //first column
                "orderable": false, //set not orderable
            },
            { 
                "targets": [ -1 ], //last column
                "orderable": false, //set not orderable
            },
 
        ],
    })

    $("div.toolbar").html('<div class="row"><div class="col-auto my-auto"><select name="select_action" id="select_action" aria-controls="datatable" class="custom-select custom-select-sm form-control form-control-sm"><option value="">Select Action</option><option value="bulk_delete">Bulk Delete</option></select></div> <div class="col-auto"><button class="btn btn-primary" type="button" onclick="select_action()"><span>Submit Action</span></button></div></div>');

    //check all
    $("#check-all").click(function () {
        $(".data-check").prop('checked', $(this).prop('checked'));
    });

    

    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });
  })

  function select_action() {
    var select_action = $("#select_action").val();
    if(select_action == 'bulk_delete') {
      bulk_delete();
    }
  }

  function bulk_delete()
  {
      var list_id = [];
      $(".data-check:checked").each(function() {
              list_id.push(this.value);
      });
      if(list_id.length > 0)
      {
        swalBulkDelete('{{ route('superuser.purchasing.purchase_order.detail.bulk_delete') }}', list_id);
      }
      else
      {
        Swal.fire('Empty!','No data Selected','info')
      }
  }

  function swalBulkDelete(delete_url, list_id) {
    Swal.fire({
      title: 'Are you sure delete this '+list_id.length+' data?',
      type: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      backdrop: false,
    }).then(result => {
      if (result.value) {
        Swal.fire({
          title: 'Deleting...',
          allowOutsideClick: false,
          allowEscapeKey: false,
          allowEnterKey: false,
          backdrop: false,
          onOpen: () => {
            Swal.showLoading()
          }
        })
        $.ajax({
          url: delete_url,
          type: 'POST',
          data: {ids:list_id, purchase_order_id:'{{ $purchase_order->id }}'},
        }).then( response => {
          Swal.fire({
            title: 'Deleted!',
            text: 'Your data has been deleted.',
            type: 'success',
            backdrop: false,
          }).then(() => {
            if (objHasProp(response, 'data.redirect_to')) {
              redirect(response.data.redirect_to);
            }
          })
        })
        .catch(error => {
          Swal.fire('Error!',`${error.statusText}`,'error')
        });
      }
    });
  }
</script>
@endpush
