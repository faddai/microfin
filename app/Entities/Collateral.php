<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Collateral extends Model
{
    protected $fillable = ['label', 'market_value', 'loan_id'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function value()
    {
        return $this->market_value;
    }
}
