@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting Report</span>
  <span class="breadcrumb-item active">Cash Flow</span>
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
          <div class="col-md-8">
            <span style="vertical-align: super;">Period :</span> <span class="h2">{{ \Carbon\Carbon::parse( $journal_periode->from_date )->format('d/m/Y') }} - {{ \Carbon\Carbon::parse( $journal_periode->to_date )->format('d/m/Y') }}</span>
          </div>
          <div class="col-md-4 text-right">
            <a href="{{ route('superuser.report.cash_flow_report.pdf', $journal_periode->id) }}" target="_blank">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                Download C/F <i class="fa fa-sticky-note-o ml-10"></i>
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
            <select class="js-select2 form-control" id="periode" name="periode" data-placeholder="Select Period">
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

<div class="block">
  <div class="block-content block-content-full">
    <table class="table table-borderless table-vcenter table-responsive">
      <tr>
        <td class="text-left" width="25%">
          <b>Beginning Balance</b>
        </td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['A'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="3">
          <b>Revenue :</b>
        </td>
      </tr>
      @foreach ($report['B'] as $item)
        @if ($item->total_debet != null && $item->total_debet != 0)
          <tr>
            <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
            <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
            <td class="text-left" width="25%"></td>
          </tr>
        @endif
        @if ($item->total_credit != null && $item->total_credit != 0)
          <tr>
            <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
            <td class="text-left" width="25%">({{ 'Rp. '.number_format($item->total_credit, 2, ",", ".") }})</td>
            <td class="text-left" width="25%"></td>
          </tr>
        @endif
      @endforeach
      <tr>
        <td class="text-left" width="25%">
          <b>Total Revenue</b>
        </td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['B']->sum('total_debet') - $report['B']->sum('total_credit'), 2, ",", ".") }}</td>
      </tr>

      <tr>
        <td class="text-left" width="25%" colspan="3">
          <b>Cost :</b>
        </td>
      </tr>
      @foreach ($report['C'] as $item)
        @if ($item->total_debet != null && $item->total_debet != 0)
          <tr>
            <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
            <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
            <td class="text-left" width="25%"></td>
          </tr>
        @endif
        @if ($item->total_credit != null && $item->total_credit != 0)
          <tr>
            <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
            <td class="text-left" width="25%">({{ 'Rp. '.number_format($item->total_credit, 2, ",", ".") }})</td>
            <td class="text-left" width="25%"></td>
          </tr>
        @endif
      @endforeach
      <tr>
        <td class="text-left" width="25%">
          <b>Total Cost</b>
        </td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['C']->sum('total_debet') - $report['C']->sum('total_credit'), 2, ",", ".") }}</td>
      </tr>

      <tr>
        <td class="text-left" width="25%" colspan="3">
          <b>Other :</b>
        </td>
      </tr>
      @foreach ($report['D'] as $item)
        @if ($item->total_debet != null && $item->total_debet != 0)
          <tr>
            <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
            <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
            <td class="text-left" width="25%"></td>
          </tr>
        @endif
        @if ($item->total_credit != null && $item->total_credit != 0)
          <tr>
            <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
            <td class="text-left" width="25%">({{ 'Rp. '.number_format($item->total_credit, 2, ",", ".") }})</td>
            <td class="text-left" width="25%"></td>
          </tr>
        @endif
      @endforeach
      <tr>
        <td class="text-left" width="25%">
          <b>Total Other</b>
        </td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['D']->sum('total_debet') - $report['D']->sum('total_credit'), 2, ",", ".") }}</td>
      </tr>

      <tr>
        <td class="text-left" width="25%">
          <b>Closing Balance</b>
        </td>
        <td class="text-left" width="25%"></td>
        <td class="text-left border-top" width="25%">{{ 'Rp. '.number_format($report['E'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="25%">
          <b>Cash Interval</b>
        </td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['F'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
      </tr>
    </table>
  </div>
</div>

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

  $('#datatable').DataTable({
    order: [
      [0, 'asc']
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

  $('.js-select2').on('select2:select', function (e) {
    window.location.href = '{{ route('superuser.report.cash_flow_report.index') }}/'+$(this).val();
  });
});
</script>
@endpush
