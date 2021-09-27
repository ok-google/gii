<?php

namespace App\Entities\Purchasing;

use App\Entities\Model;

class ReceivingDetail extends Model
{
    protected $fillable = [
        'receiving_id', 'ppb_id', 'ppb_detail_id', 'product_id', 'quantity', 'total_quantity_ri', 'total_quantity_colly', 'delivery_cost', 'description', 'total_ri_idr', 'grand_total'
    ];
    protected $table = 'receiving_detail';

    public static function boot() {
        parent::boot();

        static::deleting(function($receiving_detail) {
             $receiving_detail->collys()->delete();
        });
    }

    public function receiving()
    {
        return $this->belongsTo('App\Entities\Purchasing\Receiving');
    }

    public function purchase_order()
    {
        return $this->belongsTo('App\Entities\Purchasing\PurchaseOrder', 'ppb_id');
    }

    public function ppb_detail()
    {
        return $this->belongsTo('App\Entities\Purchasing\PurchaseOrderDetail', 'ppb_detail_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Entities\Master\Product');
    }

    public function collys()
    {
        return $this->hasMany('App\Entities\Purchasing\ReceivingDetailColly');
    }

    public function total_reject_ri($detail_id) {
        $total = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->where('is_reject', '1')->sum('quantity_ri');
        
        return $total ?? null;
    }

    public function total_reject_colly($detail_id) {
        $total = ReceivingDetailColly::where('receiving_detail_id', $detail_id)->where('is_reject', '1')->sum('quantity_colly');
        
        return $total ?? null;
    }
}
