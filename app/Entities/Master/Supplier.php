<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'address',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'email', 'phone', 'fax', 'owner_name', 'website',
        'description', 'status'
    ];
    protected $table = 'master_supplier';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

}
