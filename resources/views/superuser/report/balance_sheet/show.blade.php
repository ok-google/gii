@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting Report</span>
  <span class="breadcrumb-item active">Balance Sheet</span>
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
            <a href="{{ route('superuser.report.balance_sheet.pdf', $journal_periode->id) }}" target="_blank">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                Download B/S <i class="fa fa-sticky-note-o ml-10"></i>
              </button>
            </a>
          </div>
          <div class="col-md-4 text-right">
            <a href="{{ route('superuser.report.balance_sheet.excel', $journal_periode->id) }}" target="_blank">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                Download B/S Excel <i class="fa fa-sticky-note-o ml-10"></i>
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
    <table class="table table-sm table-borderless">
      <tr>
        <td class="p-0" width="50%">
          <table id="a-id" class="table table-borderless">
            <tr>
              <td class="border" colspan="3"><b>Activa</b></td>
            </tr>
            <tr>
              <td class="text-left border-left" width="33.33%"><b>Activa Lancar</b></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>
            {{-- A1 --}}
            @foreach ($collect['A1'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">{{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }}</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL A1 --}}
            <tr>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format($collect['A1_TOTAL'], 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-left" width="33.33%"><b>Activa Tetap</b></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>
            {{-- A2 --}}
            @foreach ($collect['A2'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">{{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }}</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- A3 --}}
            @foreach ($collect['A3'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">({{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }})</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL A3 --}}
            <tr>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format($collect['A2_TOTAL'] - $collect['A3_TOTAL'], 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-left" width="33.33%"><b>Activa Tidak Lancar</b></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>
            {{-- A4 --}}
            @foreach ($collect['A4'] as $item)
              <tr>
                <td class="text-left border-left" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-left" width="33.33%">({{ 'Rp. '.number_format($item['saldo'], 2, ",", ".") }})</td>
                <td class="text-left border-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL A4 --}}
            <tr>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format($collect['A4_TOTAL'], 2, ",", ".") }}</td>
            </tr>

            {{-- SPACE --}}
            <tr>
              <td class="space-a text-left border-left" width="33.33%"></td>
              <td class="text-left border-left" width="33.33%"></td>
              <td class="text-left border-left border-right" width="33.33%"></td>
            </tr>

            {{-- TOTAL A --}}
            <tr>
              <td class="text-right border-top border-right" colspan="2"><b>Total</b></td>
              <td class="text-left border-right border-top border-bottom" width="33.33%">{{ 'Rp. '.number_format($collect['A1_TOTAL'] + $collect['A2_TOTAL'] - $collect['A3_TOTAL'] + $collect['A4_TOTAL'], 2, ",", ".") }}</td>
            </tr>
            
          </table>
        </td>
        {{-- PASIVA --}}
        <td class="p-0" width="50%">
          <table id="p-id" class="table table-borderless">
            <tr>
              <td class="border-top border-right border-bottom" colspan="3"><b>Passiva</b></td>
            </tr>
            <tr>
              <td class="text-left border-right" width="33.33%"><b>Hutang Lancar</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>
            {{-- P1 --}}
            @foreach ($collect['P1'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }}</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL P1 --}}
            <tr>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P1_TOTAL']), 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-right" width="33.33%"><b>Hutang Jangka Panjang</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>
            {{-- P2 --}}
            @foreach ($collect['P2'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }}</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- TOTAL P2 --}}
            <tr>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P2_TOTAL']), 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-right" width="33.33%"><b>Modal</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>
            {{-- P3 --}}
            @foreach ($collect['P3'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }}</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach
            {{-- P4 --}}
            @foreach ($collect['P4'] as $item)
              <tr>
                <td class="text-left border-right" width="33.33%">{{ $item['name'] }}</td>
                <td class="text-left border-right" width="33.33%">({{ 'Rp. '.number_format(abs($item['saldo']), 2, ",", ".") }})</td>
                <td class="text-left border-right" width="33.33%"></td>
              </tr>
            @endforeach

            {{-- TOTAL P4 --}}
            <tr>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right border-top" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P3_TOTAL'] - $collect['P4_TOTAL']), 2, ",", ".") }}</td>
            </tr>

            <tr>
              <td class="text-left border-right" width="33.33%"><b>L/R Tahun Lalu</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format($collect['PL_PREV'], 2, ",", ".") }}</td>
            </tr>
            <tr>
              <td class="text-left border-right" width="33.33%"><b>L/R Tahun Berjalan</b></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%">{{ 'Rp. '.number_format($collect['PL_NOW'], 2, ",", ".") }}</td>
            </tr>

            {{-- SPACE --}}
            <tr>
              <td class="space-p text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
              <td class="text-left border-right" width="33.33%"></td>
            </tr>

            {{-- TOTAL P --}}
            <tr>
              <td class="text-right border-top border-right" colspan="2"><b>Total</b></td>
              <td class="text-left border-right border-top border-bottom" width="33.33%">{{ 'Rp. '.number_format(abs($collect['P1_TOTAL']) + abs($collect['P2_TOTAL']) + abs($collect['P3_TOTAL']) - abs($collect['P4_TOTAL']) + $collect['PL_PREV'] + $collect['PL_NOW'], 2, ",", ".") }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</div>

{{-- <div class="block">
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
        <tr>
          <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
          <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->debet, 2, ",", ".") }}</td>
          <td class="text-left" width="25%"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="25%">
          <b>Total Revenue</b>
        </td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['B']->sum('debet'), 2, ",", ".") }}</td>
      </tr>

      <tr>
        <td class="text-left" width="25%" colspan="3">
          <b>Cost :</b>
        </td>
      </tr>
      @foreach ($report['C'] as $item)
        <tr>
          <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
          <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->debet, 2, ",", ".") }}</td>
          <td class="text-left" width="25%"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="25%">
          <b>Total Cost</b>
        </td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['C']->sum('debet'), 2, ",", ".") }}</td>
      </tr>

      <tr>
        <td class="text-left" width="25%" colspan="3">
          <b>Other :</b>
        </td>
      </tr>
      @foreach ($report['D'] as $item)
        <tr>
          <td class="text-left" width="25%">{{ $loop->iteration }}. {{ $item->coa->name }}</td>
          <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->debet, 2, ",", ".") }}</td>
          <td class="text-left" width="25%"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="25%">
          <b>Total Other</b>
        </td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['D']->sum('debet'), 2, ",", ".") }}</td>
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
</div> --}}

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

  var a_height = $('#a-id').height();
  var p_height = $('#p-id').height();

  if(a_height > p_height) {
    $('.space-p').height( a_height - p_height - 0.5);
  } else if(a_height < p_height) {
    $('.space-a').height( p_height - a_height - 0.5);
  }

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
    window.location.href = '{{ route('superuser.report.balance_sheet.index') }}/'+$(this).val();
  });
});
</script>
@endpush
