<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{

    /**
     * @const STATUS_ACTIVE
     */
    const STATUS_ACTIVE = 1;

    /**
     * @const STATUS_INACTIVE
     */
    const STATUS_INACTIVE = 0;

    protected $fillable = ['name', 'code', 'location', 'status'];

    public function staff()
    {
        return $this->hasMany(User::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function getDisplayName()
    {
        $name = $this->name;

        if ($this->location) {
            $name .= " ({$this->location})";
        }

        return $name;
    }
}