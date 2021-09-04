<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    
    protected $appends = ['img_store'];
    protected $fillable = [
        'category_id', 'type_id', 'code', 'name',
        'email', 'phone', 'address',
        'owner_name', 'plafon_piutang',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'image_store', 'notification_email', 'status'
    ];
    protected $table = 'master_customers';
    public static $directory_image = 'superuser_assets/media/master/customer/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    public function category()
    {
        return $this->BelongsTo('App\Entities\Master\CustomerCategory');
    }

    public function type()
    {
        return $this->BelongsTo('App\Entities\Master\CustomerType');
    }

    public function other_addresses()
    {
        return $this->hasMany('App\Entities\Master\CustomerOtherAddress');
    }

    public function getImgStoreAttribute()
    {
        if (!$this->image_store OR !file_exists(Self::$directory_image.$this->image_store)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_store);
    }

    // public function getImgKtpAttribute()
    // {
    //     if (!$this->image_ktp OR !file_exists(Self::$directory_image.$this->image_ktp)) {
    //       return img_holder();
    //     }

    //     return asset(Self::$directory_image.$this->image_ktp);
    // }
}
