<?php

namespace App\Http\Controllers\Superuser\Finance;

use App\DataTables\Finance\JournalSettingTable;
use App\Http\Controllers\Controller;
use App\Entities\Accounting\Journal;
use App\Entities\Accounting\JournalPeriode;
use App\Entities\Accounting\Coa;
use App\Entities\Account\Superuser;
use App\Entities\Accounting\JournalSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use DB;

class JournalSettingController extends Controller
{
    public function json(Request $request, JournalSettingTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if (!Auth::guard('superuser')->user()->can('journal setting-manage')) {
            return abort(403);
        }

        return view('superuser.finance.journal_setting.index');
    }

    public function create()
    {
        if (!Auth::guard('superuser')->user()->can('journal setting-manage')) {
            return abort(403);
        }

        $superuser = Auth::guard('superuser')->user();

        $data['coa'] = Coa::where('type', $superuser->type)
            ->where('branch_office_id', $superuser->branch_office_id)
            ->get();

        $data['status'] = JournalSetting::STATUS;

        return view('superuser.finance.journal_setting.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {

                    $js = new JournalSetting;
                    $js->name = $request->name;
                    $js->debet_coa = $request->debet_coa;
                    $js->debet_note = $request->debet_note;
                    $js->credit_coa = $request->credit_coa;
                    $js->credit_note = $request->credit_note;
                    $js->status = $request->status;
                    $js->save();

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.finance.journal_setting.index');

                    return $this->response(200, $response);
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $e->getMessage(),
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function edit($id)
    {
        if (!Auth::guard('superuser')->user()->can('journal setting-manage')) {
            return abort(403);
        }

        $data['js'] = JournalSetting::findOrFail($id);

        $superuser = Auth::guard('superuser')->user();

        $data['coa'] = Coa::where('type', $superuser->type)
            ->where('branch_office_id', $superuser->branch_office_id)
            ->get();

        $data['status'] = JournalSetting::STATUS;

        return view('superuser.finance.journal_setting.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $js = JournalSetting::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];

                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                try {
                    $js->name = $request->name;
                    $js->debet_coa = $request->debet_coa;
                    $js->debet_note = $request->debet_note;
                    $js->credit_coa = $request->credit_coa;
                    $js->credit_note = $request->credit_note;
                    $js->status = $request->status;
                    $js->save();

                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.finance.journal_setting.index');

                    return $this->response(200, $response);
                } catch (\Exception $e) {
                    DB::rollback();
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => $e->getMessage(),
                    ];

                    return $this->response(400, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if (!Auth::guard('superuser')->user()->can('journal setting-manage')) {
                return abort(403);
            }

            $js = JournalSetting::findOrFail($id);

            if ($js->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
