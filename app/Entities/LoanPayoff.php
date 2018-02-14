<?php

namespace App\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class LoanPayoff extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'interest',
        'principal',
        'fees',
        'penalty',
        'created_by',
        'approved_by',
        'declined_by',
        'remarks',
        'decline_reason',
    ];

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const DECLINED = 'declined';

    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function declinedBy()
    {
        return $this->belongsTo(User::class, 'declined_by');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function approve(Request $request)
    {
        $this->forceFill([
            'status' => self::APPROVED,
            'remarks' => $request->get('remarks'),
            'approved_by' => $request->user()->id,
            'approved_at' => Carbon::now()
        ])->save();


        return $this->loan->markAsPaidOff();
    }
}
