<?php

namespace App\Entities\Accounting;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coa extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'kode_pelunasan', 'type', 'branch_office_id', 'name', 'group', 'parent_level_1', 'parent_level_2', 'parent_level_2', 'description', 'status'];
    protected $table = 'master_coa';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    const TYPE = [
        'HEAD_OFFICE' => 1,
        'BRANCH_OFFICE' => 2
    ];

    const GROUP = [
        'Aktiva' => 1,
        'Liabilities' => 2,
        'Ekuitas' => 3,
        'Revenue' => 4,
        'Expenses' => 5,
        'HPP' => 6,
        'Other' => 7
    ];

    public function group()
    {
        return array_search($this->group, self::GROUP);
    }

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function branch_office()
    {
        return $this->BelongsTo('App\Entities\Master\BranchOffice');
    }

    public function parent_level_one()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'parent_level_1');
    }

    public function parent_level_two()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'parent_level_2');
    }

    public function parent_level_three()
    {
        return $this->BelongsTo('App\Entities\Accounting\Coa', 'parent_level_3');
    }
}
