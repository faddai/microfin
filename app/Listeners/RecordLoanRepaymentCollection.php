<?php

namespace App\Listeners;

use App\Entities\LoanRepaymentCollection;
use App\Events\LoanRepaymentDeductedEvent;
use Carbon\Carbon;

class RecordLoanRepaymentCollection
{
    /**
     * Handle the event.
     *
     * @param  LoanRepaymentDeductedEvent  $event
     * @return void
     */
    public function handle(LoanRepaymentDeductedEvent $event)
    {
        $paidPrincipal = $event->currentRepaymentDeduction->getPaidPrincipal(false) - $event->previousRepaymentDeduction->getPaidPrincipal(false);
        $paidInterest = $event->currentRepaymentDeduction->getPaidInterest(false) - $event->previousRepaymentDeduction->getPaidInterest(false);
        $paidFees = $event->currentRepaymentDeduction->getPaidFees(false) - $event->previousRepaymentDeduction->getPaidFees(false);

        $totalPaid = array_sum([$paidPrincipal, $paidInterest, $paidFees]);

        if ($totalPaid > 0) {
            LoanRepaymentCollection::create([
                'collected_at' => Carbon::now(),
                'loan_repayment_id' => $event->currentRepaymentDeduction->id,
                'amount' => $totalPaid,
            ]);
        }
    }
}
