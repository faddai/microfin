<?php

namespace App\Listeners;

use App\Entities\LoanRepayment;
use App\Events\DepositAddedEvent;
use App\Events\LoanRepaymentDeductedEvent;

class DeductLoanRepaymentAfterClientDepositTransaction
{

    /**
     * Handle the event.
     *
     * @param  DepositAddedEvent $event
     * @return void
     * @throws \App\Exceptions\InsufficientAccountBalanceException
     */
    public function handle(DepositAddedEvent $event)
    {
        // get repayments belonging to the client in this transaction
        LoanRepayment::getDueRepaymentsForAClient($event->transaction->client)->each(function (LoanRepayment $repayment) {

            if ($repayment->loan->client->isDeductable()) {

                $repaymentBeforeDeduction = $repayment->replicate();

                $repayment->deductFromClientAccountBalance();

                event(new LoanRepaymentDeductedEvent($repaymentBeforeDeduction, $repayment));
            }

        });
    }
}
