<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class CustomerOtherAddressController extends Controller
{
    public function create($id)
    {
        $data['customer'] = Customer::findOrFail($id);

        return view('superuser.master.customer_other_address.create', $data);
    }

    public function store(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'label' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'required|string',
                'gps_latitude' => 'nullable|string',
                'gps_longitude' => 'nullable|string',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
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
                $customer = Customer::find($id);

                if ($customer == null) {
                    abort(404);
                }

                $other_address = new CustomerOtherAddress;

                $other_address->customer_id = $customer->id;

                $other_address->label = $request->label;
                $other_address->contact_person = $request->contact_person;
                $other_address->phone = $request->phone;
                $other_address->address = $request->address;

                $other_address->gps_latitude = $request->gps_latitude;
                $other_address->gps_longitude = $request->gps_longitude;

                $other_address->provinsi = $request->provinsi;
                $other_address->kota = $request->kota;
                $other_address->kecamatan = $request->kecamatan;
                $other_address->kelurahan = $request->kelurahan;
                $other_address->text_provinsi = $request->text_provinsi;
                $other_address->text_kota = $request->text_kota;
                $other_address->text_kecamatan = $request->text_kecamatan;
                $other_address->text_kelurahan = $request->text_kelurahan;

                $other_address->zipcode = $request->zipcode;

                $other_address->status = CustomerOtherAddress::STATUS['ACTIVE'];
                
                if ($other_address->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer.show', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function edit($id, $address_id)
    {
        $data['customer'] = Customer::findOrFail($id);
        $data['other_address'] = CustomerOtherAddress::findOrFail($address_id);

        return view('superuser.master.customer_other_address.edit', $data);
    }
    
    public function update(Request $request, $id, $address_id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'label' => 'required|string',
                'contact_person' => 'nullable|string',
                'phone' => 'nullable|string',
                'address' => 'required|string',
                'gps_latitude' => 'nullable|string',
                'gps_longitude' => 'nullable|string',
                'provinsi' => 'nullable|string',
                'kota' => 'nullable|string',
                'kecamatan' => 'nullable|string',
                'kelurahan' => 'nullable|string',
                'text_provinsi' => 'nullable|required_with:provinsi|string',
                'text_kota' => 'nullable|required_with:kota|string',
                'text_kecamatan' => 'nullable|required_with:kecamatan|string',
                'text_kelurahan' => 'nullable|required_with:kelurahan|string',
                'zipcode' => 'nullable|string',
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
                $customer = Customer::find($id);
                $other_address = CustomerOtherAddress::find($address_id);

                if ($customer == null OR $other_address == null) {
                    abort(404);
                }

                $other_address->label = $request->label;
                $other_address->contact_person = $request->contact_person;
                $other_address->phone = $request->phone;
                $other_address->address = $request->address;

                $other_address->gps_latitude = $request->gps_latitude;
                $other_address->gps_longitude = $request->gps_longitude;

                $other_address->provinsi = $request->provinsi;
                $other_address->kota = $request->kota;
                $other_address->kecamatan = $request->kecamatan;
                $other_address->kelurahan = $request->kelurahan;
                $other_address->text_provinsi = $request->text_provinsi;
                $other_address->text_kota = $request->text_kota;
                $other_address->text_kecamatan = $request->text_kecamatan;
                $other_address->text_kelurahan = $request->text_kelurahan;

                $other_address->zipcode = $request->zipcode;

                if ($other_address->save()) {
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.customer.show', $id);

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id, $address_id)
    {
        if ($request->ajax()) {
            $customer = Customer::find($id);
            $other_address = CustomerOtherAddress::find($address_id);

            if ($customer === null OR $other_address === null) {
                abort(404);
            }

            $other_address->status = CustomerOtherAddress::STATUS['DELETED'];

            if ($other_address->save()) {
                $response['redirect_to'] = 'reload()';
                return $this->response(200, $response);
            }
        }
    }
}
