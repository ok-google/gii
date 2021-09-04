<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\CustomerTable;
use App\Entities\Accounting\Coa;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerType;
use App\Entities\Master\CustomerCoa;
use App\Entities\Master\CustomerCoaPenjualan;
use App\Exports\Master\CustomerExport;
use App\Exports\Master\CustomerImportTemplate;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use App\Imports\Master\CustomerImport;
use App\Repositories\MasterRepo;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Validator;

class CustomerController extends Controller
{
    public function json(Request $request, CustomerTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('customer-manage')) {
            return abort(403);
        }

        return view('superuser.master.customer.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('customer-create')) {
            return abort(403);
        }

        $data['customer_categories'] = MasterRepo::customer_categories();
        $data['customer_types'] = MasterRepo::customer_types();

        $data['coa_head_office'] = Coa::where('type', COA::TYPE['HEAD_OFFICE'])->get();

        $data['branches'] = MasterRepo::branch_offices();

        foreach ($data['branches'] as $value) {
            $data['coa_branch'][$value->id] = Coa::where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->get();
        }

        return view('superuser.master.customer.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_customers,code',
                'name' => 'required|string',
                'store' => 'required|string',
                'category' => 'required|integer',
                'type' => 'required|integer',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'address' => 'required|string',
                'address_do' => Rule::requiredIf(function () use ($request) {
                    return '1' == CustomerType::where('id', $request->type)->first()->grosir_address;
                }),
                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',
                'plafon_piutang' => 'nullable|numeric',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
                'image_store' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'notification_email' => 'nullable'
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
                $customer = new Customer;

                $customer->code = $request->code;
                $customer->name = $request->name;
                $customer->store = $request->store;

                $customer->category_id = $request->category;
                $customer->type_id = $request->type;

                $customer->email = $request->email;
                $customer->phone = $request->phone;
                $customer->fax = $request->fax;
                $customer->address = $request->address;

                $customer->address_do = (CustomerType::where('id', $request->type)->first()->grosir_address == '1' ? $request->address_do : '');

                $customer->owner_name = $request->owner_name;
                $customer->website = $request->website;
                $customer->plafon_piutang = ($request->plafon_piutang) ? $request->plafon_piutang : 0;

                $customer->provinsi = $request->provinsi;
                $customer->kota = $request->kota;
                $customer->kecamatan = $request->kecamatan;
                $customer->kelurahan = $request->kelurahan;
                $customer->text_provinsi = $request->text_provinsi;
                $customer->text_kota = $request->text_kota;
                $customer->text_kecamatan = $request->text_kecamatan;
                $customer->text_kelurahan = $request->text_kelurahan;

                $customer->zipcode = $request->zipcode;

                if (!empty($request->file('image_store'))) {
                    $customer->image_store = UploadMedia::image($request->file('image_store'), Customer::$directory_image);
                }

                $customer->notification_email = ($request->notification_email) ? true : false;
                $customer->status = Customer::STATUS['ACTIVE'];

                if ($customer->save()) {
                    $coa_customer_head_office = CustomerCoa::where('customer_id', $customer->id)->where('type', COA::TYPE['HEAD_OFFICE'])->first();
                    if($coa_customer_head_office == null) {
                        $coa_customer_head_office = new CustomerCoa;
                        $coa_customer_head_office->customer_id = $customer->id;
                        $coa_customer_head_office->type = COA::TYPE['HEAD_OFFICE'];
                        $coa_customer_head_office->coa_id = $request->coa_head_office_id;
                        $coa_customer_head_office->save();
                    } else {
                        $coa_customer_head_office->coa_id = $request->coa_head_office_id;
                        $coa_customer_head_office->save();
                    }

                    $coa_penjualan_customer_head_office = CustomerCoaPenjualan::where('customer_id', $customer->id)->where('type', COA::TYPE['HEAD_OFFICE'])->first();
                    if($coa_penjualan_customer_head_office == null) {
                        $coa_penjualan_customer_head_office = new CustomerCoaPenjualan;
                        $coa_penjualan_customer_head_office->customer_id = $customer->id;
                        $coa_penjualan_customer_head_office->type = COA::TYPE['HEAD_OFFICE'];
                        $coa_penjualan_customer_head_office->coa_id = $request->coa_penjualan_head_office_id;
                        $coa_penjualan_customer_head_office->save();
                    } else {
                        $coa_penjualan_customer_head_office->coa_id = $request->coa_penjualan_head_office_id;
                        $coa_penjualan_customer_head_office->save();
                    }

                    if($request->branch_id) {
                        foreach($request->branch_id as $key => $value){
                            if($request->branch_id[$key]) {
                                $coa_customer_branch_office = CustomerCoa::where('customer_id', $customer->id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $request->branch_id[$key])->first();
                                if($coa_customer_branch_office == null) {
                                    $coa_customer_branch_office = new CustomerCoa;
                                    $coa_customer_branch_office->customer_id = $customer->id;
                                    $coa_customer_branch_office->type = COA::TYPE['BRANCH_OFFICE'];
                                    $coa_customer_branch_office->branch_office_id = $request->branch_id[$key];
                                    $coa_customer_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_customer_branch_office->save();
                                } else {
                                    $coa_customer_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_customer_branch_office->save();
                                }

                                $coa_penjualan_customer_branch_office = CustomerCoaPenjualan::where('customer_id', $customer->id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $request->branch_id[$key])->first();
                                if($coa_penjualan_customer_branch_office == null) {
                                    $coa_penjualan_customer_branch_office = new CustomerCoaPenjualan;
                                    $coa_penjualan_customer_branch_office->customer_id = $customer->id;
                                    $coa_penjualan_customer_branch_office->type = COA::TYPE['BRANCH_OFFICE'];
                                    $coa_penjualan_customer_branch_office->branch_office_id = $request->branch_id[$key];
                                    $coa_penjualan_customer_branch_office->coa_id = $request->coa_penjualan_branch_id[$key];
                                    $coa_penjualan_customer_branch_office->save();
                                } else {
                                    $coa_penjualan_customer_branch_office->coa_id = $request->coa_penjualan_branch_id[$key];
                                    $coa_penjualan_customer_branch_office->save();
                                }
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('customer-show')) {
            return abort(403);
        }

        $data['customer'] = Customer::findOrFail($id);

        $data['coa_head_office'] = Coa::where('type', COA::TYPE['HEAD_OFFICE'])->get();
        if($select_id = CustomerCoa::where('customer_id', $id)->where('type', COA::TYPE['HEAD_OFFICE'])->first()) {
           $data['coa_head_office_selected'] = $select_id->coa_id;
        } else {
            $data['coa_head_office_selected'] = '';
        }

        if($select_id = CustomerCoaPenjualan::where('customer_id', $id)->where('type', COA::TYPE['HEAD_OFFICE'])->first()) {
            $data['coa_penjualan_head_office_selected'] = $select_id->coa_id;
         } else {
             $data['coa_penjualan_head_office_selected'] = '';
         }
        
        $data['branches'] = MasterRepo::branch_offices();
        foreach ($data['branches'] as $value) {
            $data['coa_branch'][$value->id] = Coa::where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->get();

            if($select_id = CustomerCoa::where('customer_id', $id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->first()) {
                $data['coa_branch_selected'][$value->id] = $select_id->coa_id;
            } else {
                $data['coa_branch_selected'][$value->id] = '';
            }

            if($select_id = CustomerCoaPenjualan::where('customer_id', $id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->first()) {
                $data['coa_penjualan_branch_selected'][$value->id] = $select_id->coa_id;
            } else {
                $data['coa_penjualan_branch_selected'][$value->id] = '';
            }
        }

        return view('superuser.master.customer.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('customer-edit')) {
            return abort(403);
        }

        $data['customer'] = Customer::findOrFail($id);
        $data['customer_categories'] = MasterRepo::customer_categories();
        $data['customer_types'] = MasterRepo::customer_types();

        $data['coa_head_office'] = Coa::where('type', COA::TYPE['HEAD_OFFICE'])->get();
        if($select_id = CustomerCoa::where('customer_id', $id)->where('type', COA::TYPE['HEAD_OFFICE'])->first()) {
           $data['coa_head_office_selected'] = $select_id->coa_id;
        } else {
            $data['coa_head_office_selected'] = '';
        }

        if($select_id = CustomerCoaPenjualan::where('customer_id', $id)->where('type', COA::TYPE['HEAD_OFFICE'])->first()) {
            $data['coa_penjualan_head_office_selected'] = $select_id->coa_id;
         } else {
             $data['coa_penjualan_head_office_selected'] = '';
         }
        
        $data['branches'] = MasterRepo::branch_offices();
        foreach ($data['branches'] as $value) {
            $data['coa_branch'][$value->id] = Coa::where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->get();

            if($select_id = CustomerCoa::where('customer_id', $id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->first()) {
                $data['coa_branch_selected'][$value->id] = $select_id->coa_id;
            } else {
                $data['coa_branch_selected'][$value->id] = '';
            }

            if($select_id = CustomerCoaPenjualan::where('customer_id', $id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $value->id)->first()) {
                $data['coa_penjualan_branch_selected'][$value->id] = $select_id->coa_id;
            } else {
                $data['coa_penjualan_branch_selected'][$value->id] = '';
            }
        }

        return view('superuser.master.customer.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $customer = Customer::find($id);

            if ($customer == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:master_customers,code,' . $customer->id,
                'name' => 'required|string',
                'store' => 'required|string',
                'category' => 'required|integer',
                'type' => 'required|integer',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'address' => 'required|string',
                'address_do' => Rule::requiredIf(function () use ($request) {
                    return '1' == CustomerType::where('id', $request->type)->first()->grosir_address;
                }),
                'owner_name' => 'nullable|string',
                'website' => 'nullable|string',
                'plafon_piutang' => 'nullable|numeric',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
                'image_store' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'notification_email' => 'nullable'
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
                $customer->code = $request->code;
                $customer->name = $request->name;
                $customer->store = $request->store;

                $customer->category_id = $request->category;
                $customer->type_id = $request->type;

                $customer->email = $request->email;
                $customer->phone = $request->phone;
                $customer->fax = $request->fax;
                $customer->address = $request->address;

                $customer->address_do = (CustomerType::where('id', $request->type)->first()->grosir_address == '1' ? $request->address_do : '');

                $customer->owner_name = $request->owner_name;
                $customer->website = $request->website;
                $customer->plafon_piutang = ($request->plafon_piutang) ? $request->plafon_piutang : 0;

                $customer->provinsi = $request->provinsi;
                $customer->kota = $request->kota;
                $customer->kecamatan = $request->kecamatan;
                $customer->kelurahan = $request->kelurahan;
                $customer->text_provinsi = $request->text_provinsi;
                $customer->text_kota = $request->text_kota;
                $customer->text_kecamatan = $request->text_kecamatan;
                $customer->text_kelurahan = $request->text_kelurahan;

                $customer->zipcode = $request->zipcode;

                if (!empty($request->file('image_store'))) {
                    if (is_file_exists(Customer::$directory_image.$customer->image_store)) {
                        remove_file(Customer::$directory_image.$customer->image_store);
                    }

                    $customer->image_store = UploadMedia::image($request->file('image_store'), Customer::$directory_image);
                }

                $customer->notification_email = ($request->notification_email) ? true : false;

                if ($customer->save()) {
                    $coa_customer_head_office = CustomerCoa::where('customer_id', $customer->id)->where('type', COA::TYPE['HEAD_OFFICE'])->first();
                    if($coa_customer_head_office == null) {
                        $coa_customer_head_office = new CustomerCoa;
                        $coa_customer_head_office->customer_id = $customer->id;
                        $coa_customer_head_office->type = COA::TYPE['HEAD_OFFICE'];
                        $coa_customer_head_office->coa_id = $request->coa_head_office_id;
                        $coa_customer_head_office->save();
                    } else {
                        $coa_customer_head_office->coa_id = $request->coa_head_office_id;
                        $coa_customer_head_office->save();
                    }

                    $coa_penjualan_customer_head_office = CustomerCoaPenjualan::where('customer_id', $customer->id)->where('type', COA::TYPE['HEAD_OFFICE'])->first();
                    if($coa_penjualan_customer_head_office == null) {
                        $coa_penjualan_customer_head_office = new CustomerCoaPenjualan;
                        $coa_penjualan_customer_head_office->customer_id = $customer->id;
                        $coa_penjualan_customer_head_office->type = COA::TYPE['HEAD_OFFICE'];
                        $coa_penjualan_customer_head_office->coa_id = $request->coa_penjualan_head_office_id;
                        $coa_penjualan_customer_head_office->save();
                    } else {
                        $coa_penjualan_customer_head_office->coa_id = $request->coa_penjualan_head_office_id;
                        $coa_penjualan_customer_head_office->save();
                    }

                    if($request->branch_id) {
                        foreach($request->branch_id as $key => $value){
                            if($request->branch_id[$key]) {
                                $coa_customer_branch_office = CustomerCoa::where('customer_id', $customer->id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $request->branch_id[$key])->first();
                                if($coa_customer_branch_office == null) {
                                    $coa_customer_branch_office = new CustomerCoa;
                                    $coa_customer_branch_office->customer_id = $customer->id;
                                    $coa_customer_branch_office->type = COA::TYPE['BRANCH_OFFICE'];
                                    $coa_customer_branch_office->branch_office_id = $request->branch_id[$key];
                                    $coa_customer_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_customer_branch_office->save();
                                } else {
                                    $coa_customer_branch_office->coa_id = $request->coa_branch_id[$key];
                                    $coa_customer_branch_office->save();
                                }

                                $coa_penjualan_customer_branch_office = CustomerCoaPenjualan::where('customer_id', $customer->id)->where('type', COA::TYPE['BRANCH_OFFICE'])->where('branch_office_id', $request->branch_id[$key])->first();
                                if($coa_penjualan_customer_branch_office == null) {
                                    $coa_penjualan_customer_branch_office = new CustomerCoaPenjualan;
                                    $coa_penjualan_customer_branch_office->customer_id = $customer->id;
                                    $coa_penjualan_customer_branch_office->type = COA::TYPE['BRANCH_OFFICE'];
                                    $coa_penjualan_customer_branch_office->branch_office_id = $request->branch_id[$key];
                                    $coa_penjualan_customer_branch_office->coa_id = $request->coa_penjualan_branch_id[$key];
                                    $coa_penjualan_customer_branch_office->save();
                                } else {
                                    $coa_penjualan_customer_branch_office->coa_id = $request->coa_penjualan_branch_id[$key];
                                    $coa_penjualan_customer_branch_office->save();
                                }
                            }
                        }
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('customer-delete')) {
                return abort(403);
            }

            $customer = Customer::find($id);

            if ($customer === null) {
                abort(404);
            }

            $customer->status = Customer::STATUS['DELETED'];

            if ($customer->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-customer-import-template.xlsx';
        return Excel::download(new CustomerImportTemplate, $filename);
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
            Excel::import(new CustomerImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-customer-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new CustomerExport, $filename);
    }
}
