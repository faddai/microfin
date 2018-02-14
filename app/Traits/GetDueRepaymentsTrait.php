<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 25/12/2016
 * Time: 15:06
 */

namespace App\Traits;

use App\Entities\Loan;
use App\Entities\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;


trait GetDueRepaymentsTrait
{

    /**
     * @param \Closure|string $filterCallback
     * @param Loan $loan
     * @return Collection
     */
    public function getRepayments($filterCallback, Loan $loan = null): Collection
    {
        $filterCallback = $this->normalizeFilterCallback($filterCallback);

        if (null === $loan) {
            return LoanRepayment::schedule()->filter($filterCallback);
        }

        return $this->getRepaymentsForASpecificLoan($filterCallback, $loan);
    }

    /**
     * @param string $endDate
     * @param null $startDate
     * @param \Closure $callback
     * @return Collection
     */
    public function getRepaymentsForDate($endDate, $startDate = null, \Closure $callback = null): Collection
    {
        $startDate = $startDate ?: Carbon::today();

        return LoanRepayment::schedule(null, [$endDate, $startDate])->filter($callback);
    }

    /**
     * @param $filterCallback
     * @param Loan $loan
     * @return Collection
     */
    private function getRepaymentsForASpecificLoan($filterCallback, Loan $loan)
    {
        $filterCallback = $this->normalizeFilterCallback($filterCallback);

        return LoanRepayment::schedule($loan)->filter($filterCallback);
    }

    private function normalizeFilterCallback($filterCallback)
    {
        if (! $filterCallback instanceof \Closure) {
            $filterCallback = function (LoanRepayment $repayment) use ($filterCallback) {
                return $repayment->{$filterCallback}();
            };
        }

        return $filterCallback;
    }
}