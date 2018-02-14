<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 07/04/2017
 * Time: 13:54
 */

namespace App\Listeners;


use App\Entities\ClientTransaction;
use App\Events\LoanRepaymentDeductedEvent;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class PostDeductedLoanRepaymentAmountToClientTransactions
{
    /**
     * @param LoanRepaymentDeductedEvent $event
     * @return ClientTransaction|bool
     */
    public function handle(LoanRepaymentDeductedEvent $event)
    {
        $prevRepayment = $event->previousRepaymentDeduction;
        $repayment = $event->currentRepaymentDeduction;

        $principalPaid = $repayment->getPaidPrincipal(false) - $prevRepayment->getPaidPrincipal(false);
        $interestPaid = $repayment->getPaidInterest(false) - $prevRepayment->getPaidInterest(false);
        $feesPaid = $repayment->getPaidFees(false) - $prevRepayment->getPaidFees(false);

        $amountDeducted = array_sum([$principalPaid, $interestPaid, $feesPaid]);

        return $amountDeducted > 0 && ClientTransaction::create([
            'dr' => $amountDeducted,
            'narration' => 'Loan repayment - '. $repayment->loan->number,
            'uuid' => Uuid::uuid4()->toString(),
            'client_id' => $repayment->loan->client->id,
            'value_date' => Carbon::now(),
        ]);
    }

}