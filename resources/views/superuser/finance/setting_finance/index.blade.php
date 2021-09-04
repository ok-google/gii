@extends('superuser.app')

@section('content')

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.finance.setting_finance.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Setting Finance</h3>
    </div>

    <div class="block-header">
      <h3 class="block-title text-center">PPB Approve</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Tunai</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="ppb_tunai_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="ppb_tunai_debet" name="ppb_tunai_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $ppb_tunai_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Credit</label>
            <div class="col-md-7">
              <div class="form-control-plaintext"><i>Based PPB Selection</i></div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Non Tunai</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="ppb_non_tunai_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="ppb_non_tunai_debet" name="ppb_non_tunai_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $ppb_non_tunai_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Credit</label>
            <div class="col-md-7">
              <div class="form-control-plaintext"><i>Based Supplier Setting</i></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">Receiving ACC</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-12">
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-right" for="receiving_debet">Debet</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="receiving_debet" name="receiving_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $receiving_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-right">Credit</label>
            <div class="col-md-4">
              <div class="form-control-plaintext"><i>Based PPB Debet</i></div>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-right" for="receiving_tax">Tax (Debet)</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="receiving_tax" name="receiving_tax" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $receiving_tax == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">DO Validate</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Transaction</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Debet</label>
            <div class="col-md-7">
              <div class="form-control-plaintext"><i>Based Customer or Marketplace</i></div>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="do_transaction_credit">Credit</label>
            <div class="col-md-7">
              <div class="form-control-plaintext"><i>Based Customer or Marketplace</i></div>
            </div>
            {{-- <div class="col-md-7">
              <select class="js-select2 form-control" id="do_transaction_credit" name="do_transaction_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $do_transaction_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div> --}}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">HPP</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="do_hpp_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="do_hpp_debet" name="do_hpp_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $do_hpp_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="do_hpp_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="do_hpp_credit" name="do_hpp_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $do_hpp_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">Marketplace</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-12">
          <div class="form-group row">
            <div class="col-md-3"></div>
            <label class="col-md-3 col-form-label text-center">Debet</label>
            <label class="col-md-3 col-form-label text-center">Credit</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Tokopedia</label>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="piutang_tokopedia" name="piutang_tokopedia" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $piutang_tokopedia == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="penjualan_tokopedia" name="penjualan_tokopedia" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $penjualan_tokopedia == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Lazada</label>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="piutang_lazada" name="piutang_lazada" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $piutang_lazada == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="penjualan_lazada" name="penjualan_lazada" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $penjualan_lazada == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Shopee</label>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="piutang_shopee" name="piutang_shopee" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $piutang_shopee == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="penjualan_shopee" name="penjualan_shopee" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $penjualan_shopee == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Blibli</label>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="piutang_blibli" name="piutang_blibli" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $piutang_blibli == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <select class="js-select2 form-control" id="penjualan_blibli" name="penjualan_blibli" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $penjualan_blibli == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">Sale Return ACC</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Transaction</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="return_transaction_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="return_transaction_debet" name="return_transaction_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $return_transaction_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="return_transaction_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="return_transaction_credit" name="return_transaction_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $return_transaction_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">HPP</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="return_hpp_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="return_hpp_debet" name="return_hpp_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $return_hpp_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="return_hpp_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="return_hpp_credit" name="return_hpp_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $return_hpp_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">Disposal ACC</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-12">
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-right" for="disposal_debet">Debet</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="disposal_debet" name="disposal_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $disposal_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-4 col-form-label text-right" for="disposal_credit">Credit</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="disposal_credit" name="disposal_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $disposal_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- BUY BACK --}}
    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">BUY BACK</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-12 text-center">
          <i>Buy Back dengan kondisi barang yang baik dan bisa dijual kembali.</i>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Nilai Buy Back Price</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_valid_price_debet">Debet</label>
            <div class="col-md-7">
              <div class="form-control-plaintext"><i>Based Customer or Marketplace</i></div>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_valid_price_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="buy_back_valid_price_credit" name="buy_back_valid_price_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $buy_back_valid_price_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Nilai HPP</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_valid_hpp_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="buy_back_valid_hpp_debet" name="buy_back_valid_hpp_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $buy_back_valid_hpp_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_valid_hpp_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="buy_back_valid_hpp_credit" name="buy_back_valid_hpp_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $buy_back_valid_hpp_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-12 text-center">
          <i>Buy Back dengan kondisi barang yang rusak dan didisposal.</i>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Nilai Buy Back Price</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_disposal_price_debet">Debet</label>
            <div class="col-md-7">
              <div class="form-control-plaintext"><i>Based Customer or Marketplace</i></div>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_disposal_price_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="buy_back_disposal_price_credit" name="buy_back_disposal_price_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $buy_back_disposal_price_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">Nilai HPP</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_disposal_hpp_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="buy_back_disposal_hpp_debet" name="buy_back_disposal_hpp_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $buy_back_disposal_hpp_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="buy_back_disposal_hpp_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="buy_back_disposal_hpp_credit" name="buy_back_disposal_hpp_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $buy_back_disposal_hpp_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- STOCK ADJUSMENT --}}
    <hr>
    <div class="block-header">
      <h3 class="block-title text-center">STOCK ADJUSMENT</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">PLUS</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="stock_adjusment_plus_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="stock_adjusment_plus_debet" name="stock_adjusment_plus_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $stock_adjusment_plus_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="stock_adjusment_plus_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="stock_adjusment_plus_credit" name="stock_adjusment_plus_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $stock_adjusment_plus_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group row">
            <label class="col-md-12 col-form-label text-center">MINUS</label>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="stock_adjusment_minus_debet">Debet</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="stock_adjusment_minus_debet" name="stock_adjusment_minus_debet" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $stock_adjusment_minus_debet == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="stock_adjusment_minus_credit">Credit</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="stock_adjusment_minus_credit" name="stock_adjusment_minus_credit" data-placeholder="Select COA">
                @foreach($coa as $item)
                  <option></option>
                  <option value="{{ $item->id }}" {{ $stock_adjusment_minus_credit == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
            Save Setting <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()
  })
</script>
@endpush
