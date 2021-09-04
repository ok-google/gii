<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\WarehouseTable;
use App\Entities\Master\Warehouse;
use App\Http\Controllers\Controller;
use App\Repositories\MasterRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Validator;

class WarehouseController extends Controller
{
    public function json(Request $request, WarehouseTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('warehouse-manage')) {
            return abort(403);
        }

        return view('superuser.master.warehouse.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('warehouse-create')) {
            return abort(403);
        }

        $data['branch_offices'] = MasterRepo::branch_offices();

        return view('superuser.master.warehouse.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'type' => 'required|integer',
                'branch_office' => Rule::requiredIf(function () use ($request) {
                    return $request->type == Warehouse::TYPE['BRANCH_OFFICE'];
                }),
                'code' => 'required|string|unique:master_warehouses,code',
                'name' => 'required|string',
                'category' => 'required|integer',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'address' => 'nullable|string',
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
                $warehouse = new Warehouse;

                $warehouse->type = $request->type;
                $warehouse->branch_office_id = ($warehouse->type == Warehouse::TYPE['HEAD_OFFICE']) ? null : $request->branch_office;
                $warehouse->code = $request->code;
                $warehouse->name = $request->name;
                $warehouse->category = $request->category;
                $warehouse->contact_person = $request->contact_person;
                $warehouse->phone = $request->phone;
                $warehouse->fax = $request->fax;
                $warehouse->address = $request->address;
                $warehouse->description = $request->description;
                $warehouse->status = Warehouse::STATUS['ACTIVE'];

                if ($warehouse->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.warehouse.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('warehouse-show')) {
            return abort(403);
        }

        $data['warehouse'] = Warehouse::findOrFail($id);

        return view('superuser.master.warehouse.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('warehouse-edit')) {
            return abort(403);
        }

        $data['warehouse'] = Warehouse::findOrFail($id);
        $data['branch_offices'] = MasterRepo::branch_offices();

        return view('superuser.master.warehouse.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $warehouse = Warehouse::find($id);

            if ($warehouse == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|integer',
                'branch_office' => Rule::requiredIf(function () use ($request) {
                    return $request->type == Warehouse::TYPE['BRANCH_OFFICE'];
                }),
                'code' => 'required|string|unique:master_warehouses,code,' . $warehouse->id,
                'name' => 'required|string',
                'category' => 'required|integer',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'address' => 'nullable|string',
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
                $warehouse->type = $request->type;
                $warehouse->branch_office_id = ($warehouse->type == Warehouse::TYPE['HEAD_OFFICE']) ? null : $request->branch_office;
                $warehouse->code = $request->code;
                $warehouse->name = $request->name;
                $warehouse->category = $request->category;
                $warehouse->contact_person = $request->contact_person;
                $warehouse->phone = $request->phone;
                $warehouse->fax = $request->fax;
                $warehouse->address = $request->address;
                $warehouse->description = $request->description;

                if ($warehouse->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.warehouse.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('warehouse-delete')) {
                return abort(403);
            }

            $warehouse = Warehouse::find($id);

            if ($warehouse === null) {
                abort(404);
            }

            $warehouse->status = Warehouse::STATUS['DELETED'];

            if ($warehouse->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
