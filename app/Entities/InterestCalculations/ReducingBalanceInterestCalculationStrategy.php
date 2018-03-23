<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/02/2017
 * Time: 1:49 PM
 */

namespace App\Entities\InterestCalculations;

use App\Entities\Loan;
use App\Jobs\AddLoanRepaymentJob;
use App\Traits\LoanInterestCalculation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class ReducingBalanceInterestCalculationStrategy implements LoanInterestCalculationStrategyInterface
{
    use LoanInterestCalculation;

    /**
     * @var Loan
     */
    private $loan;

    /**
     * ReducingBalanceInterestCalculationStrategy constructor.
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function schedule(): Collection
    {
        $repayments = collect([]);
        $dueDate = $this->getRepaymentStartDate();
        $loanAmount = $this->loan->getPrincipalAmount(false);

        for ($i = 0, $count = $this->loan->getNumberOfRepayments(); $i < $count; $i++) {
            $dueDate = $this->loan->getNextRepaymentDueDatePerPlan($dueDate);
            $repaymentAmount = $this->getRepaymentAmount();
            $interest = $this->getInterestOnRepayment($loanAmount);
            $principal = $this->getPrincipalOnRepayment($repaymentAmount, $interest);

            $request = new Request([
                'loan_id' => $this->loan->id,
                'amount' => $repaymentAmount,
                'principal' => $principal,
                'interest' => $interest,
                'fees' => $this->loan->getFeesComponentOnRepayment(),
                'due_date' => $dueDate
            ]);

            $repayments->push($this->dispatch(new AddLoanRepaymentJob($request)));

            $loanAmount = $loanAmount - $principal < 0 ? 0 : $loanAmount - $principal;
        }

        return $repayments;
    }

    /**
     * @see http://bit.ly/2kHACRx
     * @return float
     */
    public function getRepaymentAmount()
    {
        $simpleMonthlyInterest = $this->loan->getMonthlyInterest(false);

        $timeInterestRateFactor = 1 - (1 / (1 + $this->loan->getMonthlyRateInPercentage()) ** $this->loan->tenure->number_of_months);

        $monthlyRepaymentAmount = $this->loan->getFeesComponentOnRepayment();

        if ($timeInterestRateFactor !== 0.0) {
            $monthlyRepaymentAmount += $simpleMonthlyInterest / $timeInterestRateFactor;
        }

        return $monthlyRepaymentAmount  / $this->loan->repaymentPlan->number_of_repayments_per_month;
    }

    /**
     * @param $outstandingPrincipal
     * @return float
     * @see https://www.comparehero.my/blog/how-to-calculate-flat-rate-interest-and-reducing-balance-rate
     */
    private function getInterestOnRepayment($outstandingPrincipal)
    {
        return ($outstandingPrincipal * $this->loan->getMonthlyRateInPercentage()) / $this->loan->repaymentPlan->number_of_repayments_per_month;
    }
}