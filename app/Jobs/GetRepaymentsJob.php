<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 07/01/2017
 * Time: 11:13 PM
 */

namespace App\Jobs;

use App\Entities\LoanRepayment;
use App\Traits\GetDueRepaymentsTrait;
use Illuminate\Http\Request;


class GetRepaymentsJob
{
    use GetDueRepaymentsTrait;

    /**
     * @var Request
     */
    private $request;

    /**
     * GetRepaymentsJob constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle()
    {
        if ($this->requestHasDateRange()) {

            return $this->getRepaymentsForDate(
                $this->request->get('startDate'),
                $this->request->get('endDate'),
                function (LoanRepayment $repayment) {
                    return $this->isUnpaid($repayment);
                }
            )->filter($this->filterLoansForCreditOfficer());
        }

        return $this->getRepayments(function (LoanRepayment $repayment) {
            return $this->isUnpaid($repayment);
        });
    }

    private function requestHasDateRange()
    {
        return $this->request->has('startDate') || $this->request->has('endDate');
    }

    private function isUnpaid(LoanRepayment $repayment)
    {
        return $repayment->isDue() && ! $repayment->isFullyPaid();
    }

    /**
     * Get loans belonging to a specified credit officer
     *
     * @return \Closure
     */
    private function filterLoansForCreditOfficer()
    {
        return function (LoanRepayment $repayment) {

            if (! $this->request->has('credit_officer')) {
                return true;
            }

            return $repayment->loan->creditOfficer->id == $this->request->get('credit_officer');
        };
    }
}