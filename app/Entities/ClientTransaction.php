<?php

namespace App\Entities;

use App\Entities\Accounting\Ledger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class ClientTransaction extends Model
{
    protected $fillable = ['uuid', 'dr', 'cr', 'narration', 'client_id', 'branch_id', 'user_id', 'receipt', 'ledger_id',
        'value_date', 'created_at'
    ];

    protected $dates = ['value_date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * @return bool
     */
    public function isDeposit()
    {
        return $this->cr > 0;
    }

    /**
     * @return bool
     */
    public function isWithdrawal()
    {
        return $this->dr > 0;
    }

    /**
     * @param bool $format
     * @return mixed
     */
    public function getAmount($format = true)
    {
        $amount = $this->isDeposit() ? $this->cr : $this->dr;

        return $format ? number_format($amount, 2) : $amount;
    }

    /**
     * @return bool
     */
    public function isNominal()
    {
        return null === $this->ledger_id;
    }

    /**
     * @param $date
     */
    public function setValueDateAttribute($date)
    {
        $this->attributes['value_date'] = Carbon::parse(trim($date));

    }
}
