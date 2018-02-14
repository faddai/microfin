<?php

namespace App\Entities;

use App\Entities\Accounting\Ledger;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    protected $fillable = ['name', 'description', 'min_loan_amount', 'max_loan_amount', 'code',
        // ledgers for receiving principal and interest
        'principal_ledger_id', 'interest_ledger_id', 'interest_income_ledger_id'
    ];

    const CUSTOMER = 1120;
    const STAFF = 1100;
    const GRZ = 1130;

    public function getDisplayName()
    {
        return sprintf('%s (%s)', $this->name, $this->code);
    }

    /**
     * The ledger for keeping track of principal repayments of loans belonging to this product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function principalLedger()
    {
        return $this->belongsTo(Ledger::class, 'principal_ledger_id');
    }

    /**
     * The ledger for keeping track of interest repayments of loans belonging to this product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function interestReceivableLedger()
    {
        return $this->belongsTo(Ledger::class, 'interest_ledger_id');
    }

    /**
     * The ledger for keeping track of actual interest repayments of loans belonging to this product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function interestIncomeLedger()
    {
        return $this->belongsTo(Ledger::class, 'interest_income_ledger_id');
    }
}