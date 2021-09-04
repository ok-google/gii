@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.sale.sales_order.index') }}">Sales Order</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div id="alert-block"></div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Sales Order</h3>
  </div>
  <div class="block-content">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="code">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_order->code }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="marketplace_order">Marketplace Order</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_order->marketplace_order() }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_order->warehouse->name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="customer">Customer</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_order->marketplace_order == \App\Entities\Sale\SalesOrder::MARKETPLACE_ORDER['Non Marketplace'] ? $sales_order->customer->name : $sales_order->customer_marketplace }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_order->marketplace_order == \App\Entities\Sale\SalesOrder::MARKETPLACE_ORDER['Non Marketplace'] ? $sales_order->ekspedisi->name : $sales_order->ekspedisi_marketplace }}</div>
      </div>
    </div>
    <hr class="my-20">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="store_name">Store Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_order->store_name }}</div>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="store_phone">Store Phone</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_order->store_phone }}</div>
      </div>
    </div>
    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.sale.sales_order.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Product</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Select SKU</th>
          <th class="text-center">Product</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Price</th>
          <th class="text-center">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sales_order->sales_order_details as $detail)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $detail->product->code }}</td>
            <td>{{ $detail->product->name }}</td>
            <td>{{ $detail->quantity }}</td>
            <td>{{ $detail->price }}</td>
            <td>{{ $detail->total }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="block-header block-header-default">
    <div class="container">
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="subtotal">IDR Sub Total</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">{{ $sales_order->total }}</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="tax">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="tax_checked" name="tax_checked" {{ $sales_order->tax != '0' && $sales_order->tax != '' ? 'checked' : ''}} onclick="return false;">
            <label class="form-check-label" for="tax_checked">
              Tax
            </label>
          </div>
        </label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">{{ $sales_order->tax }}</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="discount">IDR Discount</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">{{ $sales_order->discount }}</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="shipping_fee">Courier</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">{{ $sales_order->shipping_fee }}</div>
        </div>
      </div>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="grand_total">IDR Total</label>
        <div class="col-md-2 text-right">
          <div class="form-control-plaintext">{{ $sales_order->grand_total }}</div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({})
  });
</script>
@endpush
