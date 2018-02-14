<?php

namespace App\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laratrust\Traits\LaratrustUserTrait;


class User extends Authenticatable
{
    use LaratrustUserTrait;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'branch_id', 'last_login', 'is_active'
    ];

    protected $dates = ['last_login'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password)
    {
        if (Hash::needsRehash($password)) {
            $this->attributes['password'] = bcrypt($password);
        } else {
            $this->attributes['password'] = $password;
        }
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function clientTransactions()
    {
        return $this->hasMany(ClientTransaction::class);
    }

    public function getFullName($uppercased = true)
    {
        return $uppercased ? strtoupper($this->name) : $this->name;
    }

    public static function relationshipManagers()
    {
        return self::whereHas('roles', function ($query) {
            $query->where('name', str_slug(Role::RELATIONSHIP_MANAGER));
        })->get();
    }

    public static function creditOfficers()
    {
        return self::whereHas('roles', function ($query) {
            $query->where('name', str_slug(Role::CREDIT_OFFICER));
        })->get(['id', 'name']);
    }

    public static function approvers()
    {
        return self::whereHas('roles.permissions', function ($query) {
            $query->where('name', Permission::APPROVE_LOAN);
        })->get();
    }

    public static function disbursers()
    {
        return self::whereHas('roles.permissions', function ($query) {
            $query->where('name', Permission::DISBURSE_LOAN);
        })->get();
    }

    public function isActive()
    {
        return (bool) $this->is_active;
    }
}
