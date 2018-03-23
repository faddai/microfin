<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/02/2017
 * Time: 1:57 PM
 */

namespace App\Entities\InterestCalculations;

use App\Entities\Loan;
use App\Jobs\AddLoanRepaymentJob;
use App\Traits\LoanInterestCalculation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class StraightLineInterestCalculationStrategy implements LoanInterestCalculationStrategyInterface
{
    use LoanInterestCalculation;

    /**
     * @var Loan
     */
    private $loan;

    /**
     * StraightLineInterestCalculationStrategy constructor.
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Generate a repayment schedule for this Loan using Straight
     * Line to calculate interest
     *
     * @return Collection
     */
    public function schedule(): Collection
    {
        // the actual repayment start date of the loan would be available once the loan is approved and disbursed
        // by default it is same as the date the loan was created. This is by design so that demo schedule
        // can be generated as part of the loan creation
        $dueDate = $this->getRepaymentStartDate();

        $repayments = collect([]);

        for ($i = 1; $i <= $this->loan->getNumberOfRepayments(); $i++) {
            $dueDate = $this->loan->getNextRepaymentDueDatePerPlan($dueDate);
            $repaymentAmount = $this->getRepaymentAmount();
            $interest = $this->getInterestOnRepayment();
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
        }

        return $repayments;
    }

    /**
     * @return float
     */
    public function getRepaymentAmount()
    {
        $totalInterest = $this->loan->tenure->number_of_months * $this->loan->getMonthlyInterest(false);

        $totalInterestAndPrincipalAmount = $totalInterest + $this->loan->getPrincipalAmount(false);

        $simpleMonthlyPayment = $totalInterestAndPrincipalAmount / $this->loan->getNumberOfRepayments();

        return $simpleMonthlyPayment + $this->loan->getFeesComponentOnRepayment();
    }

    /**
     * @return float|string
     */
    private function getInterestOnRepayment()
    {
        return $this->loan->getMonthlyInterest(false) / $this->loan->repaymentPlan->number_of_repayments_per_month;
    }
}