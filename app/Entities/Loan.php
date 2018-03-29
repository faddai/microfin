<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 1:49 PM
 */

namespace App\Entities;

use App\Entities\InterestCalculations\LoanInterestCalculationStrategyInterface;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


class Loan extends Model
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const DISBURSED = 'disbursed';
    const DECLINED = 'declined';
    const PAID_OFF = 'paid off';
    const RESTRUCTURED = 'restructured';

    // interest calculation
    const REDUCING_BALANCE_STRATEGY = 'reducing_balance';
    const STRAIGHT_LINE_STRATEGY = 'straight_line';

    protected $fillable = [
        'client_id', 'tenure_id', 'repayment_plan_id', 'credit_officer', 'loan_size', 'purpose',
        'amount', 'rate', 'zone_id', 'grace_period', 'loan_type_id', 'age_group', 'monthly_income',
        'business_sector_id', 'start_date', 'maturity_date', 'user_id', 'interest_calculation_strategy',
        'disbursed_at', 'loan_product_id', 'disburser_id', 'status', 'approved_at', 'number', 'approver_id',
        // make created_at overridable
        'created_at',
    ];

    protected $dates = ['start_date', 'maturity_date', 'approved_at', 'disbursed_at'];

    /**
     * @param Builder $query
     * @return mixed
     */
    public function scopeActive(Builder $query)
    {
        return $query->disbursed();
    }

    /**
     * @todo rename this to active
     * @deprecated use scopeActive instead of this
     * @param $query
     * @return mixed
     */
    public function scopeDisbursed(Builder $query)
    {
        return $query->where('status', self::DISBURSED)
            ->whereNotNull('disbursed_at')
            ->whereNotNull('disburser_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePaidOff(Builder $query)
    {
        return $query->where('status', self::PAID_OFF)
            ->whereHas('payoff', function (Builder $query) {
                return $query->whereNotNull('created_at')->whereNotNull('created_by');

            });
    }

    /**
     * @todo return a chunk of the result set
     * @param Builder $query
     * @return mixed
     */
    public function scopeRunning(Builder $query)
    {
        return $query->disbursed()->get();
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return mixed
     */
    public function scopeBook(Builder $query, Request $request)
    {
        return $query->with('client.clientable', 'product', 'schedule', 'fees', 'type', 'payoff')
            ->disbursed()
            ->when($request->get('business_unit'), function (Builder $query) use ($request) {
                return $query->whereHas('createdBy.branch', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('business_unit'));
                });
            })
            ->when($request->get('credit_officer'), function (Builder $query) use ($request) {
                return $query->whereHas('creditOfficer', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('credit_officer'));
                });
            })
            ->when($request->get('loan_type'), function (Builder $query) use ($request) {
                return $query->whereHas('type', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('loan_type'));
                });
            })
            ->when($request->get('product_id'), function (Builder $query) use ($request) {
                return $query->whereHas('product', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('product_id'));
                });
            })
            ->when($request->get('date'), function (Builder $query) use ($request) {
                return $query->where('disbursed_at', '<=', $request->get('date'));
            })
            ->get();
    }

    /**
     * @param $query
     * @param Request $request
     * @return mixed
     */
    public function scopeCollection(Builder $query, Request $request)
    {
        return $query->with([
            'repaymentCollections' => function ($query) use ($request) {
                $query->whereBetween('collected_at', [$request->get('startDate'), $request->get('endDate')]);
            },
            'schedule', 'fees', 'client.clientable', 'creditOfficer'])
            ->when($request->get('credit_officer'), function (Builder $query) use ($request) {
                return $query->whereHas('creditOfficer', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('credit_officer'));
                });
            })
            ->when($request->get('loan_type'), function (Builder $query) use ($request) {
                return $query->whereHas('type', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('loan_type'));
                });
            })
            ->when($request->get('product_id'), function (Builder $query) use ($request) {
                return $query->whereHas('product', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('product_id'));
                });
            })
            ->when($request->get('business_unit'), function (Builder $query) use ($request) {
                return $query->whereHas('createdBy.branch', function (Builder $query) use ($request) {
                    return $query->where('id', $request->get('business_unit'));
                });
            })
            ->whereHas('schedule', function (Builder $query) use ($request) {
                return $query->due();
            })
            ->whereHas('repaymentCollections', function (Builder $query) use ($request) {
                return $query->whereBetween('collected_at', [$request->get('startDate'), $request->get('endDate')]);
            })
            ->get();
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return mixed
     */
    public function scopeCrb(Builder $query, Request $request)
    {
        $clientable = $request->get('client_type');

        return $query
            ->with([
                'schedule',
                'repaymentCollections',
                'client.clientable',
                'type',
                'tenure',
                'client.country',
                'repaymentPlan',
                'fees',
                'payments.loan',
            ])
            ->disbursed()
            ->when($clientable, function (Builder $query) use ($clientable) {
                $query->whereHas('client', function (Builder $query) use ($clientable) {
                    $query->whereClientableType($clientable);
                });
            });
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return mixed
     */
    public function scopeMaturityLadder(Builder $query, Request $request)
    {
        $creditOfficer = $request->get('credit_officer');
        $product = $request->get('product_id');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $loanType = $request->get('loan_type');

        return $query->with(['client.clientable', 'product', 'schedule', 'fees'])
            ->disbursed()
            ->when($creditOfficer, function ($query) use ($creditOfficer) {
                return $query->whereCreditOfficer($creditOfficer);
            })
            ->when($product, function ($query) use ($product) {
                return $query->whereLoanProductId($product);
            })
            ->when($loanType, function ($query) use ($loanType) {
                return $query->whereLoanTypeId($loanType);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('maturity_date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
            });
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return mixed
     */
    public function scopeBusinessSector(Builder $query, Request $request)
    {
        $creditOfficer = $request->get('credit_officer');
        $product = $request->get('product_id');
        $date = $request->get('date');
        $loanType = $request->get('loan_type');
        $sector = $request->get('sector');

        return $query->with(['client.clientable', 'product', 'schedule', 'fees', 'sector', 'type'])
            ->disbursed()
            ->when($creditOfficer, function ($query) use ($creditOfficer) {
                return $query->whereCreditOfficer($creditOfficer);
            })
            ->when($product, function ($query) use ($product) {
                return $query->whereLoanProductId($product);
            })
            ->when($loanType, function ($query) use ($loanType) {
                return $query->whereLoanTypeId($loanType);
            })
            ->when($sector, function ($query) use ($sector) {
                return $query->whereBusinessSectorId($sector);
            })
            ->when($date, function ($query) use ($date) {
                return $query->whereDate('disbursed_at', '<=', $date);
            });
    }

    /**
     * @param Builder $query
     * @param Request $request
     * @return mixed
     */
    public function scopeDaysToMaturity(Builder $query, Request $request)
    {
        $creditOfficer = $request->get('credit_officer');
        $product = $request->get('product_id');
        $date = $request->get('date');
        $loanType = $request->get('loan_type');

        return $query->with(['client.clientable', 'product', 'type', 'schedule'])
            ->disbursed()
            ->when($creditOfficer, function ($query) use ($creditOfficer) {
                return $query->whereCreditOfficer($creditOfficer);
            })
            ->when($product, function ($query) use ($product) {
                return $query->whereLoanProductId($product);
            })
            ->when($loanType, function ($query) use ($loanType) {
                return $query->whereLoanTypeId($loanType);
            })
            ->when($date, function ($query) use ($date) {
                return $query->whereDate('maturity_date', '<=', $date->copy()->endOfDay());
            });
    }

    /**
     * The loan that was restructured if this loan was created as a result of
     * a Loan Restructure
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentLoan()
    {
        return $this->belongsTo(self::class, 'parent_loan_id');
    }

    /**
     * Get all the restructures for this loan.
     * Ideally, there'd be only 1 restructure out of a Loan. In cases, where 1 or more requests to
     * restructure a Loan is declined, they'd still be attached to the Loan out of which this Loan was created
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function restructures()
    {
        return $this->hasMany(self::class, 'parent_loan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function collaterals()
    {
        return $this->hasMany(Collateral::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function guarantors()
    {
        return $this->hasMany(Guarantor::class);
    }

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
    public function sector()
    {
        return $this->belongsTo(BusinessSector::class, 'business_sector_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenure()
    {
        return $this->belongsTo(Tenure::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repaymentPlan()
    {
        return $this->belongsTo(RepaymentPlan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creditOfficer()
    {
        return $this->belongsTo(User::class, 'credit_officer');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fees()
    {
        return $this->belongsToMany(Fee::class, 'loan_fees')
            ->withPivot('amount', 'rate', 'is_paid_upfront', 'type') // utilized after loan creation instead of props on Fee itself
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schedule()
    {
        return $this->hasMany(LoanRepayment::class)->withoutGlobalScope('paid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function statement()
    {
        return $this->hasOne(LoanStatement::class, 'loan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function disbursedBy()
    {
        return $this->belongsTo(User::class, 'disburser_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function repaymentCollections()
    {
        return $this->hasManyThrough(LoanRepaymentCollection::class, LoanRepayment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payoff()
    {
        return $this->hasOne(LoanPayoff::class, 'loan_id');
    }

    /**
     * Calculates the amount to pay as interest on monthly basis using the monthly rate
     *
     * @param bool $format
     * @return mixed|string
     */
    public function getMonthlyInterest($format = true)
    {
        $interest = $this->amount * $this->getMonthlyRateInPercentage();

        return $format ? number_format($interest, 2) : $interest;
    }

    /**
     * Calculates the interest accumulated over the tenure of the loan
     *
     * @param bool $format
     * @return string|float
     */
    public function getTotalInterest($format = true)
    {
        $interest = $this->schedule->sum('interest');

        return $format ? number_format($interest, 2) : $interest;
    }

    /**
     * Calculates the sum total of the loan amount, interest and fees payable
     * throughout the lifetime of the Loan
     *
     * @param bool $format
     * @return mixed|string
     */
    public function getTotalLoanAmount($format = true)
    {
        $total = $this->getPrincipalAmount(false) + $this->getTotalInterest(false) + $this->getTotalFees(false);

        return $format ? number_format($total, 2) : $total;
    }

    /**
     * Gets the actual loan amount with optional formatting
     *
     * @param bool $format
     * @return mixed|string
     */
    public function getPrincipalAmount($format = true)
    {
        return $format ? number_format($this->amount, 2) : $this->amount;
    }

    /**
     * @param bool $format
     * @return mixed|string
     */
    public function getBalance($format = true)
    {
        // if this loan has a payoff created and it has been approved
        if ($this->hasAnApprovedPayoff()) {
            return number_format(0, 2);
        }

        $balance = round($this->getTotalLoanAmount(false), 2) - round($this->getAmountPaid(false), 2);

        return $format ? number_format($balance, 2) : $balance;
    }

    /**
     * Calculates the total amount of fees applied to this loan
     *
     * @param bool $format
     * @return float
     */
    public function getTotalFees($format = true)
    {
        $amount = $this->fees->sum('pivot.amount');

        return $format ? number_format($amount, 2) : $amount;
    }

    /**
     * @param bool $format
     * @return float|string
     */
    public function getTotalFeesWithoutUpfrontFees($format = true)
    {
        $amount = $this->getTotalFees(false) - $this->getUpfrontFees(false);

        return $format ? number_format($amount, 2) : $amount;
    }

    /**
     * Get the total amount of money repaid so far
     *
     * @param bool $format
     * @return mixed
     */
    public function getAmountPaid($format = true)
    {
        $amount = $this->schedule
            ->filter(function (LoanRepayment $schedule) {
                return $schedule->isFullyPaid() || $schedule->isPartlyPaid();
            })
            ->sum(function (LoanRepayment $repayment) {
                return array_sum([
                    $repayment->paid_interest,
                    $repayment->paid_principal,
                    $repayment->paid_fees,
                ]);
            });

        $amount += $this->getUpfrontFees(false);

        return $format ? number_format($amount, 2) : $amount;
    }

    public function getStatus()
    {
        switch(strtolower($this->status)) {
            case self::PENDING:
            case self::APPROVED:
                $label = 'Inactive';
                break;

            case self::DISBURSED:
                $label = 'Active';

                if ($this->isFullyPaid()) {
                    $label = 'Closed';
                }
                break;

            case self::PAID_OFF:
                $label = 'Paid off';
                break;

            case self::DECLINED:
                $label = ucfirst($this->status);
                break;

            case self::RESTRUCTURED:
                $label = ucfirst($this->status);
                break;

            default:
                $label = 'Inactive';
        }

        return $label;
    }

    /**
     * The total loan amount is fully paid
     *
     * @return bool
     */
    public function isFullyPaid()
    {
        return $this->isPaidOff() || $this->getAmountPaid() === $this->getTotalLoanAmount();
    }

    /**
     * @return string
     */
    public function isPaidOff()
    {
        return $this->status === Loan::PAID_OFF;
    }

    public function markAsPaidOff()
    {
        return $this->update(['status' => Loan::PAID_OFF]);
    }

    /**
     * If no loan start date is provided, use current date.
     * Apply grace period for loan to the loan start date
     *
     * @param $value
     * @return static
     */
    public function setStartDateAttribute($value)
    {
        $startDate = Carbon::parse($value) ?: Carbon::now();

        $startDate->addWeekdays($this->grace_period);

        $this->attributes['start_date'] = $startDate;
    }

    /**
     * Get loan maturity date
     *
     * Loan matures from the time it is disbursed + grace period if applicable + the tenure selected by Client
     * Currently, this calculation is handled by @see GenerateLoanRepaymentScheduleJob so the due_date of the last
     * repayment is used
     *
     * @return Carbon|string
     */
    public function getMaturityDateAttribute()
    {
        return $this->schedule->count() ? $this->schedule->last()->due_date : 'n/a';
    }

    /**
     * Calculate how much to pay every month for the loan
     *
     * @param bool $format
     * @return float|string
     */
    public function getRepaymentAmount($format = true)
    {
        $amount = $this->schedule->first()->amount;

        return $format ? number_format($amount, 2) : $amount;
    }

    /**
     * Calculate the number of expected payments to be made till the loan is fully paid
     *
     * @example A $1000 loan with a 3 month tenure and weekly payments would be 3 * (4 weeks)
     * because 4 weeks makes a month.
     *
     * @return mixed
     */
    public function getNumberOfRepayments(): int
    {
        return $this->tenure->number_of_months * $this->repaymentPlan->number_of_repayments_per_month;
    }

    /**
     * @return string
     */
    public function getTransactionBranch()
    {
        return $this->createdBy->branch->name ?? 'n/a';
    }

    /**
     * @return string
     */
    public function status()
    {
        return ucfirst($this->attributes['status']);
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::PENDING;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->approver_id !== null && $this->approved_at !== null;
    }

    /**
     * @return bool
     */
    public function isDisbursed()
    {
        return $this->disburser_id !== null && $this->disbursed_at !== null;
    }

    /**
     * @return float
     */
    public function getMonthlyRateInPercentage()
    {
        return $this->rate / 100;
    }

    /**
     * Set the next repayment due date
     *
     * @param $dueDate
     * @return mixed
     */
    public function getNextRepaymentDueDatePerPlan($dueDate)
    {
        return Carbon::parse($dueDate)->startOfDay()->addWeekdays($this->repaymentPlan->number_of_days);
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        $currency = config('app.currency');
        $rate = number_format($this->rate, 2);

        return "$currency {$this->getPrincipalAmount()} @ $rate%";
    }

    /**
     * @param $rate
     * @return mixed|string
     */
    public function getFeeAmount($rate)
    {
        return $this->getPrincipalAmount(false) * $rate;
    }

    /**
     * Get the amount of money that goes towards the repayment of fees
     * on each repayment
     *
     * @param bool $format
     * @return float
     */
    public function getFeesComponentOnRepayment($format = false)
    {
        $fee = ($this->getTotalFees(false) - $this->getUpfrontFees(false)) / $this->getNumberOfRepayments();

        return $format ? number_format($fee, 2) : $fee;
    }

    /**
     * @return LoanInterestCalculationStrategyInterface
     */
    public function getInterestCalculationStrategyInstance()
    {
        $strategyPath = '\App\Entities\InterestCalculations\%sInterestCalculationStrategy';
        $strategy = sprintf($strategyPath, str_replace(' ', '', $this->getInterestCalculationStrategy()));

        return new $strategy($this);
    }

    /**
     * @return string
     */
    public function getInterestCalculationStrategy()
    {
        return ucwords(str_replace('_', ' ', $this->interest_calculation_strategy));
    }

    /**
     * @param bool $format
     * @return mixed
     */
    public function getUpfrontFees($format = true)
    {
        $amount = $this->fees->filter(function (Fee $fee) {
            return (bool)$fee->pivot->is_paid_upfront;
        })->sum('pivot.amount');

        return $format ? number_format($amount, 2) : $amount;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->isDisbursed() && ! $this->isFullyPaid();
    }

    /**
     * Get the daily interest for this loan for a particular month and year
     * @param Carbon $date
     * @return float
     */
    public function getDailyInterestForTheMonth(Carbon $date)
    {
        $interest = $this->schedule()
                ->whereYear('due_date', $date->format('Y'))
                ->whereMonth('due_date', $date->format('m'))
                ->first()->interest ?? 0;

        return round($interest / $this->repaymentPlan->number_of_days, 2);
    }

    /**
     * Loan has elapsed its maturity date
     * @return bool
     */
    public function isMatured()
    {
        return $this->maturity_date->lt(Carbon::today());
    }

    /**
     * @return float
     */
    public function getRateAttribute()
    {
        return round($this->attributes['rate'] ?? 0, 2);
    }

    /**
     * @return mixed
     */
    public function isBackdated()
    {
        return $this->disbursed_at->lt($this->created_at);
    }

    /**
     * @param $column
     * @param bool $format
     * @return string
     */
    public function getOutstanding($column, $format = true)
    {
        $outstanding = $this->schedule->sum($column) - $this->schedule->sum('paid_'. $column);

        return $format ? number_format($outstanding, 2) : $outstanding;
    }

    /**
     * @return bool
     */
    private function hasAnApprovedPayoff()
    {
        return $this->payoff && LoanPayoff::APPROVED === $this->payoff->status;
    }

    /**
     * @return bool
     */
    public function isRestructured()
    {
        return $this->status === self::RESTRUCTURED && $this->restructured_at !== null && $this->restructured_by !== null;
    }

}