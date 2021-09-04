<?php

namespace App\Entities\Inventory;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MutationDetail extends Model
{
    protected $fillable = ['mutation_id', 'receiving_detail_colly_id'];
    protected $table = 'mutation_detail';

    public function receiving_detail_colly()
    {
        return $this->belongsTo('App\Entities\Purchasing\ReceivingDetailColly');
    }

    public function mutation()
    {
        return $this->belongsTo('App\Entities\Inventory\Mutation');
    }
}
