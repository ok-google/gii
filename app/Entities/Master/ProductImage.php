<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use SoftDeletes;

    protected $appends = ['image_url'];
    protected $fillable = ['image'];
    protected $table = 'product_images';
    public static $directory_image = 'superuser_assets/media/master/product/';

    public function getImageUrlAttribute()
    {
        if (!$this->image OR !file_exists(Self::$directory_image.$this->image)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image);
    }
}
