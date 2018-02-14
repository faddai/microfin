<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/07/2017
 * Time: 22:15
 */

namespace App\Listeners;


use App\Entities\Loan;
use App\Events\LoanDeclinedEvent;

class ReverseRestructureWhenLoanIsDeclined
{
    /**
     * If the loan that was just declined was previously marked as Restructured,
     * reverse that and keep the Loan in its previous status
     *
     * @param LoanDeclinedEvent $event
     */
    public function handle(LoanDeclinedEvent $event)
    {
        if ($loan = $event->loan->parentLoan) {

            $loan->forceFill([
                'restructured_by' => null,
                'restructured_at' => null,
                'status' => Loan::DISBURSED,
            ])->save();

        }
    }
}