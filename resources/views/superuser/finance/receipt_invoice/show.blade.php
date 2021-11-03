@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <a class="breadcrumb-item" href="{{ route('superuser.finance.receipt_invoice.index') }}">Cash/Bank Receipt (Inv)</a>
  <span class="breadcrumb-item active">Show</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Cash/Bank Receipt (Inv)</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receipt_invoice->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right">Select Date</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ \Carbon\Carbon::parse($receipt_invoice->select_date)->format('d/m/Y') }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="coa">Account</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receipt_invoice->coa->name }}</div>
      </div>
    </div>
    {{-- <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="customer">Customer</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receipt_invoice->customer->name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="address">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receipt_invoice->customer->address }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="note">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receipt_invoice->description }}</div>
      </div>
    </div> --}}
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="status">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receipt_invoice->status() }}</div>
      </div>
    </div>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Counter</th>
          <th class="text-center">Invoice</th>
          <th class="text-center">Total</th>
          <th class="text-center">Paid</th>
        </tr>
      </thead>
      <tbody>
        @php
        $subtotal_total = 0;
        $subtotal_paid = 0;
        @endphp
        @foreach ($receipt_invoice->details as $item)
          @php
              $subtotal_total = $subtotal_total + $item->total;
              $subtotal_paid = $subtotal_paid + $item->paid;
          @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><span>{{ $item->sales_order->code }}</span></td>
              <td>
                <span>Rp. {{ number_format( $item->total, 2, ".", ",") }}</span>
              </td>
              <td><span>Rp. {{ number_format( $item->paid, 2, ".", ",") }}</span></td>
            </tr>
        @endforeach
      </tbody>
    </table>
    <div class="form-group row justify-content-end">
      <label class="col-md-3 col-form-label text-right" for="subtotal_total">Grand Total</label>
      <div class="col-md-3">
        <div class="form-control-plaintext text-center">Rp. {{ number_format($subtotal_total, 2, ".", ",") }}</div>
      </div>
      <div class="col-md-3">
        <div class="form-control-plaintext text-center">Rp. {{ number_format($subtotal_paid, 2, ".", ",") }}</div>
      </div>
    </div>
    <hr>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="total_payment">Total Payment</label>
      <div class="col-md-6">
        <div class="form-control-plaintext">Rp. {{ number_format($subtotal_paid, 2, ".", ",") }}</div>
      </div>
    </div>
  </div>
  
  <div class="block-content">
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.finance.receipt_invoice.index') }}">
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
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
  
    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'so', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "28%"},
          {name: 'paid', orderable: false, searcable: false, width: "24%"}
        ],
        'order' : [[0,'desc']]
    })

  })
</script>
@endpush
