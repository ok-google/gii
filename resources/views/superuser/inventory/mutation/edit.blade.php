@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Inventory</span>
  <a class="breadcrumb-item" href="{{ route('superuser.inventory.mutation.index') }}">Mutation</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.inventory.mutation.update', $mutation->id) }}" data-type="POST" enctype="multipart/form-data">
  <input type="hidden" name="_method" value="PUT">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Edit Mutation</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $mutation->code }}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
            <option></option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ ($warehouse->id == $mutation->warehouse_id ) ? 'selected' : '' }}>{{ $warehouse->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.inventory.mutation.index') }}">
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
      <h3 class="block-title">Add Barcode</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-2 col-form-label text-left" for="s_code">Scan Barcode</label>
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
    <div class="block-content">
      <table id="datatable" class="table table-striped table-vcenter table-responsive">
        <thead>
          <tr>
            <th class="text-center">Counter</th>
            <th class="text-center">Barcode</th>
            <th class="text-center">SKU</th>
            <th class="text-center">Product</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($mutation->mutation_details as $detail)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $detail->receiving_detail_colly->code }}</td>
              <td>{{ $detail->receiving_detail_colly->receiving_detail->product->code }}</td>
              <td>{{ $detail->receiving_detail_colly->receiving_detail->product->name }}</td>
              <td><input type="hidden" class="form-control" name="edit[]" value="old"><input type="hidden" class="form-control" name="id[]" value="{{ $detail->receiving_detail_colly->id }}">{{ $detail->receiving_detail_colly->quantity_mutation }}</td>
              <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</form>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()

    $(window).keydown(function(event){
      if(event.keyCode == 13) {
        event.preventDefault();
        return false;
      }
    });
  
    var table = $('#datatable').DataTable({
        paging: false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'code', orderable: false},
          {name: 'sku', orderable: false, searcable: false},
          {name: 'name', orderable: false, searcable: false},
          {name: 'quantity', orderable: false, searcable: false},
          {name: 'action', orderable: false, searcable: false}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1000;
  
    $('#s_code').keyup(delay( function (){
      s_code = $(this).val();
  
      if(s_code.length == 13) {
        $('#msg-result').hide();
        $('#loading').show();
  
        $.ajax({
          url: '{{ route('superuser.inventory.mutation.get_barcode') }}',
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
                    '<input type="hidden" class="form-control" name="edit[]" value="new"><input type="hidden" class="form-control" name="id[]" value="'+json.data.id+'">'+json.data.quantity,
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
  
                  counter++;
  
                  $('#msg-result').hide();
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
      parent = $(this).parents('tr');
      edit = parent.find('input[name="edit[]"]').val();
      
      if(edit == 'new') {
        deleteConfirmationBarcode('/', parent);
      } else {
        id = parent.find('input[name="id[]"]').val();
        deleteConfirmationBarcode('{{ route('superuser.inventory.mutation.delete_barcode', '' ) }}'+'/'+id, parent);
      }
      
    });
  
  
    function delay(fn, ms) {
      let timer = 0
      return function(...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
    }
    
    function deleteConfirmationBarcode(delete_url, parent) {
      Swal.fire({
        title: 'Are you sure?',
        type: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        backdrop: false,
      }).then(result => {
        if (result.value) {
          if(delete_url != '/') {
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
              type: 'DELETE'
            }).then( response => {
              Swal.fire({
                title: 'Deleted!',
                text: 'Your data has been deleted.',
                type: 'success',
                backdrop: false,
              }).then(() => {
                table.row( parent ).remove().draw();
              })
            })
            .catch(error => {
              Swal.fire('Error!',`${error.statusText}`,'error')
            });
          } else {
            Swal.fire({
              title: 'Deleted!',
              text: 'Your data has been deleted.',
              type: 'success',
              backdrop: false,
            }).then(() => {
              table.row( parent ).remove().draw();
            })
          }
        }
      });
    }
  
  });
</script>
@endpush
