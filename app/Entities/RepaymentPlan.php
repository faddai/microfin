<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class RepaymentPlan extends Model
{
    const WEEKLY = 'Weekly';
    const FORTNIGHTLY = 'Fortnightly';
    const MONTHLY = 'Monthly';

    protected $fillable = ['label', 'number_of_days', 'number_of_repayments_per_month'];
}
