<?php

namespace App\Entities\Finance;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CBReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'type', 'branch_office_id', 'status', 'note_receipt', 'select_date'];
    protected $table = 'cb_receipt';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'ACC' => 2
    ];

    const TYPE = [
        'HEAD_OFFICE' => 1,
        'BRANCH_OFFICE' => 2
    ];

    public function type()
    {
        return array_search($this->type, self::TYPE);
    }

    public function branch_office()
    {
        return $this->BelongsTo('App\Entities\Master\BranchOffice');
    }

    public function details()
    {
        return $this->hasMany('App\Entities\Finance\CBReceiptDetail', 'cb_receipt_id');
    }
}
