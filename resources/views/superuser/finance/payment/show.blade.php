@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <a class="breadcrumb-item" href="{{ route('superuser.finance.payment.index') }}">Cash/Bank Payment</a>
  <span class="breadcrumb-item active">Show</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Cash/Bank Payment</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Transaction</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $payment->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right">Select Date</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ \Carbon\Carbon::parse($payment->select_date)->format('d/m/Y') }}</div>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-md-6">
      <div class="block-header block-header-default">
        <h3 class="block-title">Debet</h3>
      </div>
      <div class="block-content">
        <table id="datatable_debet" class="table table-striped table-vcenter table-responsive">
          <thead>
            <tr>
              <th class="text-center">Counter</th>
              <th class="text-center">Account</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody>
            @php $grandtotal_debet = 0; @endphp
            @foreach ($payment_debet as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->coa->name }}</td>
                  <td>{{ $item->price_format($item->total) }}</td>
                </tr>
                @php $grandtotal_debet = $grandtotal_debet + $item->total; @endphp
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th class="text-center"></th>
              <th class="text-right">Grand Total</th>
              <th class="text-center">
                {{ number_format($grandtotal_debet, 2, ".", ",") }}
              </th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="col-md-6">
      <div class="block-header block-header-default">
        <h3 class="block-title">Credit</h3>
      </div>
      <div class="block-content">
        <table id="datatable_credit" class="table table-striped table-vcenter table-responsive">
          <thead>
            <tr>
              <th class="text-center">Counter</th>
              <th class="text-center">Account</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody>
            @php $grandtotal_credit = 0; @endphp
            @foreach ($payment_credit as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->coa->name }}</td>
                  <td>{{ $item->price_format($item->total) }}</td>
                </tr>
                @php $grandtotal_credit = $grandtotal_credit + $item->total; @endphp
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th class="text-center"></th>
              <th class="text-right">Grand Total</th>
              <th class="text-center">
                {{ number_format($grandtotal_credit, 2, ".", ",") }}
              </th>
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
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script>
  $(document).ready(function () {
    var table_credit = $('#datatable_credit').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'coa', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "35%"},
        ],
        'order' : [[0,'desc']]
    })

    var table_debet = $('#datatable_debet').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'coa', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "35%"},
        ],
        'order' : [[0,'desc']]
    })
  })
</script>
@endpush
