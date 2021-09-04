<?php

namespace App\Entities\Inventory;

use App\Entities\Model;

class ReconditionDetail extends Model
{
    protected $fillable = ['recondition_id', 'receiving_detail_colly_id', 'quality_control2_id', 'sale_return_detail_id', 'recondition_residual_id', 'product_id', 'quantity_recondition', 'quantity_disposal', 'description'];
    protected $table = 'recondition_detail';

    public function receiving_detail_colly()
    {
        return $this->belongsTo('App\Entities\Purchasing\ReceivingDetailColly');
    }

    public function quality_control2_detail()
    {
        return $this->belongsTo('App\Entities\QualityControl\QualityControl2','quality_control2_id');
    }

    public function sale_return_detail()
    {
        return $this->belongsTo('App\Entities\Sale\SaleReturnDetail');
    }

    public function residual_detail()
    {
        return $this->belongsTo('App\Entities\Inventory\ReconditionResidual', 'recondition_residual_id');
    }
}
