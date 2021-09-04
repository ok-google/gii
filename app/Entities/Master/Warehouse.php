<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['type', 'branch_office_id', 'code', 'name', 'category', 'contact_person', 'phone', 'fax', 'address', 'description', 'status'];
    protected $table = 'master_warehouses';

    const TYPE = [
        'HEAD_OFFICE' => 1,
        'BRANCH_OFFICE' => 2
    ];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    const CATEGORY = [
        'GD UTAMA' => 1,
        'GD DISPLAY' => 2,
        'GD REPARASI' => 3
    ];

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function category()
    {
        return array_search($this->category, self::CATEGORY);
    }

    public function branch_office()
    {
        return $this->BelongsTo('App\Entities\Master\BranchOffice');
    }

}
