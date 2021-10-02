<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\JournalSaldo;
use App\Entities\Account\Superuser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;

class BalanceSheetReportExcel implements FromView
{
  use Exportable;

  public function __construct($data)
  {
    $this->data = $data;
  }

  public function view(): View
  {
    $data = $this->data;
    return view('superuser.report.balance_sheet.excel', $data);
  }

}
