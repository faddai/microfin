<?php

namespace App\Entities\Accounting;

use App\Entities\Branch;
use App\Entities\User;
use Illuminate\Database\Eloquent\Model;

class UnapprovedLedgerTransaction extends Model
{
    protected $fillable = ['user_id', 'branch_id', 'entries', 'value_date'];

    protected $casts = [
        'entries' => 'collection'
    ];

    protected $dates = ['value_date'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
