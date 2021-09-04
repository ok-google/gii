<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $appends = ['images_url'];
    protected $fillable = [
                        'brand_reference_id', 'sub_brand_reference_id', 'category_id', 'type_id',
                        'code', 'name', 'description', 'quantity', 'unit_id', 'non_stock', 'status'
                    ];

    protected $table = 'master_products';
    public static $directory_image = 'superuser_assets/media/master/product/';
    
    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function($product) {
             $product->images()->delete();
        });
    }

    public function images()
    {
        return $this->hasMany('App\Entities\Master\ProductImage');
    }

    public function brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\BrandReference');
    }

    public function sub_brand_reference()
    {
        return $this->BelongsTo('App\Entities\Master\SubBrandReference');
    }

    public function category()
    {
        return $this->BelongsTo('App\Entities\Master\ProductCategory');
    }

    public function type()
    {
        return $this->BelongsTo('App\Entities\Master\ProductType');
    }

    public function unit()
    {
        return $this->belongsTo('App\Entities\Master\Unit');
    }

    public function min_stocks()
    {
        return $this->hasMany('App\Entities\Master\ProductMinStock');
    }

    // public function getImageUrlAttribute()
    // {
    //     if (!$this->images OR !file_exists(Self::$directory_image.$this->images)) {
    //       return img_holder();
    //     }

    //     return asset(Self::$directory_image.$this->images);
    // }

    // public function getDefaultQuantityAttribute($value)
    // {
    //     return floatval($value);
    // }

    public function getImagesUrlAttribute()
    {
        if ($this->images()->exists()) {
            return $this->images->pluck('image_url');
        }

        return img_holder();
    }
}
