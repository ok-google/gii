<?php

namespace App\Entities\Account;

use App\Observers\ResponsibleUserObserver;
use Illuminate\Database\Eloquent\Model;

class SalesPerson extends Model
{
    protected $table = 'sales_persons';

    public static function boot() {
        parent::boot();

        static::observe(new ResponsibleUserObserver());
    }

    public function zones()
    {
        return $this->hasMany('App\Entities\Account\SalesPersonZone');
    }
}
