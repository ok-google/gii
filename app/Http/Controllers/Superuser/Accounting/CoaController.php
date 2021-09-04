<?php

namespace App\Http\Controllers\Superuser\Accounting;

use App\DataTables\Accounting\CoaTable;
use App\Entities\Account\Superuser;
use App\Entities\Accounting\Coa;
use App\Entities\Accounting\Journal;
use App\Exports\Accounting\CoaImportTemplate;
use App\Imports\Accounting\CoaImport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Validator;
use DB;
use Excel;

class CoaController extends Controller
{
    public function select_parent_level_1(Request $request)
    {
        if ($request->ajax()) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            
            $parent_level_1 = Coa::where('type', $superuser->type)
                                ->where('branch_office_id', $superuser->branch_office_id)
                                ->whereNull('parent_level_1')
                                ->get();

            return response()->json(['code'=> 200, 'data' => $parent_level_1]);
        }
    }

    public function select_parent_level_2(Request $request)
    {
        if ($request->ajax()) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            
            $parent_level_2 = Coa::where('type', $superuser->type)
                                ->where('branch_office_id', $superuser->branch_office_id)
                                ->where('parent_level_1', $request->id_parent_level_1)
                                ->whereNull('parent_level_2')
                                ->get();

            return response()->json(['code'=> 200, 'data' => $parent_level_2]);
        }
    }

    public function select_parent_level_3(Request $request)
    {
        if ($request->ajax()) {
            $superuser = Superuser::find(Auth::guard('superuser')->id());
            
            $parent_level_3 = Coa::where('type', $superuser->type)
                                ->where('branch_office_id', $superuser->branch_office_id)
                                ->where('parent_level_1', $request->id_parent_level_1)
                                ->where('parent_level_2', $request->id_parent_level_2)
                                ->whereNull('parent_level_3')
                                ->get();
                                
            return response()->json(['code'=> 200, 'data' => $parent_level_3]);
        }
    }

    public function json(Request $request, CoaTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('master coa-manage')) {
            return abort(403);
        }

        return view('superuser.accounting.coa.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('master coa-create')) {
            return abort(403);
        }

        return view('superuser.accounting.coa.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code_column_1' => 'min:2',
                'code_column_2' => 'min:2',
                'code_column_3' => 'min:2',
                'code_column_4' => 'min:4',
                'name' => 'required|string',
                'group' => 'required|integer',
                'parent_level_1' => 'nullable|integer',
                'parent_level_2' => 'nullable|integer',
                'parent_level_3' => 'nullable|integer',

                'use_saldo_awal' => 'nullable',
                'transaction' => Rule::requiredIf(function () use ($request) {
                    return $request->has('use_saldo_awal');
                }),
                'saldo_awal' => [
                    Rule::requiredIf(function () use ($request) {
                        return $request->has('use_saldo_awal');
                    }),
                    'nullable',
                    'integer'
                ],
                'debet_credit' => Rule::requiredIf(function () use ($request) {
                    return $request->has('use_saldo_awal');
                }),
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
                $superuser = Superuser::find(Auth::guard('superuser')->id());

                $code = $request->code_column_1.'.'.$request->code_column_2.'.'.$request->code_column_3.'.'.$request->code_column_4;

                $is_duplicate_code = Coa::where('code', $code)->where('type', $superuser->type)->where('branch_office_id', $superuser->branch_office_id)->first();

                if ($is_duplicate_code) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => ['Code has been used.'],
                    ];
      
                    return $this->response(400, $response);
                }

                $coa = new Coa;

                $coa->code = $code;
                $coa->kode_pelunasan = $request->kode_pelunasan ?? null;
                $coa->type = $superuser->type;
                $coa->branch_office_id = $superuser->branch_office_id;
                $coa->name = $request->name;
                $coa->group = $request->group;
                $coa->parent_level_1 = $request->parent_level_1;
                $coa->parent_level_2 = $request->parent_level_2;
                $coa->parent_level_3 = $request->parent_level_3;
                $coa->status = Coa::STATUS['ACTIVE'];

                if ($coa->save()) {
                    if($request->has('use_saldo_awal')) {
                        $journal = new Journal;

                        $journal->coa_id = $coa->id;
                        $journal->name = $request->transaction;
                        
                        if($request->debet_credit == 'debet') {
                            $journal->debet = $request->saldo_awal;
                        } else if($request->debet_credit == 'credit') {
                            $journal->credit = $request->saldo_awal;
                        }

                        $journal->status = Journal::STATUS['UNPOST'];

                        $journal->save();
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.accounting.coa.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('master coa-show')) {
            return abort(403);
        }

        $data['coa'] = Coa::findOrFail($id);

        return view('superuser.accounting.coa.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('master coa-edit')) {
            return abort(403);
        }

        $data['coa'] = Coa::findOrFail($id);
        $data['pecah_code'] = explode('.', $data['coa']->code);
        
        return view('superuser.accounting.coa.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $coa = Coa::find($id);

            if ($coa == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code_column_1' => 'min:2',
                'code_column_2' => 'min:2',
                'code_column_3' => 'min:2',
                'code_column_4' => 'min:4',
                'name' => 'required|string',
                'group' => 'required|integer',
                'parent_level_1' => 'nullable|integer',
                'parent_level_2' => 'nullable|integer',
                'parent_level_3' => 'nullable|integer'
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
                $superuser = Superuser::find(Auth::guard('superuser')->id());

                $code = $request->code_column_1.'.'.$request->code_column_2.'.'.$request->code_column_3.'.'.$request->code_column_4;

                $is_duplicate_code = Coa::where('code', $code)
                                            ->where('id', '<>', $coa->id)
                                            ->where('type', $superuser->type)
                                            ->where('branch_office_id', $superuser->branch_office_id)
                                            ->first();

                if ($is_duplicate_code) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => ['Code has been used.'],
                    ];
      
                    return $this->response(400, $response);
                }

                $coa->code = $code;
                $coa->kode_pelunasan = $request->kode_pelunasan ?? null;
                $coa->type = $superuser->type;
                $coa->branch_office_id = $superuser->branch_office_id;
                $coa->name = $request->name;
                $coa->group = $request->group;
                $coa->parent_level_1 = $request->parent_level_1;
                $coa->parent_level_2 = $request->parent_level_2;
                $coa->parent_level_3 = $request->parent_level_3;

                if ($coa->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.accounting.coa.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('master coa-delete')) {
                return abort(403);
            }

            $coa = Coa::find($id);

            if ($coa === null) {
                abort(404);
            }

            DB::beginTransaction();
            try {
                $coa->status = Coa::STATUS['DELETED'];

                if ($coa->forceDelete()) {
                    DB::commit();

                    $response['redirect_to'] = '#datatable';
                    return $this->response(200, $response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response['redirect_to'] = '#datatable';
                return $this->response(400, $response);
            }
            
        }
    }

    public function import_template()
    {
        $filename = 'master-coa-import-template.xlsx';
        return Excel::download(new CoaImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new CoaImport;
            Excel::import($import, $request->import_file);
            
            if($import->error) {
                return redirect()->back()->withErrors($import->error);
            }
            
            return redirect()->back()->with(['message' => 'Import success']);
        }
    }

    // public function export()
    // {
    //     $filename = 'master-supplier-' . date('d-m-Y_H-i-s') . '.xlsx';
    //     return Excel::download(new SupplierExport, $filename);
    // }
}
