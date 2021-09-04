<?php

namespace App\Entities\Account;

use App\Observers\ResponsibleUserObserver;
use DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class Superuser extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $table               = 'superusers';
    protected $guard_name          = 'superuser';
    protected $appends             = ['img'];
    public static $directory_image = 'superuser_assets/media/profiles/';

    public static function boot() {
        parent::boot();

        static::observe(new ResponsibleUserObserver());
    }

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

    public function canAny(array $permissions)
    {
        foreach($permissions as $permission) {
            if (! $this->can($permission) ) {
                return false;
            }
        }
        
        return true;
    }

    public function getImgAttribute()
    {
        if (!$this->image OR !file_exists(self::$directory_image.$this->image)) {
            return img_holder('avatar');
        }

        return asset(self::$directory_image.$this->image);
    }

    public function dontHaveRoles()
    {
        $roles = DB::table('roles')->whereNotIn('id', $this->roles()->pluck('id'));

        if (!Auth::guard('superuser')->user()->hasRole('Developer')) {
            $roles->where('name', '!=', 'Developer')
                  ->where('name', '!=', 'SuperAdmin');
        }

        return $roles->get();
    }

    public function createdBySuperuser()
    {
        $superuser = static::find($this->created_by);

        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
	
    public function updatedBySuperuser()
    {
        $superuser = static::find($this->updated_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
	
    public function deletedBySuperuser()
    {
        $superuser = static::find($this->deleted_by);
        
        if ($superuser) {
            return $superuser->name ?? $superuser->username;
        }
	}
}
