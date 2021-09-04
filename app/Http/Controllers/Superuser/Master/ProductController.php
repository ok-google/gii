<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\ProductTable;
use App\Entities\Master\Product;
use App\Entities\Master\ProductImage;
use App\Exports\Master\ProductExport;
use App\Exports\Master\ProductImportTemplate;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use App\Imports\Master\ProductImport;
use App\Repositories\MasterRepo;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ProductController extends Controller
{
    public function json(Request $request, ProductTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if(!Auth::guard('superuser')->user()->can('product-manage')) {
            return abort(403);
        }

        return view('superuser.master.product.index');
    }

    public function create()
    {
        if(!Auth::guard('superuser')->user()->can('product-create')) {
            return abort(403);
        }

        $data['brand_references'] = MasterRepo::brand_references();
        $data['sub_brand_references'] = MasterRepo::sub_brand_references();
        $data['product_categories'] = MasterRepo::product_categories();
        $data['product_types'] = MasterRepo::product_types();
        $data['units'] = MasterRepo::units();

        return view('superuser.master.product.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'brand_reference' => 'required|integer',
                'sub_brand_reference' => 'required|integer',
                'category' => 'required|integer',
                'type' => 'required|integer',

                'code' => 'required|string|unique:master_products,code',
                'name' => 'required|string',
                'description' => 'nullable|string',

                'quantity' => 'required|numeric',
                'unit' => 'required|integer',

                'images' => 'nullable|array',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

                'non_stock' => 'nullable|string',
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
                $product = new Product;

                $product->brand_reference_id = $request->brand_reference;
                $product->sub_brand_reference_id = $request->sub_brand_reference;
                $product->category_id = $request->category;
                $product->type_id = $request->type;

                $product->code = $request->code;
                $product->name = $request->name;
                $product->description = $request->description;

                $product->quantity = $request->quantity;
                $product->unit_id = $request->unit;

                $product->non_stock = '0';
                if($request->has('non_stock')) {
                    $product->non_stock = '1';
                }
                
                $images = [];

                if($request->file('images')) {
                    foreach($request->file('images') as $image) {
                        $img = ['image' => UploadMedia::image($image, ProductImage::$directory_image)];
                        array_push($images, $img);
                    }
                }
                
                $product->status = Product::STATUS['ACTIVE'];

                if ($product->save()) {
                    if($images) {
                        $product->images()->createMany($images);
                    }
                    
                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        if(!Auth::guard('superuser')->user()->can('product-show')) {
            return abort(403);
        }

        $data['product'] = Product::findOrFail($id);

        return view('superuser.master.product.show', $data);
    }

    public function edit($id)
    {
        if(!Auth::guard('superuser')->user()->can('product-edit')) {
            return abort(403);
        }

        $data['product'] = Product::findOrFail($id);

        $data['brand_references'] = MasterRepo::brand_references();
        $data['sub_brand_references'] = MasterRepo::sub_brand_references();
        $data['product_categories'] = MasterRepo::product_categories();
        $data['product_types'] = MasterRepo::product_types();
        $data['units'] = MasterRepo::units();

        $data['product_image_trash'] = ProductImage::where('product_id', $id)->onlyTrashed()->get();
        
        return view('superuser.master.product.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $product = Product::find($id);

            if ($product == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'brand_reference' => 'required|integer',
                'sub_brand_reference' => 'required|integer',
                'category' => 'required|integer',
                'type' => 'required|integer',

                'code' => 'required|string|unique:master_products,code,' . $product->id,
                'name' => 'required|string',
                'description' => 'nullable|string',

                'quantity' => 'required|numeric',
                'unit' => 'required|integer',

                'images'   => 'nullable|array',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

                'non_stock' => 'nullable|string',
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
                $product->brand_reference_id = $request->brand_reference;
                $product->sub_brand_reference_id = $request->sub_brand_reference;
                $product->category_id = $request->category;
                $product->type_id = $request->type;

                $product->code = $request->code;
                $product->name = $request->name;
                $product->description = $request->description;

                $product->quantity = $request->quantity;
                $product->unit_id = $request->unit;

                $product->non_stock = '0';
                if($request->has('non_stock')) {
                    $product->non_stock = '1';
                }
                
                if (!empty($request->file('images'))) {
                    $images = [];

                    foreach($request->file('images') as $image) {
                      $img = ['image' => UploadMedia::image($image, ProductImage::$directory_image)];
                      array_push($images, $img);
                    }
                }

                if ($product->save()) {
                    if (isset($images)) {
                        $product->images()->createMany($images);
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.product.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(!Auth::guard('superuser')->user()->can('product-delete')) {
                return abort(403);
            }

            $product = Product::find($id);

            if ($product === null) {
                abort(404);
            }

            $product->status = Product::STATUS['DELETED'];

            if ($product->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-product-import-template.xlsx';
        return Excel::download(new ProductImportTemplate, $filename);
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
            Excel::import(new ProductImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'master-product-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new ProductExport, $filename);
    }
}
