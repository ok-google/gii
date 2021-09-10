<?php

namespace App\DataTables\AccountingReport;

use App\DataTables\Table;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\JournalSaldo;
use App\Entities\Account\Superuser;
use App\Repositories\MasterRepo;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;