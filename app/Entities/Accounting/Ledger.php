<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;


class Ledger extends Model
{
    protected $fillable = ['category_id', 'name', 'code', 'is_bank_or_cash', 'is_left', 'is_right'];

    // @todo add ability to configure this ledger
    // for now, going to hard code a ledger from the short term liability category
    const CURRENT_ACCOUNT_CODE = 3001;

    protected $casts = [
        'is_left' => 'boolean',
        'is_right' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(LedgerCategory::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return sprintf('%d - %s || %s', $this->code, $this->name, $this->category->name);
    }

    /**
     * Do credit/debit calculation and arrive at a number that will be used as the
     * closing balance on the account/ledger
     *
     * @param bool $format
     * @return float|string
     */
    public function getClosingBalance($format = true)
    {
        $balance = 0.0;

        if ($this->isDebitAccount()) {
            $balance = $this->entries->sum('dr') - $this->entries->sum('cr');
        } elseif ($this->isCreditAccount()) {
            $balance = $this->entries->sum('cr') - $this->entries->sum('dr');
        }

        return $format ? number_format($balance, 2) : $balance;
    }

    /**
     * Do credit/debit calculation and arrive at a number that will be used as the
     * closing balance on the account/ledger
     *
     * @return string
     */
    public function getClosingBalanceDetailed()
    {
        $str = '';

        if ($this->isDebitAccount()) {
            $balance = $this->entries->sum('dr') - $this->entries->sum('cr');

            $str = sprintf('<td>%s</td><td>-</td>', number_format(abs($balance), 2));

            if ($balance < 0) {
                $str = sprintf('<td>-</td><td>%s</td>', number_format(abs($balance), 2));
            }
        } elseif ($this->isCreditAccount()) {
            $balance = $this->entries->sum('cr') - $this->entries->sum('dr');

            $str = sprintf('<td>-</td><td>%s</td>', number_format(abs($balance), 2));

            if ($balance < 0) {
                $str = sprintf('<td>%s</td><td>-</td>', number_format(abs($balance), 2));
            }
        }

        return $str;
    }

    /**
     * @return bool
     */
    public function isCreditAccount()
    {
        return $this->is_right;
    }

    /**
     * @return bool
     */
    public function isDebitAccount()
    {
        return $this->is_left;
    }
}