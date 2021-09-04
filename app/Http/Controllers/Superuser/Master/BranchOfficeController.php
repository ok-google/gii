<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\BranchOfficeTable;
use App\Entities\Master\BranchOffice;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class BranchOfficeController extends Controller
{
    public function json(Request $request, BranchOfficeTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('branch office-manage')) {
            return abort(403);
        }

        return view('superuser.master.branch_office.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('branch office-create')) {
            return abort(403);
        }

        return view('superuser.master.branch_office.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_branch_offices,code',
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'address' => 'required|string',
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
                $branch_office = new BranchOffice;

                $branch_office->code = $request->code;
                $branch_office->name = $request->name;
                $branch_office->contact_person = $request->contact_person;
                $branch_office->phone = $request->phone;
                $branch_office->fax = $request->fax;
                $branch_office->address = $request->address;
                $branch_office->description = $request->description;
                $branch_office->status = BranchOffice::STATUS['ACTIVE'];

                if ($branch_office->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.branch_office.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('branch office-show')) {
            return abort(403);
        }

        $data['branch_office'] = BranchOffice::findOrFail($id);

        return view('superuser.master.branch_office.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('branch office-edit')) {
            return abort(403);
        }

        $data['branch_office'] = BranchOffice::findOrFail($id);

        return view('superuser.master.branch_office.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $branch_office = BranchOffice::find($id);

            if ($branch_office == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_branch_offices,code,' . $branch_office->id,
                'name' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'address' => 'required|string',
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
                $branch_office->code = $request->code;
                $branch_office->name = $request->name;
                $branch_office->contact_person = $request->contact_person;
                $branch_office->phone = $request->phone;
                $branch_office->fax = $request->fax;
                $branch_office->address = $request->address;
                $branch_office->description = $request->description;

                if ($branch_office->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.branch_office.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('branch office-delete')) {
                return abort(403);
            }

            $branch_office = BranchOffice::find($id);

            if ($branch_office === null) {
                abort(404);
            }

            $branch_office->status = BranchOffice::STATUS['DELETED'];

            if ($branch_office->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
