<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\BrandReferenceTable;
use App\Entities\Master\BrandReference;
use App\Exports\Master\BrandReferenceExport;
use App\Exports\Master\BrandReferenceImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\BrandReferenceImport;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class BrandReferenceController extends Controller
{
    public function json(Request $request, BrandReferenceTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('brand-manage')) {
            return abort(403);
        }

        return view('superuser.master.brand_reference.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('brand-create')) {
            return abort(403);
        }

        return view('superuser.master.brand_reference.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_brand_references,code',
                'name' => 'required|string',
                'description' => 'nullable|string',
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
                $brand_reference = new BrandReference;

                $brand_reference->code = $request->code;
                $brand_reference->name = $request->name;
                $brand_reference->description = $request->description;
                $brand_reference->status = BrandReference::STATUS['ACTIVE'];

                if ($brand_reference->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.brand_reference.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('brand-show')) {
            return abort(403);
        }

        $data['brand_reference'] = BrandReference::findOrFail($id);

        return view('superuser.master.brand_reference.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('brand-edit')) {
            return abort(403);
        }

        $data['brand_reference'] = BrandReference::findOrFail($id);

        return view('superuser.master.brand_reference.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $brand_reference = BrandReference::find($id);

            if ($brand_reference == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_brand_references,code,' . $brand_reference->id,
                'name' => 'required|string',
                'description' => 'nullable|string',
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
                $brand_reference->code = $request->code;
                $brand_reference->name = $request->name;
                $brand_reference->description = $request->description;

                if ($brand_reference->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.brand_reference.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('brand-delete')) {
                return abort(403);
            }

            $brand_reference = BrandReference::find($id);

            if ($brand_reference === null) {
                abort(404);
            }

            $brand_reference->status = BrandReference::STATUS['DELETED'];

            if ($brand_reference->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-brand-reference-import-template.xlsx';
        return Excel::download(new BrandReferenceImportTemplate, $filename);
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
            Excel::import(new BrandReferenceImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-brand-reference-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new BrandReferenceExport, $filename);
    }
}
