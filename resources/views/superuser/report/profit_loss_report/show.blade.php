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
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <div class="col-md-8">
            <span style="vertical-align: super;">Period :</span> <span class="h2">{{ \Carbon\Carbon::parse( $journal_periode->from_date )->format('d/m/Y') }} - {{ \Carbon\Carbon::parse( $journal_periode->to_date )->format('d/m/Y') }}</span>
          </div>
          <div class="col-md-4 text-right">
            <a href="{{ route('superuser.report.profit_loss_report.excel', $journal_periode->id) }}?coa={{$coa}}" target="_blank">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                <i class="fa fa-file-excel-o"></i>
              </button>
            </a>
            <a href="{{ route('superuser.report.profit_loss_report.pdf', $journal_periode->id) }}?coa={{$coa}}" target="_blank">
              <button type="button" class="btn bg-gd-sea border-0 text-white">
                Download P/L <i class="fa fa-sticky-note-o ml-10"></i>
              </button>
            </a>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-left" for="store">Coa :</label>
          <div class="col-md-4">
            <select class="js-select2 form-control" id="coa" name="coa[]" data-placeholder="Select Coa" multiple="multiple" required>
              <option value="all">All</option>
              @foreach ($coas as $item)
              <option value="{{ $item->id }}" data-code="{{ $item->code }}">{{ $item->code }} - {{ $item->name }}</option>
              @endforeach
            </select>
            <input type="hidden" value="{{$coa}}" id="coa-sel" />
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
        <div class="form-group row">
          <div class="col-md-12 text-center">
            <a href="#" id="btn-filter" class="btn bg-gd-corporate btn-block border-0 text-white">
              Filter <i class="fa fa-search ml-10"></i>
            </a>
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
        <td class="text-left" width="25%" colspan="4">
          <b>Pendapatan dari penjualan</b>
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%">Penjualan</td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['A'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
      </tr>
      <tr>
        <td class="text-left" width="25%">Retur Penjualan</td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['B'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
      </tr>
      <tr>
        <td class="text-left" width="25%">Potongan Penjualan</td>
        <td class="text-left border-bottom" width="25%">{{ 'Rp. '.number_format($report['C'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
      </tr>
      <tr>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['B']+$report['C'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
      </tr>
      <tr>
        <td class="text-left" width="25%"><b>Penjualan Bersih</b></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['D'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%"><b>Harga Pokok Penjualan</b></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left border-bottom" width="25%">{{ 'Rp. '.number_format($report['E'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%"><b>Laba Kotor</b></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['laba_kotor'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4"><b>Beban Operasional</b></td>
      </tr>
      @foreach ($report['F'] as $item)
        <tr>
          <td class="text-left" width="25%">{{ $item->coa->code .' - '. $item->coa->name }}</td>
          <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
          <td class="text-left" width="25%"></td>
          <td class="text-left" width="25%"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="25%"><b>Total Beban Operasional</b></td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['G'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4"><b>Beban Administrasi</b></td>
      </tr>
      @foreach ($report['H'] as $item)
        <tr>
          <td class="text-left" width="25%">{{ $item->coa->code .' - '. $item->coa->name }}</td>
          <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
          <td class="text-left" width="25%"></td>
          <td class="text-left" width="25%"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="25%"><b>Total Beban Administrasi</b></td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left border-bottom" width="25%">{{ 'Rp. '.number_format($report['I'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%"><b>Jumlah Beban Operasional</b></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left border-bottom" width="25%">{{ 'Rp. '.number_format($report['J'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%"><b>Laba Bersih Operasional</b></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['M'], 2, ",", ".") }}</td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4"><b>Pendapatan Lain - Lain :</b></td>
      </tr>
      @foreach ($report['K'] as $item)
        <tr>
          <td class="text-left" width="25%">{{ $item->coa->code .' - '. $item->coa->name }}</td>
          <td class="text-left" width="25%">{{ 'Rp. '.number_format($item->total_debet, 2, ",", ".") }}</td>
          <td class="text-left" width="25%"></td>
          <td class="text-left" width="25%"></td>
        </tr>
      @endforeach
      <tr>
        <td class="text-left" width="25%"><b>Total Pendapatan Lain - Lain</b></td>
        <td class="text-left border-top" width="25%"></td>
        <td class="text-left border-bottom" width="25%">{{ 'Rp. '.number_format($report['L'], 2, ",", ".") }}</td>
        <td class="text-left" width="25%"></td>
      </tr>
      <tr>
        <td class="text-left" width="25%" colspan="4">
        </td>
      </tr>
      <tr>
        <td class="text-left" width="25%"><b>Laba Bersih</b></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%"></td>
        <td class="text-left" width="25%">{{ 'Rp. '.number_format($report['laba_bersih'], 2, ",", ".") }}</td>
      </tr>
    </table>
  </div>
</div>

{{-- <div class="block">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped table-vcenter table-responsive">
      <thead>
        <tr>
          <th class="text-center">Date</th>
          <th class="text-left">Chart of Account</th>
          <th class="text-left">Transaction</th>
          <th class="text-center">Debet</th>
          <th class="text-center">Credit</th>
        </tr>
      </thead>
      <tbody>
        @php
            $total_debet = 0;
            $total_credit = 0;
        @endphp
        @foreach ($journal as $value)
        @php
            $total_debet = $total_debet + $value->debet;
            $total_credit = $total_credit + $value->credit;
        @endphp
            <tr>
              <td>{{ Carbon\Carbon::parse($value->created_at)->format('j/m/Y') }}</td>
              <td class="text-left">{{ $value->coa->code.' / '.$value->coa->name }}</td>
              <td class="text-left">{{ $value->name }}</td>
              <td>{{ $value->debet ? 'Rp. '.number_format($value->debet, 2, ",", ".") : '' }}</td>
              <td>{{ $value->credit ? 'Rp. '.number_format($value->credit, 2, ",", ".") : '' }}</td>
            </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <th class="text-left" colspan="3" id="action_table"></th>
          <th class="text-center" id="debet_total">Rp. {{ number_format($total_debet, 2, ",", ".") }}</th>
          <th class="text-center" id="credit_total">Rp. {{ number_format($total_credit, 2, ",", ".") }}</th>
        </tr>
      </tfoot>
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

  $('#periode').on('select2:select', function (e) {
    window.location.href = '{{ route('superuser.report.profit_loss_report.index') }}/'+$(this).val();
  });

  $("#btn-filter").click(function(){
    const coa = $('#coa').val();
    const per = $('#periode').val();
    window.location.href = '{{ route('superuser.report.profit_loss_report.index') }}/'+per+"?coa="+coa;
  });
  
  let coa_sel = $("#coa-sel").val();
  if(coa_sel == "all"){
    coa_sel = "all";
  }else{
    coa_sel = coa_sel.split(",");
  }
  
  $('#coa').val(coa_sel).trigger('change');
  $('#coa').on('select2:select', function (e) {
      var data = e.params.data.id;
      if(data == 'all') {
        $('#coa').val('all').trigger('change');
      } else {
        var all = $('#coa').val();
        const index = all.indexOf('all');
        if (index > -1) {
          all.splice(index, 1);
        }
        $('#coa').val(all).trigger('change');
      }
  });
});
</script>
@endpush
