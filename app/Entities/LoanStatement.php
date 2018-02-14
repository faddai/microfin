<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class LoanStatement extends Model
{
    protected $fillable = ['loan_id'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function entries()
    {
        return $this->hasMany(LoanStatementEntry::class);
    }
}
