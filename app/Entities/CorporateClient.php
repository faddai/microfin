<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class CorporateClient extends Model
{
    protected $fillable = ['company_name', 'date_of_incorporation', 'business_registration_number',
        'company_ownership_type', 'statement_frequency'];

    protected $dates = ['date_of_incorporation'];

    public function client()
    {
        return $this->morphOne(Client::class, 'clientable');
    }
}
