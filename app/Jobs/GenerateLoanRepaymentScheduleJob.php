<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 04/12/2016
 * Time: 9:12 PM
 */

namespace App\Jobs;

use App\Entities\InterestCalculations\LoanInterestCalculationStrategyInterface;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use Illuminate\Support\Collection;


class GenerateLoanRepaymentScheduleJob
{
    /**
     * @var Loan
     */
    private $loan;

    /**
     * @var LoanInterestCalculationStrategyInterface
     */
    private $strategy;

    /**
     * @var bool
     */
    private $regenerate;

    /**
     * @var bool
     */
    private $isBackdatedLoan;

    /**
     * Create a new job instance.
     *
     * @param Loan $loan
     * @param bool $regenerate
     * @param bool $isBackdatedLoan
     */
    public function __construct(Loan $loan, $regenerate = false, $isBackdatedLoan = false)
    {
        $this->loan = $loan;
        $this->strategy = $loan->getInterestCalculationStrategyInstance();
        $this->regenerate = $regenerate;
        $this->isBackdatedLoan = $isBackdatedLoan;
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(): Collection
    {
        // don't bother re-generating the schedule if it was previously generated
        if (! $this->regenerate && LoanRepayment::schedule($this->loan)->count()) {
            return LoanRepayment::schedule($this->loan);
        }

        // drop all repayments for this loan (if any) before re/generating
        $this->loan->schedule()->delete();

        $schedule = $this->strategy->schedule();

        // loan is backdated so whatever money is in Client's account can't be used to repay loan
        // set status of the repayments due as defaulted repayments
        if ($this->isBackdatedLoan) {
            $schedule
                ->filter(function (LoanRepayment $repayment) { return $repayment->isDue(); })
                ->transform(function (LoanRepayment $repayment) { return $repayment->markAsDefaulted(); });
        }

        return $schedule;
    }
}