<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\ProductTypeTable;
use App\Entities\Master\ProductType;
use App\Exports\Master\ProductTypeExport;
use App\Exports\Master\ProductTypeImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\ProductTypeImport;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ProductTypeController extends Controller
{
    public function json(Request $request, ProductTypeTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('product type-manage')) {
            return abort(403);
        }

        return view('superuser.master.product_type.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('product type-create')) {
            return abort(403);
        }

        return view('superuser.master.product_type.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_product_types,code',
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
                $product_type = new ProductType;

                $product_type->code = $request->code;
                $product_type->name = $request->name;
                $product_type->description = $request->description;
                $product_type->status = ProductType::STATUS['ACTIVE'];

                if ($product_type->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_type.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('product type-show')) {
            return abort(403);
        }

        $data['product_type'] = ProductType::findOrFail($id);

        return view('superuser.master.product_type.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('product type-edit')) {
            return abort(403);
        }

        $data['product_type'] = ProductType::findOrFail($id);

        return view('superuser.master.product_type.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $product_type = ProductType::find($id);

            if ($product_type == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_product_types,code,' . $product_type->id,
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
                $product_type->code = $request->code;
                $product_type->name = $request->name;
                $product_type->description = $request->description;

                if ($product_type->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_type.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('product type-delete')) {
                return abort(403);
            }

            $product_type = ProductType::find($id);

            if ($product_type === null) {
                abort(404);
            }

            $product_type->status = ProductType::STATUS['DELETED'];

            if ($product_type->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-product-type-import-template.xlsx';
        return Excel::download(new ProductTypeImportTemplate, $filename);
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
            Excel::import(new ProductTypeImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-product-type-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductTypeExport, $filename);
    }
}
