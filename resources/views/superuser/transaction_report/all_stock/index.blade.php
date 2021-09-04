@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Transaction Report</span>
  <span class="breadcrumb-item active">All Stock</span>
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
<div class="block">
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-2 col-form-label text-left" for="category">Category <span class="text-danger">*</span></label>
      <div class="col-md-3">
        <select class="js-select2 form-control" id="category" name="category" data-placeholder="Select Category">
          <option value="all">All Category</option>
          @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive display nowrap">
      <thead>
        <tr>
          <th class="text-center">SKU</th>
          <th class="text-center">Category</th>
          <th class="text-center">Product</th>
          @foreach ($warehouses as $item)
            <th class="text-center">{{ $item->name }}</th>
          @endforeach
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
@include('superuser.asset.plugin.select2')

@section('modal')

@endsection

@push('scripts')
<script type="text/javascript">

$.fn.dataTable.ext.search.push(
  function(settings, data, dataIndex) {
    var category = $('#category').select2('data')[0]['text'];
    var category_data = data[1];

    if (category == 'All Category' || category == category_data) {
      return true;
    }
    return false;
  }
);

$(document).ready(function() {
  $('.js-select2').select2()

  let datatableUrl = '{{ route('superuser.transaction_report.all_stock.json') }}';

  var datatable = $('#datatable').DataTable({
    processing: true,
    // serverSide: true,
    scrollX: true,
    ajax: {
      "url": datatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    order: [
      [0, 'asc']
    ],
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100]
    ],
    // "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    @if($superuser->can('all stock-print'))
    buttons: [
      {
        extend: 'excelHtml5',
        text: '<i class="fa fa-file-excel-o"></i>',
        titleAttr: 'Excel',
        title: 'All Stock'
      }
    ]
    @else
    buttons: []
    @endif
    
  });
  
  $('#category').on('change', function() {
    datatable.draw();
  });
});
</script>
@endpush
