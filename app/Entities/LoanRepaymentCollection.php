<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class LoanRepaymentCollection extends Model
{
    protected $fillable = ['loan_repayment_id', 'collected_at', 'amount'];

    public function repayment()
    {
        return $this->belongsTo(LoanRepayment::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class);
    }
}
