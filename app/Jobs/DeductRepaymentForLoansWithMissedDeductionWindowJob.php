<?php

namespace App\Jobs;

use App\Entities\LoanRepayment;
use App\Traits\GetDueRepaymentsTrait;


/**
 * Handles the deduction of repayments that are due but the repayment
 * amount hasn't been deducted yet. Possibly, due to the unavailability
 * of the app at the scheduled date to automatically deduct repayment.
 *
 * Class DeductRepaymentForLoansWithMissedDeductionWindowJob
 * @package App\Jobs
 */
class DeductRepaymentForLoansWithMissedDeductionWindowJob
{
    use GetDueRepaymentsTrait;

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle()
    {
        $this->getRepayments(function (LoanRepayment $repayment) {
            return $repayment->isDue() && $repayment->status === null;
        })->each(function (LoanRepayment $repayment) {
            dispatch(new AutomatedLoanRepaymentJob($repayment->due_date));
        });
    }
}
