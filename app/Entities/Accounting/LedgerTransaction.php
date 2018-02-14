<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 02/02/2017
 * Time: 12:20 AM
 */

namespace App\Entities\Accounting;

use App\Entities\Branch;
use App\Entities\User;
use Illuminate\Database\Eloquent\Model;


class LedgerTransaction extends Model
{
    protected $fillable = ['uuid', 'branch_id', 'user_id', 'value_date', 'loan_id'];

    protected $dates = ['value_date'];

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries()
    {
        return $this->hasMany(LedgerEntry::class, 'ledger_transaction_id', 'uuid');
    }

}