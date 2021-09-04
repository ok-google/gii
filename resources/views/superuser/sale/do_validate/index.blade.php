@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <span class="breadcrumb-item active">DO Validate</span>
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

  var counter = 1;

  $('#s_code').keyup(delay( function (){
    s_code = $(this).val();
    if(s_code) {
      $('#msg-result').hide();
      $('#msg').text('');
      $('#loading').show();

      $.ajax({
        url: '{{ route('superuser.sale.do_validate.get_barcode') }}',
        data: {code:s_code, _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            $('#loading').hide();
            $('#msg-result').show();
            $('#msg').text(json.msg);
            $('#s_code').val('');
            if(json.msg == 'VALIDATE SUCCESS') {
              playSuccess();
            } else {
              playFailed();
            }
          }
        }
      });
    }
    

  }, 100));

  function delay(fn, ms) {
    let timer = 0
    return function(...args) {
      clearTimeout(timer)
      timer = setTimeout(fn.bind(this, ...args), ms || 0)
    }
  }

  function playSuccess() {
    const audio = new Audio("{{ asset('superuser_assets/media/sounds/success.wav') }}");
    audio.play();
  }

  function playFailed() {
    const audio = new Audio("{{ asset('superuser_assets/media/sounds/failed.wav') }}");
    audio.play();
  }

});
</script>
@endpush
