<?php

namespace App\Imports\Accounting;

use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Entities\Account\Superuser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use DB;

HeadingRowFormatter::default('none');

class CoaImport implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;

    public function collection(Collection $rows)
    {
        $superuser = Superuser::find(Auth::guard('superuser')->id());

        DB::beginTransaction();
        $collect_error = [];

        try {
            foreach ($rows as $row) {
                $coa = new Coa;

                $coa->id   = $row['id'];
                $coa->code = $row['code'];
                $coa->type = $superuser->type;
                $coa->branch_office_id = $superuser->branch_office_id;
                $coa->name = $row['name'];
                $coa->group = $row['group'];
                $coa->parent_level_1 = $row['parent_level_1'] && $row['parent_level_1'] != 'NULL' ? $row['parent_level_1'] : null;
                $coa->parent_level_2 = $row['parent_level_2'] && $row['parent_level_2'] != 'NULL' ? $row['parent_level_2'] : null;
                $coa->parent_level_3 = $row['parent_level_3'] && $row['parent_level_3'] != 'NULL' ? $row['parent_level_3'] : null;
                $coa->status = Coa::STATUS['ACTIVE'];

                if($coa->save()) {
                    if($row['saldo_awal']) {
                        $journal = new Journal;

                        $journal->coa_id = $coa->id;
                        $journal->name = 'Saldo Awal';
                        
                        if($row['debet(1)_credit(0)'] == '1') {
                            $journal->debet = $row['saldo_awal'];
                        } else if($row['debet(1)_credit(0)'] == '0') {
                            $journal->credit = $row['saldo_awal'];
                        }

                        $journal->status = Journal::STATUS['UNPOST'];

                        $journal->save();
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            DB::rollBack();
        }
    }

    public function startRow(): int
    {
        return 2;
    }

}
