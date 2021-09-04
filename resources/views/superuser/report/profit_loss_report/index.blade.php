@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting Report</span>
  <span class="breadcrumb-item active">Profit Loss</span>
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
<div class="form-group row">
  <div class="col-md-8">
    
  </div>
  <div class="col-md-4">
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <div class="col-md-12">
            <select id="select_periode" class="js-select2 form-control" id="periode" name="periode" data-placeholder="Select Period">
              <option></option>
              @foreach($journal_periodes as $periode)
              <option value="{{ $periode->id }}">{{ \Carbon\Carbon::parse( $periode->from_date )->format('d/m/Y') }} &nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp; {{ \Carbon\Carbon::parse( $periode->to_date )->format('d/m/Y') }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@section('modal')

@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  $('.js-select2').select2()

  $('#select_periode').on('select2:select', function (e) {
    window.location.href = '{{ route('superuser.report.profit_loss_report.index') }}/'+$(this).val();
  });
});
</script>
@endpush
