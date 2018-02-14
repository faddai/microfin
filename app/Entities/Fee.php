<?php

namespace App\Entities;

use App\Entities\Accounting\Ledger;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = ['name', 'rate', 'is_paid_upfront', 'receivable_ledger_id', 'income_ledger_id', 'type'];

    protected $casts = [
        'is_paid_upfront' => 'boolean',
    ];

    const FIXED = 'fixed';
    const PERCENTAGE = 'percentage';

    const ADMINISTRATION = 'Administration fee';
    const ARRANGEMENT = 'Arrangement fee';
    const PROCESSING = 'Processing fee';
    const DISBURSEMENT = 'Disbursement fee';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function loans()
    {
        return $this->belongsToMany(Loan::class, 'loan_fees')->withPivot('amount', 'rate', 'is_paid_upfront', 'type');
    }

    /**
     * @return mixed
     */
    public function isPaidUpfront()
    {
        return $this->is_paid_upfront;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receivableLedger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function incomeLedger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * @return bool
     */
    public function isFixed()
    {
        return $this->type === self::FIXED;
    }

    /**
     * @return bool
     */
    public function isPercentage()
    {
        return $this->type === self::PERCENTAGE;
    }
}