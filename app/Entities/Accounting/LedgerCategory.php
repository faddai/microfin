<?php

namespace App\Entities\Accounting;

use Illuminate\Database\Eloquent\Model;


class LedgerCategory extends Model
{
    protected $fillable = ['name', 'type'];

    const ASSET = 'asset';
    const CAPITAL = 'capital';
    const EXPENSE = 'expense';
    const INCOME = 'income';
    const LIABILITY = 'liab';

    const TYPES = [self::ASSET, self::CAPITAL, self::EXPENSE, self::INCOME, self::LIABILITY];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ledgers()
    {
        return $this->hasMany(Ledger::class, 'category_id');
    }

    /**
     * @return mixed
     */
    public static function scopeGetBankOrCashLedgers()
    {
        return self::with('ledgers')
            ->whereName('Other Current Assets')
            ->first()
            ->ledgers()
            ->where('is_bank_or_cash', 1)
            ->get();
    }

    /**
     * @return mixed
     */
    public static function scopeCustomerControlAssetsLedgers()
    {
        return self::with('ledgers.category')
            ->whereName('Customer Control-Assets')
            ->first()
            ->ledgers;
    }

    /**
     * @return mixed
     */
    public static function scopeIncomeLedgers()
    {
        return self::with('ledgers.category')
            ->whereName('Income')
            ->first()
            ->ledgers;
    }

    /**
     * Guess what type this category is. This is useful to the seeder.
     * It is required to be supplied from the UI
     *
     * @param $category
     * @return mixed|string
     */
    public static function getCategoryType($category)
    {
        foreach (self::TYPES as $type) {
            if (strpos(strtolower($category), strtolower($type)) !== false) {
                return $type;
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    public function hasDebitBalance()
    {
        return in_array($this->type, [self::ASSET, self::EXPENSE], true);
    }

    /**
     * @return bool
     */
    public function hasCreditBalance()
    {
        return in_array($this->type, [self::LIABILITY, self::CAPITAL, self::INCOME], true);
    }
}