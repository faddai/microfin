<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 11:19
 */

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'alpha_2_code', 'alpha_3_code', 'nationality'];

}