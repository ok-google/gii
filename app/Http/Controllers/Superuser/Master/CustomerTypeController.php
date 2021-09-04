<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\CustomerTypeTable;
use App\Entities\Master\CustomerType;
use App\Exports\Master\CustomerTypeExport;
use App\Exports\Master\CustomerTypeImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\CustomerTypeImport;
use App\Repositories\CustomerRepo;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class CustomerTypeController extends Controller
{
    public function json(Request $request, CustomerTypeTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('customer type-manage')) {
            return abort(403);
        }

        return view('superuser.master.customer_type.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('customer type-create')) {
            return abort(403);
        }

        return view('superuser.master.customer_type.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_customer_types,code',
                'name' => 'required|string',
                'grosir_address' => 'nullable|string',
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
                $customer_type = new CustomerType;

                // $customer_type->code = CustomerRepo::generateTypeCode($request->name);
                // $duplicate = CustomerType::where('code', $customer_type->code)->first();
                // if ($duplicate) {
                //     $response['notification'] = [
                //         'alert' => 'block',
                //         'type' => 'alert-danger',
                //         'header' => 'Error',
                //         'content' => ['The generated code from name [' . $request->name . '] is a duplicate [' . $duplicate->code . ']'],
                //     ];
                
                //     return $this->response(400, $response);
                // }
                        
                $customer_type->code = $request->code;
                $customer_type->name = $request->name;
                
                $customer_type->grosir_address = '0';
                if($request->has('grosir_address')) {
                    $customer_type->grosir_address = '1';
                }

                $customer_type->status = CustomerType::STATUS['ACTIVE'];

                if ($customer_type->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer_type.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('customer type-show')) {
            return abort(403);
        }

        $data['customer_type'] = CustomerType::findOrFail($id);

        return view('superuser.master.customer_type.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('customer type-edit')) {
            return abort(403);
        }

        $data['customer_type'] = CustomerType::findOrFail($id);

        return view('superuser.master.customer_type.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $customer_type = CustomerType::find($id);

            if ($customer_type == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_customer_types,code,' . $customer_type->id,
                'name' => 'required|string',
                'grosir_address' => 'nullable|string',
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
                $customer_type->code = $request->code;
                $customer_type->name = $request->name;

                $customer_type->grosir_address = '0';
                if($request->has('grosir_address')) {
                    $customer_type->grosir_address = '1';
                }

                if ($customer_type->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer_type.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('customer type-delete')) {
                return abort(403);
            }

            $customer_type = CustomerType::find($id);

            if ($customer_type === null) {
                abort(404);
            }

            $customer_type->status = CustomerType::STATUS['DELETED'];

            if ($customer_type->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-customer-type-import-template.xlsx';
        return Excel::download(new CustomerTypeImportTemplate, $filename);
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
            Excel::import(new CustomerTypeImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-customer-type-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new CustomerTypeExport, $filename);
    }
}
