<?php

namespace App\Entities;

use App\Exceptions\InsufficientAccountBalanceException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


class LoanRepayment extends Model
{
    const DEFAULTED = 'Defaulted';
    const PART_PAYMENT = 'Part payment';
    const FULL_PAYMENT = 'Paid';

    protected $fillable = [
        'loan_id', 'user_id', 'amount', 'payment_method', 'has_been_paid', 'repayment_timestamp',
        'due_date', 'principal', 'interest', 'status', 'paid_principal', 'paid_interest', 'fees', 'paid_fees',
    ];

    protected $dates = ['repayment_timestamp', 'due_date'];

    protected $casts = [
        'has_been_paid' => 'boolean',
        'principal' => 'float',
        'paid_principal' => 'float',
        'interest' => 'float',
        'paid_interest' => 'float',
        'amount' => 'float',
        'fees' => 'float',
        'paid_fees' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        // get actual payments
        static::addGlobalScope('paid', function (Builder $builder) {
            return  $builder->where('has_been_paid', 1)
                ->where('paid_interest', '<>', 0)
                ->where('paid_principal', '<>', 0)
                ->whereNotNull('repayment_timestamp');
        });
    }

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
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function collections()
    {
        return $this->hasMany(LoanRepaymentCollection::class);
    }

    /**
     * @param Loan $loan
     * @param array $dueDateRange
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function schedule($loan = null, $dueDateRange = [])
    {
        return self::withoutGlobalScope('paid')
            ->when($dueDateRange, function (Builder $query) use ($dueDateRange) {
                return $query->whereBetween('due_date', $dueDateRange);
            })
            ->when($loan->id ?? null, function (Builder $query) use ($loan) {
                return $query->where('loan_id', $loan->id);
            })
            ->get();
    }

    /**
     * Interest on repayment
     *
     * @param bool|true $format
     * @return mixed|string
     */
    public function getInterest($format = true)
    {
        return $format ? number_format($this->interest, 2) : $this->interest;
    }

    /**
     * Principal amount on repayment
     *
     * @param bool|true $format
     * @return mixed|string
     */
    public function getPrincipal($format = true)
    {
        return $format ? number_format($this->principal, 2) : $this->principal;
    }

    /**
     * Fees component on repayment
     *
     * @param bool|true $format
     * @return mixed|string
     */
    public function getFees($format = true)
    {
        return $format ? number_format($this->fees, 2) : $this->fees;
    }

    /**
     * Repayment amount
     * @param bool|true $format
     * @return mixed|string
     */
    public function getAmount($format = true)
    {
        return $format ? number_format($this->amount, 2) : $this->amount;
    }

    /**
     * Get all repayments that are due and unpaid on a specific date (default is today)
     * @param $query
     * @param null $date
     * @return mixed
     */
    public function scopeGetLoanRepaymentsDue($query, $date = null)
    {
        return $query->withoutGlobalScope('paid')
            ->with(['loan','loan.client'])
            ->whereDueDate($date ?? Carbon::today())
            ->where('has_been_paid', false);
    }

    /**
     * @param $query
     * @param Client $client
     * @return mixed
     */
    public function scopeGetDueRepaymentsForAClient($query, Client $client)
    {
        return $query
            ->unpaid()
            ->due()
            ->whereHas('loan', function ($q) {
                return $q->active();
            })
            ->whereHas('loan.client', function ($q) use ($client) {
                return $q->whereClientId($client->id);
            })
            ->get();
    }

    /**
     * @param Builder $query
     * @param Carbon $dueDate
     * @return mixed
     */
    public function scopeDue(Builder $query, Carbon $dueDate = null)
    {
        return $query->whereDate('due_date', '<=', $dueDate ?? Carbon::today());
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeUnpaid(Builder $query)
    {
        return $query->withoutGlobalScope('paid')->whereHasBeenPaid(false);
    }

    /**
     * @param $query
     * @param Request $request
     * @return mixed
     */
    public function scopePar(Builder $query, Request $request)
    {
        $numberOfDays = $request->get('no_of_days', 120);

        return $query->with('loan.client.clientable', 'loan.creditOfficer', 'loan.type')
            ->unpaid()
            ->whereRaw('DATEDIFF(?, `due_date`) > ?', [Carbon::today(), $numberOfDays])
            ->when($request->get('credit_officer'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.creditOfficer', function (Builder $query) use ($request) {
                    $query->where('id', $request->get('credit_officer'));
                });
            })
            ->when($request->get('product_id'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.product', function (Builder $query) use ($request) {
                    $query->where('id', $request->get('product_id'));
                });
            })
            ->when($request->get('loan_type'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.type', function (Builder $query) use ($request) {
                    $query->where('id', $request->get('loan_type'));
                });
            })
            ->when($request->get('date'), function (Builder $query) use ($request) {
                return $query->whereHas('loan', function (Builder $query) use ($request) {
                    $query->where('disbursed_at', '<=', $request->get('date'));
                });
            })
            ->get()
            ->groupBy('loan_id');
    }

    /**
     * @param $query
     * @param Request $request
     * @return mixed
     */
    public function scopeAgeing(Builder $query, Request $request)
    {
        return $query->with('loan.client.clientable', 'loan.creditOfficer', 'loan.type')
            ->unpaid()
            ->when($request->get('credit_officer'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.creditOfficer', function (Builder $query) use ($request) {
                    $query->where('id', $request->get('credit_officer'));
                });
            })
            ->when($request->get('product_id'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.product', function (Builder $query) use ($request) {
                    $query->where('id', $request->get('product_id'));
                });
            })
            ->when($request->get('loan_type'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.type', function (Builder $query) use ($request) {
                    $query->where('id', $request->get('loan_type'));
                });
            })
            ->when($request->get('date'), function (Builder $query) use ($request) {
                return $query->where('due_date', '<=', $request->get('date'));
            })
            ->get()
            ->groupBy('loan_id');
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return mixed
     */
    public function scopeMonthlyCollectionProjections(Builder $query, Request $request)
    {
        return $query->unpaid()
            ->with('loan.client.clientable', 'loan.schedule')
            ->whereBetween('due_date', [$request->get('startDate'), $request->get('endDate')])
            ->when($request->get('credit_officer'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.creditOfficer', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('credit_officer'));
                });
            })
            ->when($request->get('loan_type'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.type', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('loan_type'));
                });
            })
            ->when($request->get('product_id'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.product', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('product_id'));
                });
            })
            ->when($request->get('business_unit'), function (Builder $query) use ($request) {
                return $query->whereHas('loan.createdBy.branch', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('business_unit'));
                });
            })
            ->get()
            ->groupBy('loan_id');
    }

    /**
     * Deduct scheduled repayment amount
     *
     * @return bool
     * @throws \App\Exceptions\InsufficientAccountBalanceException
     */
    public function deductFromClientAccountBalance()
    {
        $client = $this->loan->client;

        // Client has no balance, there is no need to continue
        if (! $client->isDeductable()) {
            return $this->markAsDefaulted();
        }

        // client has enough balance for repayment
        if (!$this->isPartlyPaid() && $client->fresh()->isDeductable($this->amount)) {
            return $this->deductAmountFromClientAccountBalance();
        }

        /**
         * Client has some funds but it wasn't enough to pay the full repayment  amount, so, first we'll try and pay
         * the interest component then proceed to pay the principal component
         */
        $this->deductInterestFromClientAccountBalance();

        // For repayments with fees, do fees deduction if fees haven't been deducted already
        $this->hasFees() && $this->fees !== $this->paid_fees && $this->deductFeesFromClientAccountBalance();
    }

    /**
     * Deduct full repayment amount from Client account balance
     *
     * @return bool
     * @throws \App\Exceptions\InsufficientAccountBalanceException
     */
    private function deductAmountFromClientAccountBalance()
    {
        return $this->decrementClientAccountBalance($this->amount) && $this->markAsPaid();
    }

    /**
     * Update a repayment to indicate it is fully paid
     *
     * @return bool
     */
    public function markAsPaid()
    {
        return $this->update([
            'has_been_paid' => true,
            'repayment_timestamp' => Carbon::now(),
            'paid_principal' => $this->principal,
            'paid_interest' => $this->interest,
            'paid_fees' => $this->fees,
            'user_id' => auth()->id(),
            'status' => self::FULL_PAYMENT,
        ]);
    }

    /**
     * @throws \App\Exceptions\InsufficientAccountBalanceException
     */
    private function deductInterestFromClientAccountBalance()
    {
        $outstandingInterest = $this->interest - $this->paid_interest;

        $amount = $this->loan->fresh()->client->isDeductable($outstandingInterest) ?
            $outstandingInterest : $this->getClientAccountBalance();

        $paid_interest = $this->paid_interest + $amount;

        if ($this->decrementClientAccountBalance($amount)) {
            $this->update([
                'paid_interest' => $paid_interest,
                'status' => self::PART_PAYMENT
            ]);
        }

        /**
         * After the interest is successfully deducted, use whatever client account
         * balance remaining to repay the principal.
         *
         * If for some reason, Client has more money in his account, such as depositing more than
         * what is currently outstanding. Make sure the principal getting deducted isn't more than
         * the principal on the repayment.
         */
        $this->deductPrincipalFromClientAccountBalance();
    }

    /**
     * @throws \App\Exceptions\InsufficientAccountBalanceException
     */
    private function deductPrincipalFromClientAccountBalance()
    {
        $outstandingPrincipal = $this->principal - $this->paid_principal;

        $amount = $this->loan->fresh()->client->isDeductable($outstandingPrincipal) ?
            $outstandingPrincipal : $this->getClientAccountBalance();

        $paidPrincipal = $this->paid_principal + $amount;

        if ($this->decrementClientAccountBalance($amount)) {
            $this->update([
                'paid_principal' => $paidPrincipal,
                'status' => self::PART_PAYMENT
            ]);
        }

        // if this repayment doesn't have a fee component and we have finished repaying principal,
        // mark this repayment as fully paid
        !$this->hasFees() && $paidPrincipal === $this->principal && $this->markAsPaid();
    }

    /**
     * Attempt to deduct full fees. If that is unsuccessful, deduct whatever the Client has  available
     *
     * @throws \App\Exceptions\InsufficientAccountBalanceException
     */
    private function deductFeesFromClientAccountBalance()
    {
        $outstandingFees = $this->fees - $this->paid_fees;

        $amount = $this->loan->fresh()->client->isDeductable($outstandingFees) ?
            $outstandingFees : $this->getClientAccountBalance();

        $paidFees = $this->paid_fees + $amount;

        if ($this->decrementClientAccountBalance($amount)) {
            $this->update([
                'paid_fees' => $paidFees,
                'status' => self::PART_PAYMENT
            ]);
        }

        // if we have finished repaying fees, mark this repayment as fully paid
        $paidFees === $this->fees && $this->markAsPaid();
    }

    /**
     * Don't update the status to DEFAULTED if some payment has already been made.
     * In that case the status should be PART_PAYMENT
     *
     * @return bool
     */
    public function markAsDefaulted()
    {
        if ($this->getTotalAmountPaid(false) <= 0) {
            return $this->update(['status' => self::DEFAULTED]);
        }

        return false;
    }

    /**
     * @param bool $format
     * @return int|mixed|string
     */
    public function getOutstandingRepaymentAmount($format = true)
    {
        $outstanding = 0;

        if (! $this->isFullyPaid()) {
            $outstanding = $this->amount - $this->getTotalAmountPaid(false);
        }

        return $format ? number_format($outstanding, 2) : $outstanding;
    }

    /**
     * @param bool $format
     * @return mixed|string
     */
    public function getPaidPrincipal($format = true)
    {
        return $format ? number_format($this->paid_principal, 2) : $this->paid_principal;
    }

    /**
     * @param bool $format
     * @return mixed|string
     */
    public function getPaidInterest($format = true)
    {
        return $format ? number_format($this->paid_interest, 2) : $this->paid_interest;
    }

    /**
     * @param bool|true $format
     * @return mixed|string
     */
    public function getPaidFees($format = true)
    {
        return $format ? number_format($this->paid_fees, 2) : $this->paid_fees;
    }

    /**
     * @param bool $format
     * @return mixed|string
     */
    public function getTotalAmountPaid($format = true)
    {
        $total = $this->paid_interest + $this->paid_principal + $this->paid_fees;

        return $format ? number_format($total, 2) : $total;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getStatus()
    {
        $label = '<span class="label label-%s">%s</span>';
        $background = 'default';

        if (!$this->isDue() && !$this->loan->isPaidOff()) {
            $label = sprintf($label, 'default', 'Not Due');
        } elseif ($this->isFullyPaid()) {
            $label = sprintf($label, 'success', 'Paid');
            $background = 'success';
        } elseif ($this->isDefaulted()) {
            $label = sprintf($label, 'danger', self::DEFAULTED);
            $background = 'danger';
        } elseif (null === $this->getPaidInterest(false)) {
            $label = sprintf($label, 'default', 'No Deduction made');
        } elseif ($this->isPartlyPaid()) {
            $label = sprintf($label, 'warning', self::PART_PAYMENT);
            $background = 'warning';
        }

        return collect(compact('label', 'background'));
    }

    /**
     * @return bool
     */
    public function isFullyPaid()
    {
        return $this->status === self::FULL_PAYMENT && $this->has_been_paid;
    }

    /**
     * @return bool
     */
    public function isPartlyPaid()
    {
        return $this->hasOutstandingInterest() || $this->hasOutstandingPrincipal() || $this->hasOutstandingFees();
    }

    /**
     * @return bool
     */
    private function hasOutstandingPrincipal()
    {
        return $this->status === self::PART_PAYMENT && $this->paid_principal < $this->principal;
    }

    /**
     * @return bool
     */
    private function hasOutstandingInterest()
    {
        return $this->status === self::PART_PAYMENT && $this->paid_interest < $this->interest;
    }

    /**
     * @return bool
     */
    private function hasOutstandingFees()
    {
        return $this->status === self::PART_PAYMENT && $this->paid_fees < $this->fees;
    }

    public function isDefaulted()
    {
        return $this->isDue() && $this->status === self::DEFAULTED;
    }

    public function isDue()
    {
        return $this->due_date->lte(Carbon::today());
    }

    /**
     * A control to avoid decrementing an account to a negative balance
     *
     * @param $amount
     * @return bool
     * @throws InsufficientAccountBalanceException
     */
    private function decrementClientAccountBalance($amount)
    {
        if ($amount > $this->getClientAccountBalance()) {
            throw new InsufficientAccountBalanceException(
                sprintf(
                    'Client #%s doesn\'t have enough account balance (%d) for deduction. [toBeDeducted => %d, loanNumber => %s]',
                    $this->loan->client->account_number, $this->loan->client->getAccountBalance(), $amount, $this->loan->number
                )
            );
        }

        // WARNING: don't use decrement here. Causes precision errors (Floating point arithmetics)
        return $this->loan->client()->update(['account_balance' => $this->getClientAccountBalance() - $amount]);
    }

    /**
     * @return mixed
     */
    private function getClientAccountBalance()
    {
        return $this->loan->client->getAccountBalance(false);
    }

    public function getDueDate($dateFormat = 'd/m/Y')
    {
        return $this->due_date ? $this->due_date->format($dateFormat) : 'N/A';
    }

    /**
     * Check if there is an amortized fee on this repayment (and on the loan as a whole)
     * @return bool
     */
    public function hasFees()
    {
        return $this->fees > 0;
    }
}
