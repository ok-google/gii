<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\SubBrandReferenceTable;
use App\Entities\Master\SubBrandReference;
use App\Exports\Master\SubBrandReferenceExport;
use App\Exports\Master\SubBrandReferenceImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\SubBrandReferenceImport;
use App\Repositories\MasterRepo;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class SubBrandReferenceController extends Controller
{
    public function json(Request $request, SubBrandReferenceTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('sub brand-manage')) {
            return abort(403);
        }

        return view('superuser.master.sub_brand_reference.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('sub brand-create')) {
            return abort(403);
        }

        $data['brand_references'] = MasterRepo::brand_references();
        return view('superuser.master.sub_brand_reference.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_sub_brand_references,code',
                'brand_reference' => 'required|integer',
                'name' => 'required|string',
                'link' => 'nullable|string',
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
                $sub_brand_reference = new SubBrandReference;

                $sub_brand_reference->brand_reference_id = $request->brand_reference;
                $sub_brand_reference->code = $request->code;
                $sub_brand_reference->name = $request->name;
                $sub_brand_reference->link = $request->link;
                $sub_brand_reference->description = $request->description;
                $sub_brand_reference->status = SubBrandReference::STATUS['ACTIVE'];

                if ($sub_brand_reference->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.sub_brand_reference.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('sub brand-show')) {
            return abort(403);
        }

        $data['sub_brand_reference'] = SubBrandReference::findOrFail($id);

        return view('superuser.master.sub_brand_reference.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('sub brand-edit')) {
            return abort(403);
        }

        $data['brand_references'] = MasterRepo::brand_references();
        $data['sub_brand_reference'] = SubBrandReference::findOrFail($id);

        return view('superuser.master.sub_brand_reference.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $sub_brand_reference = SubBrandReference::find($id);

            if ($sub_brand_reference == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_sub_brand_references,code,' . $sub_brand_reference->id,
                'brand_reference' => 'required|integer',
                'name' => 'required|string',
                'link' => 'nullable|string',
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
                $sub_brand_reference->brand_reference_id = $request->brand_reference;
                $sub_brand_reference->code = $request->code;
                $sub_brand_reference->name = $request->name;
                $sub_brand_reference->link = $request->link;
                $sub_brand_reference->description = $request->description;

                if ($sub_brand_reference->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.sub_brand_reference.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('sub brand-delete')) {
                return abort(403);
            }

            $sub_brand_reference = SubBrandReference::find($id);

            if ($sub_brand_reference === null) {
                abort(404);
            }

            $sub_brand_reference->status = SubBrandReference::STATUS['DELETED'];

            if ($sub_brand_reference->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-sub-brand-reference-import-template.xlsx';
        return Excel::download(new SubBrandReferenceImportTemplate, $filename);
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
            Excel::import(new SubBrandReferenceImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-sub-brand-reference-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new SubBrandReferenceExport, $filename);
    }
}
