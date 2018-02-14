<?php

namespace App\Entities\Accounting;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = ['ledger_transaction_id', 'ledger_id', 'narration', 'cr', 'dr', 'created_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id', 'uuid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Get the balance on the ledger as at the date selected
     * @todo WIP; finish implementation and test
     * @param Carbon $fromDate
     * @param Carbon|null $toDate
     * @return int|mixed
     */
    public function setBalanceAttribute(Carbon $fromDate = null, Carbon $toDate = null)
    {
        $fromDate = $fromDate ?? Carbon::now()->startOfYear();
        $toDate = $toDate ?? Carbon::now();

        $entries = self::whereBetween('created_at', [$fromDate, $toDate])->get();

        $balance =  $entries->sum('cr') - $entries->sum('dr'); // credit balance

        if ($this->ledger->isDebitAccount()) {
            $balance = $entries->sum('dr') - $entries->sum('cr');
        }

        $this->attributes['balance'] = $balance;
    }

    /**
     * @return bool
     */
    public function isDebit()
    {
        return $this->dr > 0;
    }

    /**
     * @return bool
     */
    public function isCredit()
    {
        return $this->cr > 0;
    }
}
