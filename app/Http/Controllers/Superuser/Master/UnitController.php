<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\UnitTable;
use App\Entities\Master\Unit;
use App\Exports\Master\UnitExport;
use App\Exports\Master\UnitImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\UnitImport;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class UnitController extends Controller
{
    public function json(Request $request, UnitTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('unit-manage')) {
            return abort(403);
        }

        return view('superuser.master.unit.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('unit-create')) {
            return abort(403);
        }

        return view('superuser.master.unit.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'abbreviation' => 'required|string|unique:master_units,abbreviation',
                'description' => 'nullable|string'
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
                $unit = new Unit;

                $unit->name = $request->name;
                $unit->abbreviation = $request->abbreviation;
                $unit->description = $request->description;
                $unit->status = Unit::STATUS['ACTIVE'];

                if ($unit->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.unit.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('unit-show')) {
            return abort(403);
        }

        $data['unit'] = Unit::findOrFail($id);

        return view('superuser.master.unit.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('unit-edit')) {
            return abort(403);
        }

        $data['unit'] = Unit::findOrFail($id);

        return view('superuser.master.unit.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $unit = Unit::find($id);

            if ($unit == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'abbreviation' => 'required|string|unique:master_units,abbreviation,' . $unit->id,
                'description' => 'nullable|string'
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
                $unit->name = $request->name;
                $unit->abbreviation = $request->abbreviation;
                $unit->description = $request->description;

                if ($unit->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.unit.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('unit-delete')) {
                return abort(403);
            }

            $unit = Unit::find($id);

            if ($unit === null) {
                abort(404);
            }

            $unit->status = Unit::STATUS['DELETED'];

            if ($unit->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-unit-import-template.xlsx';
        return Excel::download(new UnitImportTemplate, $filename);
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
            Excel::import(new UnitImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-unit-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new UnitExport, $filename);
    }
}
