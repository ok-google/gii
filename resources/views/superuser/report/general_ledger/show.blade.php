@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting Report</span>
  <span class="breadcrumb-item active">General Ledger</span>
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
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <div class="col-md-6">
            <select id="select_coa" class="js-select2 form-control" id="coa" name="coa" data-placeholder="Select Coa">
              <option value="all">ALL</option>
              @foreach($general_ledger as $key => $item)
              <option value="{{ $key }}">{{ $item['title'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6 text-right">
            <a href="{{ route('superuser.report.general_ledger.pdf', $journal_periode->id) }}" target="_blank">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                Download G/L <i class="fa fa-sticky-note-o ml-10"></i>
              </button>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <div class="col-md-12">
            <select id="select_periode" class="js-select2 form-control" id="periode" name="periode" data-placeholder="Select Periode History">
              <option></option>
              @foreach($journal_periodes as $periode)
              <option value="{{ $periode->id }}" {{ $periode->id == $journal_periode->id ? 'selected':'' }}>{{ \Carbon\Carbon::parse( $periode->from_date )->format('d/m/Y') }} &nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp; {{ \Carbon\Carbon::parse( $periode->to_date )->format('d/m/Y') }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@foreach ($general_ledger as $key => $item)
  <div class="block ledger" id="ledger-{{ $key }}">
    <div class="block-header block-header-default">
      <h3 class="block-title">{{ $item['title'] }}</h3>
    </div>
    <div class="block-content block-content-full">
      <table class="datatable table table-striped table-vcenter table-responsive">
        <thead>
          <tr>
            <th class="text-center">Date</th>
            <th class="text-left">Transaction</th>
            <th class="text-center">Debet</th>
            <th class="text-center">Credit</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($item['data'] as $value)
              <tr>
                <td>{{ $value['date'] }}</td>
                <td class="text-left">{{ $value['name'] }}</td>
                <td>{{ $value['debet'] }}</td>
                <td>{{ $value['credit'] }}</td>
              </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th class="text-right" colspan="2" id="action_table">TOTAL</th>
            <th class="text-center" id="debet_total">{{ $item['total']['debet'] }}</th>
            <th class="text-center" id="credit_total">{{ $item['total']['credit'] }}</th>
          </tr>
          <tr>
            <th class="text-right" colspan="2" id="action_table">SALDO AKHIR</th>
            <th class="text-center" id="debet_total">{{ $item['saldoakhir']['debet'] }}</th>
            <th class="text-center" id="credit_total">{{ $item['saldoakhir']['credit'] }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
@endforeach

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@section('modal')

@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('.js-select2').select2()

  $('.datatable').DataTable({
    order: [
      [0, 'asc']
    ],
    "columns": [
      { "width": "20%" },
      null,
      { "width": "20%" },
      { "width": "20%" },
    ],
    paging:   false,
    info: false,
    ordering: false,
    searching: false,
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100]
    ],
  });

  $('#select_coa').on('select2:select', function (e) {
    if($(this).val() == 'all') {
      $('.ledger').show();
    } else {
      $('.ledger').hide();
      $('#ledger-'+$(this).val()).show();
    }
  });

  $('#select_periode').on('select2:select', function (e) {
    window.location.href = '{{ route('superuser.report.general_ledger.index') }}/'+$(this).val();
  });
});
</script>
@endpush
