@extends('superuser.app')

@section('content')
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Inventory</span>
    <a class="breadcrumb-item" href="{{ route('superuser.inventory.recondition.index') }}">Recondition</a>
    <span class="breadcrumb-item active">Show</span>
  </nav>
  <div id="alert-block"></div>

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Show Recondition</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $recondition->code }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $recondition->warehouse->name }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="status">Status</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $recondition->status() }}</div>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.inventory.recondition.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div id="list-result">
    @foreach ($collect as $key => $value)
      <div class="js-animation-object animated fadeIn block block-themed block-rounded block-result">
        <div class="block-header bg-earth-dark">
          <h3 class="block-title">{{ $value['title'] }}</h3>
        </div>
        <div class="block-content">
          <table id="product-{{ $key }}" class="table table-striped table-vcenter table-responsive">
            <thead>
              <tr>
                <th class="text-center">Date In</th>
                <th class="text-center">In From</th>
                <th class="text-center">Quantity</th>
                <th class="text-center">Keterangan</th>
                <th class="text-center">Recondition</th>
                <th class="text-center">Disposal</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($value['list'] as $list)
                <tr>
                  <td class="text-center">{{ $list['date_in'] }}</td>
                  <td class="text-center">{{ $list['type_text'] }}</td>
                  <td class="text-center">{{ $list['quantity'] }}</td>
                  <td class="text-center">{{ $list['keterangan'] }}</td>
                  <td class="text-center">{{ $list['quantity_recondition'] }}</td>
                  <td class="text-center">{{ $list['quantity_disposal'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endforeach
  </div>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.moment')

@push('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      $('.table').DataTable({
        paging: false,
        searching: false,
        columns: [{
            width: '15%',
            render: function(data, type, row) {
              if (data && data != 'null') {
                return moment(data).format('DD/MM/YYYY')
              } else {
                return '-';
              }
            }
          },
          {
            width: '10%'
          },
          {
            width: '10%'
          },
          {
            width: '35%'
          },
          {
            width: '15%'
          },
          {
            width: '15%'
          }
        ]
      });

    });

  </script>
@endpush
