<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\CustomerCategoryTable;
use App\Entities\Master\CustomerCategory;
use App\Exports\Master\CustomerCategoryExport;
use App\Exports\Master\CustomerCategoryImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\CustomerCategoryImport;
use App\Repositories\CustomerRepo;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class CustomerCategoryController extends Controller
{
    public function json(Request $request, CustomerCategoryTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('customer category-manage')) {
            return abort(403);
        }

        return view('superuser.master.customer_category.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('customer category-create')) {
            return abort(403);
        }

        return view('superuser.master.customer_category.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_customer_categories,code',
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
                $customer_category = new CustomerCategory;

                $customer_category->code = $request->code;
                $customer_category->name = $request->name;
                $customer_category->status = CustomerCategory::STATUS['ACTIVE'];

                if ($customer_category->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer_category.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('customer category-show')) {
            return abort(403);
        }

        $data['customer_category'] = CustomerCategory::findOrFail($id);

        return view('superuser.master.customer_category.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('customer category-edit')) {
            return abort(403);
        }

        $data['customer_category'] = CustomerCategory::findOrFail($id);

        return view('superuser.master.customer_category.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $customer_category = CustomerCategory::find($id);

            if ($customer_category == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_customer_categories,code,' . $customer_category->id,
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
                $customer_category->code = $request->code;
                $customer_category->name = $request->name;

                if ($customer_category->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer_category.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('customer category-delete')) {
                return abort(403);
            }

            $customer_category = CustomerCategory::find($id);

            if ($customer_category === null) {
                abort(404);
            }

            $customer_category->status = CustomerCategory::STATUS['DELETED'];

            if ($customer_category->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-customer-category-import-template.xlsx';
        return Excel::download(new CustomerCategoryImportTemplate, $filename);
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
            Excel::import(new CustomerCategoryImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-customer-category-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new CustomerCategoryExport, $filename);
    }
}
