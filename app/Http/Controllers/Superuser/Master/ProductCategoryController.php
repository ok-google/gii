<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\ProductCategoryTable;
use App\Entities\Master\ProductCategory;
use App\Exports\Master\ProductCategoryExport;
use App\Exports\Master\ProductCategoryImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\ProductCategoryImport;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ProductCategoryController extends Controller
{
    public function json(Request $request, ProductCategoryTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('product category-manage')) {
            return abort(403);
        }

        return view('superuser.master.product_category.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('product category-create')) {
            return abort(403);
        }

        return view('superuser.master.product_category.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_product_categories,code',
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
                $product_category = new ProductCategory;

                $product_category->code = $request->code;
                $product_category->name = $request->name;
                $product_category->description = $request->description;
                $product_category->status = ProductCategory::STATUS['ACTIVE'];

                if ($product_category->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_category.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('product category-show')) {
            return abort(403);
        }

        $data['product_category'] = ProductCategory::findOrFail($id);

        return view('superuser.master.product_category.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('product category-edit')) {
            return abort(403);
        }

        $data['product_category'] = ProductCategory::findOrFail($id);

        return view('superuser.master.product_category.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $product_category = ProductCategory::find($id);

            if ($product_category == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_product_categories,code,' . $product_category->id,
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
                $product_category->code = $request->code;
                $product_category->name = $request->name;
                $product_category->description = $request->description;

                if ($product_category->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product_category.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('product category-delete')) {
                return abort(403);
            }

            $product_category = ProductCategory::find($id);

            if ($product_category === null) {
                abort(404);
            }

            $product_category->status = ProductCategory::STATUS['DELETED'];

            if ($product_category->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-product-category-import-template.xlsx';
        return Excel::download(new ProductCategoryImportTemplate, $filename);
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
            Excel::import(new ProductCategoryImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-product-category-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductCategoryExport, $filename);
    }
}
