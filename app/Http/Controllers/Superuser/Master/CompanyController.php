<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Company;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class CompanyController extends Controller
{
    public function show()
    {
        $data['company'] = Company::find(1);
        return view('superuser.master.company.show', $data);
    }

    public function edit()
    {
        $data['company'] = Company::find(1);
        return view('superuser.master.company.edit', $data);
    }

    public function update(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'address' => 'required|string',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
                'email' => 'nullable|string',
                'phone' => 'nullable|string',
                'fax' => 'nullable|string',
                'website' => 'nullable|string',
                'owner_name' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
                $company = Company::find(1);

                if ($company === null) {
                    $company = new Company;
                }
                
                $company->name = $request->name;
                $company->address = $request->address;
                $company->provinsi = $request->provinsi;
                $company->kota = $request->kota;
                $company->kecamatan = $request->kecamatan;
                $company->kelurahan = $request->kelurahan;
                $company->text_provinsi = $request->text_provinsi;
                $company->text_kota = $request->text_kota;
                $company->text_kecamatan = $request->text_kecamatan;
                $company->text_kelurahan = $request->text_kelurahan;
                $company->zipcode = $request->zipcode;
                $company->email = $request->email;
                $company->phone = $request->phone;
                $company->fax = $request->fax;
                $company->website = $request->website;
                $company->owner_name = $request->owner_name;

                if (!empty($request->file('logo'))) {
                    if (is_file_exists(Company::$directory_image.$company->logo)) {
                        remove_file(Company::$directory_image.$company->logo);
                    }

                    $company->logo = UploadMedia::image($request->file('logo'), Company::$directory_image);
                }

                if ($company->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.company.show');

                    return $this->response(200, $response);
                }
            }
        }
    }
}
