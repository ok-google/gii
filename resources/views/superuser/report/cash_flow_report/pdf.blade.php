<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CF-{{ \Carbon\Carbon::parse($journal_periode->from_date)->isoFormat('DDMMYY') }}</title>
    @include('superuser.asset.css-pdf')
    <style>
      td {
        padding: 5px;
      }
    </style>
  </head>
  <body style="font-size: 0.7em;">
    <table>
      <tr>
        <td class="text-center text-bold" style="font-size: 1.3em;">Cash Flow Report</td>
      </tr>
      <tr>
        <td class="text-center text-bold">{{ \Carbon\Carbon::parse($journal_periode->from_date)->isoFormat('DD/MM/YYYY') }} - {{ \Carbon\Carbon::parse($journal_periode->to_date)->isoFormat('DD/MM/YYYY') }}</td>
      </tr>
    </table>

    <table class="mt-25">
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

    {{-- PAGING --}}
    <script type="text/php">
      if (isset($pdf)) {
          $text = "Cash Flow Report | {{ \Carbon\Carbon::parse($journal_periode->from_date)->isoFormat('DD/MM/YYYY') }} - {{ \Carbon\Carbon::parse($journal_periode->to_date)->isoFormat('DD/MM/YYYY') }} | Page {PAGE_NUM} of {PAGE_COUNT}";
          $size = 8;
          $font = $fontMetrics->getFont("Verdana");
          $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
          $x = ($pdf->get_width() - $width) / 2 + 150;
          $y = $pdf->get_height() - 35;
          $pdf->page_text($x, $y, $text, $font, $size);
      }
    </script>
  </body>
</html>
