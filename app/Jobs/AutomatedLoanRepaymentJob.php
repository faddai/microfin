<?php

namespace App\Jobs;

use App\Entities\LoanRepayment;
use App\Events\LoanRepaymentDeductedEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;


class AutomatedLoanRepaymentJob
{
    /**
     * @var Carbon|null
     */
    private $dueDate;

    /**
     * AutomatedLoanRepaymentJob constructor.
     * @param Carbon|null $dueDate
     */
    public function __construct(Carbon $dueDate = null)
    {
        $this->dueDate = $dueDate ?? Carbon::today();
    }

    /**
     * Execute the job.
     *
     * @return mixed
     * @throws \App\Exceptions\InsufficientAccountBalanceException
     */
    public function handle()
    {
        logger('Execute loan repayment job', [
            'dueDate' => $this->dueDate->format('d/m/Y'),
            'repaymentsCount' => $this->getLoanRepaymentsDue()->count()
        ]);

        return DB::transaction(function () {
            return $this->getLoanRepaymentsDue()->chunk(30, function (Collection $repayments) {
                return $repayments->each(function (LoanRepayment $repayment) {

                    $repaymentBeforeDeduction = $repayment->replicate();

                    $repayment->loan->isDisbursed() &&
                    $repayment->deductFromClientAccountBalance();

                    // post a ledger transaction of whatever amount was deducted
                    event(new LoanRepaymentDeductedEvent($repaymentBeforeDeduction, $repayment));
                });
            });
        });

    }

    /**
     * @return Builder
     */
    private function getLoanRepaymentsDue(): Builder
    {
        return LoanRepayment::getLoanRepaymentsDue($this->dueDate);
    }
}