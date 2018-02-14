<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 15:04
 */

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

class IndividualClient extends Model
{
    protected $fillable = [
        'firstname', 'lastname', 'middlename', 'dob', 'gender', 'marital_status', 'spouse_name'
    ];

    protected $dates = ['dob'];

    public function getMorphClass()
    {
        return 'MorphIndividual';
    }

    public function client()
    {
        return $this->morphOne(Client::class, 'clientable');
    }
}