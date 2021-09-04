@extends('superuser.app')

@section('content')

<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.finance.secret_setting.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">WARNING</h3>
    </div>
    <div class="block-header">
      <h3 class="block-title">THIS SETTING CAN BROKE SOME DATABASE. PLEASE LEAVE NOW !!</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <div class="col-md-3">
          <input type="text" class="form-control" name="reset_hpp" placeholder="Password">
        </div>
        <label class="col-md-2 col-form-label text-left" for="reset_hpp">RESET HPP</label>
        <div class="col-md-4 text-left">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Reset <i class="fa fa-arrow-right ml-10"></i>
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
