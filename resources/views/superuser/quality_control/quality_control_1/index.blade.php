@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Quality Control</span>
  <span class="breadcrumb-item active">Quality Control Utama</span>
</nav>
<div id="alert-block"></div>
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
<div class="block">
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-left" for="s_code">Scan Barcode</label>
      <div class="col-md-4">
        <input type="text" class="form-control" id="s_code" name="s_code" autofocus>
        <small class="form-text text-muted">Please hover the mouse cursor here to scan the barcode.</small>
      </div>
      <div class="col-md-2 col-form-label text-left" id="loading" style="display: none;">
        <div class="spinner-grow" style="width: 1rem; height: 1rem;" role="status">
          <span class="sr-only">Loading...</span>
        </div>
        <div class="spinner-grow" style="width: 1rem; height: 1rem;" role="status">
          <span class="sr-only">Loading...</span>
        </div>
        <div class="spinner-grow" style="width: 1rem; height: 1rem;" role="status">
          <span class="sr-only">Loading...</span>
        </div>
      </div>
      <div class="col-md-4 col-form-label text-left" id="msg-result" style="display: none;">
        <strong><span id="msg"></span></strong>
      </div>
    </div>
  </div>
  <div class="block">
    <form class="ajax" data-action="{{ route('superuser.quality_control.quality_control_1.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-left" for="warehouse_reparation">Warehouse Reparation <span class="text-danger">*</span></label>
          <div class="col-md-4">
            <select class="js-select2 form-control" id="warehouse_reparation" name="warehouse_reparation" data-placeholder="Select Warehouse">
              <option></option>
              @foreach($warehouses as $warehouse)
              <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <hr class="my-20">
      <div class="block-header">
        <h3 class="block-title">Barcode List</h3>
        <button id="submit-table" type="submit" class="btn bg-gd-corporate border-0 text-white" style="display: none;">
          Submit <i class="fa fa-arrow-right ml-10"></i>
        </button>
      </div>
      <div class="block-content">
        <table id="datatable" class="table table-striped table-vcenter table-responsive">
          <thead>
            <tr>
              <th class="text-center">Counter</th>
              <th class="text-center">Barcode</th>
              <th class="text-center">SKU</th>
              <th class="text-center">Product</th>
              <th class="text-center">Quantity</th>
              <th class="text-center">Description</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </form>
  </div>

</div>
@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>

<script type="text/javascript">
$(document).ready(function() {
  $('.js-select2').select2()

  var table = $('#datatable').DataTable({
      paging: false,
      searching: false,
      columns: [
        {name: 'counter', "visible": false},
        {name: 'code', orderable: false},
        {name: 'sku', orderable: false, searcable: false},
        {name: 'name', orderable: false, searcable: false},
        {name: 'quantity', orderable: false, searcable: false},
        {name: 'description', orderable: false, searcable: false},
        {name: 'action', orderable: false, searcable: false}
      ],
      'order' : [[0,'desc']]
  })

  var counter = 1;

  $('#s_code').keyup(delay( function (){
    s_code = $(this).val();

    if(s_code.length == 13) {
      $('#msg-result').hide();
      $('#loading').show();

      $.ajax({
        url: '{{ route('superuser.quality_control.quality_control_1.get_barcode') }}',
        data: {code:s_code, _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            if (json.msg == '') {
              var duplicate = 0;
              $('input[name="id[]"]').each( function  () {
                if($(this).val() == json.data.id) {
                  duplicate = 1;
                } 
              });
              if (duplicate == 1) {
                $('#msg-result').show();
                $('#msg').text('Barcode is already in the table.');
              } else {
                table.row.add([
                  counter,
                  json.data.code,
                  json.data.sku,
                  json.data.name,
                  '<input type="hidden" class="form-control" name="id[]" value="'+json.data.id+'"><input type="number" class="form-control" name="quantity[]" value="0" min="0" max="'+json.data.quantity+'">',
                  '<textarea class="form-control" name="description[]"></textarea>',
                  '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                ]).draw( false );

                counter++;

                $('#msg-result').hide();
                $('#submit-table').show();
              }
    
              $('#loading').hide();
              $('#s_code').val('');
            } else {
              $('#loading').hide();
              $('#msg-result').show();
              $('#msg').text(json.msg);
              $('#s_code').val('');
            }
          }
        }
      });

    } else {
      $('#loading').hide();
      $('#msg-result').hide();
    }
  }, 100));


  $('#datatable tbody').on( 'click', '.row-delete', function (e) {
    e.preventDefault();
    table.row( $(this).parents('tr') ).remove().draw();

    if(typeof $('input[name="id[]"]').val() == 'undefined') {
      $('#submit-table').hide();
    }
  });


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
