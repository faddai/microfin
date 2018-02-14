<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Tenure extends Model
{
    protected $fillable = ['number_of_months', 'label'];
}
