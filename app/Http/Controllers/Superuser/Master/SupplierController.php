<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\SupplierTable;
use App\Entities\Accounting\Coa;
use App\Entities\Master\Supplier;
use App\Entities\Master\SupplierCoa;
use App\Exports\Master\SupplierExport;
use App\Exports\Master\SupplierImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\SupplierImport;
use App\Repositories\MasterRepo;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class SupplierController extends Controller
{
    public function json(Request $request, SupplierTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('supplier-manage')) {
            return abort(403);
        }

        return view('superuser.master.supplier.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('supplier-create')) {
            return abort(403);
        }

        $data['coa_head_office'] = Coa::where('type', COA::TYPE['HEAD_OFFICE'])->get();

        $data['branches'] = MasterRepo::branch_offices();

        foreach ($data['branches'] as $value) {
            $data['coa_branch'][$value->id] = Coa::where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->get();
        }

        return view('superuser.master.supplier.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_supplier,code',
                'name' => 'required|string',
                'address' => 'nullable|string',

                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',

                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',

                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',

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
                $supplier = new Supplier;

                $supplier->code = $request->code;
                $supplier->name = $request->name;
                $supplier->address = $request->address;

                $supplier->provinsi = $request->provinsi;
                $supplier->kota = $request->kota;
                $supplier->kecamatan = $request->kecamatan;
                $supplier->kelurahan = $request->kelurahan;
                $supplier->text_provinsi = $request->text_provinsi;
                $supplier->text_kota = $request->text_kota;
                $supplier->text_kecamatan = $request->text_kecamatan;
                $supplier->text_kelurahan = $request->text_kelurahan;

                $supplier->zipcode = $request->zipcode;

                $supplier->email = $request->email;
                $supplier->phone = $request->phone;
                $supplier->fax = $request->fax;

                $supplier->owner_name = $request->owner_name;
                $supplier->website = $request->website;
                
                $supplier->description = $request->description;
                $supplier->status = Supplier::STATUS['ACTIVE'];

                if ($supplier->save()) {

                    $coa_supplier_head_office = SupplierCoa::where('supplier_id', $supplier->id)->where('type', COA::TYPE['HEAD_OFFICE'])->first();
                    if($coa_supplier_head_office == null) {
                        $coa_supplier_head_office = new SupplierCoa;
                        $coa_supplier_head_office->supplier_id = $supplier->id;
                        $coa_supplier_head_office->type = COA::TYPE['HEAD_OFFICE'];
                        $coa_supplier_head_office->coa_id = $request->coa_head_office_id;
                        $coa_supplier_head_office->save();
                    } else {
                        $coa_supplier_head_office->coa_id = $request->coa_head_office_id;
                        $coa_supplier_head_office->save();
                    }

                    if($request->branch_id) {
                        foreach($request->branch_id as $key => $value){
                            if($request->branch_id[$key]) {
                                $coa_supplier_branch_office = SupplierCoa::where('supplier_id', $supplier->id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $request->branch_id[$key])->first();
                                if($coa_supplier_branch_office == null) {
                                    $coa_supplier_branch_office = new SupplierCoa;
                                    $coa_supplier_branch_office->supplier_id = $supplier->id;
                                    $coa_supplier_branch_office->type = COA::TYPE['BRANCH_OFFICE'];
                                    $coa_supplier_branch_office->branch_office_id = $request->branch_id[$key];
                                    $coa_supplier_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_supplier_branch_office->save();
                                } else {
                                    $coa_supplier_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_supplier_branch_office->save();
                                }
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.supplier.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('supplier-show')) {
            return abort(403);
        }

        $data['supplier'] = Supplier::findOrFail($id);

        $data['coa_head_office'] = Coa::where('type', COA::TYPE['HEAD_OFFICE'])->get();

        if($select_id = SupplierCoa::where('supplier_id', $id)->where('type', COA::TYPE['HEAD_OFFICE'])->first()) {
           $data['coa_head_office_selected'] = $select_id->coa_id;
        } else {
            $data['coa_head_office_selected'] = '';
        }
        
        $data['branches'] = MasterRepo::branch_offices();
        foreach ($data['branches'] as $value) {
            $data['coa_branch'][$value->id] = Coa::where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->get();

            if($select_id = SupplierCoa::where('supplier_id', $id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->first()) {
                $data['coa_branch_selected'][$value->id] = $select_id->coa_id;
            } else {
                $data['coa_branch_selected'][$value->id] = '';
            }
        }

        return view('superuser.master.supplier.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('supplier-edit')) {
            return abort(403);
        }

        $data['supplier'] = Supplier::findOrFail($id);

        $data['coa_head_office'] = Coa::where('type', COA::TYPE['HEAD_OFFICE'])->get();

        if($select_id = SupplierCoa::where('supplier_id', $id)->where('type', COA::TYPE['HEAD_OFFICE'])->first()) {
           $data['coa_head_office_selected'] = $select_id->coa_id;
        } else {
            $data['coa_head_office_selected'] = '';
        }
        
        $data['branches'] = MasterRepo::branch_offices();
        foreach ($data['branches'] as $value) {
            $data['coa_branch'][$value->id] = Coa::where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->get();

            if($select_id = SupplierCoa::where('supplier_id', $id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->first()) {
                $data['coa_branch_selected'][$value->id] = $select_id->coa_id;
            } else {
                $data['coa_branch_selected'][$value->id] = '';
            }
        }

        return view('superuser.master.supplier.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $supplier = Supplier::find($id);

            if ($supplier == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_supplier,code,'. $supplier->id,
                'name' => 'required|string',
                'address' => 'nullable|string',

                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',

                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',

                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',

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
                $supplier->code = $request->code;
                $supplier->name = $request->name;
                $supplier->address = $request->address;

                $supplier->provinsi = $request->provinsi;
                $supplier->kota = $request->kota;
                $supplier->kecamatan = $request->kecamatan;
                $supplier->kelurahan = $request->kelurahan;
                $supplier->text_provinsi = $request->text_provinsi;
                $supplier->text_kota = $request->text_kota;
                $supplier->text_kecamatan = $request->text_kecamatan;
                $supplier->text_kelurahan = $request->text_kelurahan;

                $supplier->zipcode = $request->zipcode;

                $supplier->email = $request->email;
                $supplier->phone = $request->phone;
                $supplier->fax = $request->fax;

                $supplier->owner_name = $request->owner_name;
                $supplier->website = $request->website;
                
                $supplier->description = $request->description;

                if ($supplier->save()) {
                    $coa_supplier_head_office = SupplierCoa::where('supplier_id', $supplier->id)->where('type', COA::TYPE['HEAD_OFFICE'])->first();
                    if($coa_supplier_head_office == null) {
                        $coa_supplier_head_office = new SupplierCoa;
                        $coa_supplier_head_office->supplier_id = $supplier->id;
                        $coa_supplier_head_office->type = COA::TYPE['HEAD_OFFICE'];
                        $coa_supplier_head_office->coa_id = $request->coa_head_office_id;
                        $coa_supplier_head_office->save();
                    } else {
                        $coa_supplier_head_office->coa_id = $request->coa_head_office_id;
                        $coa_supplier_head_office->save();
                    }

                    if($request->branch_id) {
                        foreach($request->branch_id as $key => $value){
                            if($request->branch_id[$key]) {
                                $coa_supplier_branch_office = SupplierCoa::where('supplier_id', $supplier->id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $request->branch_id[$key])->first();
                                if($coa_supplier_branch_office == null) {
                                    $coa_supplier_branch_office = new SupplierCoa;
                                    $coa_supplier_branch_office->supplier_id = $supplier->id;
                                    $coa_supplier_branch_office->type = COA::TYPE['BRANCH_OFFICE'];
                                    $coa_supplier_branch_office->branch_office_id = $request->branch_id[$key];
                                    $coa_supplier_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_supplier_branch_office->save();
                                } else {
                                    $coa_supplier_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_supplier_branch_office->save();
                                }
                            }
                        }
                    }
                    
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.supplier.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    // public function generate_coa()
    // {
    //     if(!Auth::guard('superuser')->user()->can('supplier-create')) {
    //         return abort(403);
    //     }
        
    //     DB::beginTransaction();
    //     try {

    //         DB::commit();
    //         return redirect()->back()->withMessage('Generate COA Supplier Successfully.');
    //     } catch (\Throwable $th) {
    //         DB::rollback();
    //         return redirect()->back()->withErrors('Something went wrong, please try again!');
    //     }
    // }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('supplier-delete')) {
                return abort(403);
            }

            $supplier = Supplier::find($id);

            if ($supplier === null) {
                abort(404);
            }

            $supplier->status = Supplier::STATUS['DELETED'];

            if ($supplier->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-supplier-import-template.xlsx';
        return Excel::download(new SupplierImportTemplate, $filename);
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
            Excel::import(new SupplierImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-supplier-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new SupplierExport, $filename);
    }
}
