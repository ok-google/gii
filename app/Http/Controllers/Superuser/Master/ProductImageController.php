<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Product;
use App\Entities\Master\ProductImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function delete(Request $request, $id)
    {
        if ($request->ajax()) {
            $product_image = ProductImage::find($id);

            if ($product_image === null) {
                abort(404);
            }

            if ($product_image->delete()) {
                return $this->response(200);
            }
        }
    }

    public function restore($parent_id, $id)
    {
        $product = Product::findOrFail($parent_id);

        $product_image = ProductImage::where('product_id', $parent_id)->where('id', $id)->onlyTrashed()->firstOrFail();

        $product_image->restore();

        return redirect()->route('superuser.product.edit', $parent_id);
    }

    public function restore_all($parent_id)
    {
        $product = Product::findOrFail($parent_id);
        $product->images()->restore();

        return redirect()->route('superuser.product.edit', $parent_id);
    }

    public function destroy($parent_id, $id)
    {
        $product = Product::findOrFail($parent_id);

        $product_image = ProductImage::where('product_id', $parent_id)->where('id', $id)->onlyTrashed()->firstOrFail();

        if (is_file_exists(ProductImage::$directory_image.$product_image->image)) {
            remove_file(ProductImage::$directory_image.$product_image->image);
        }

        $product_image->forceDelete();

        return redirect()->route('superuser.product.edit', $parent_id);
    }

    public function destroy_all($parent_id)
    {
        $product = Product::findOrFail($parent_id);

        $images = $product->images()->onlyTrashed()->get()->pluck('image');

        foreach ($images as $img) {
            if (is_file_exists(ProductImage::$directory_image.$img)) {
                remove_file(ProductImage::$directory_image.$img);
            }
        }

        $product->images()->onlyTrashed()->forceDelete();

        return redirect()->route('superuser.product.edit', $parent_id);
    }
}
