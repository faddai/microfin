<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Guarantor extends Model
{
    protected $fillable = [
        'name', 'work_phone', 'personal_phone', 'employer', 'job_title', 'years_known', 'email',
        'residential_address', 'loan_id', 'relationship'
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
