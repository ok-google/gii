<?php

namespace App\Entities\Purchasing;

use App\Entities\Model;

class ReceivingDetailColly extends Model
{
    protected $fillable = [
        'code', 'receiving_detail_id', 'quantity_ri', 'quantity_colly', 'is_reject', 
        'status_qc', 'quantity_mutation', 'quantity_recondition',
        'status_mutation', 'status_recondition', 'warehouse_reparation_id', 'description', 'date_recondition'
    ];
    protected $table = 'receiving_detail_colly';

    const STATUS_QC = [
        'USED' => 1,
        'NOTUSED' => 0
    ];

    const STATUS_MUTATION = [
        'USED' => 1,
        'NOTUSED' => 0
    ];

    public function status_qc()
    {
        return array_search($this->status_qc, self::STATUS_QC);
    }

    public function status_mutation()
    {
        return array_search($this->status_mutation, self::STATUS_MUTATION);
    }

    public function receiving_detail()
    {
        return $this->belongsTo('App\Entities\Purchasing\ReceivingDetail');
    }
    
}
