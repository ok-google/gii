@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <span class="breadcrumb-item active">Packing</span>
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

<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.sale.delivery_order.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Packing Number</th>
          <th class="text-center">Store</th>
          <th class="text-center">Print Count</th>
          <th class="text-center">Status</th>
          <th class="text-center">List SO</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<div class="modal fade" id="modal-manage" tabindex="-1" role="dialog" aria-labelledby="modal-manage" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">SALES ORDER LIST</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
              <i class="si si-close"></i>
            </button>
          </div>
        </div>
        <div class="block-content pb-20">
          <div class="row">
            <div class="col-md-12" id="result-detail-validate">

              <div class="form-group row">
                <label class="col-md-4 col-form-label text-left" for="grand_total">INVS123213</label>
                <div class="col-md-8">
                  <div class="form-control-plaintext">v</div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
      {{-- <div class="modal-footer">
        <button type="button" class="btn btn-alt-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-alt-success" data-dismiss="modal">
          <i class="fa fa-check"></i> Perfect
        </button>
      </div> --}}
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.sale.delivery_order.json') }}';

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
      {data: 'DT_RowIndex', name: 'id'},
      {
        data: 'created_at',
        render: {
          _: 'display',
          sort: 'timestamp'
        }
      },
      {data: 'code'},
      {data: 'store_name', name: 'sales_order.store_name'},
      {data: 'print_count'},
      {data: 'status'},
      {data: 'list_so', name: 'sales_order.code', visible: false},
      {data: 'action', orderable: false, searcable: false, width: '20%'}
    ],
    order: [
      [0, 'asc']
    ],
    pageLength: 5,
    lengthMenu: [
      [5, 15, 20, 100],
      [5, 15, 20, 100]
    ],
    "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });
  
  $(document).on('click','.btn-detail-validate',function(e) {
    e.preventDefault();
    $('#result-detail-validate').html( $(this).parent('td').find('.detail-validate').html() );
  });

});
</script>
@endpush
