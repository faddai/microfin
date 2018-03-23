<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 08/04/2017
 * Time: 19:51
 */

namespace App\Listeners;


use App\Events\LoanRepaymentDeductedEvent;
use App\Jobs\AddLoanStatementEntryJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class PostLoanRepaymentDeductionToLoanAccountStatement
{

    use DispatchesJobs;

    /**
     * When a loan repayment is deducted, record total amount paid as Credit
     * @param LoanRepaymentDeductedEvent $event
     * @return mixed
     */
    public function handle(LoanRepaymentDeductedEvent $event)
    {
        $paidPrincipal = $event->currentRepaymentDeduction->getPaidPrincipal(false) - $event->previousRepaymentDeduction->getPaidPrincipal(false);
        $paidInterest = $event->currentRepaymentDeduction->getPaidInterest(false) - $event->previousRepaymentDeduction->getPaidInterest(false);
        $paidFees = $event->currentRepaymentDeduction->getPaidFees(false) - $event->previousRepaymentDeduction->getPaidFees(false);

        $totalPaid = array_sum([$paidPrincipal, $paidInterest, $paidFees]);

        $request = new Request([
            'cr' => $totalPaid,
            'narration' => 'Loan installment receipt',
        ]);

        if ($totalPaid > 0) {
            logger('Add repayment deduction to loan statement', array_merge($request->all(), ['loan_id' => $event->currentRepaymentDeduction->loan->id]));

            return $this->dispatch(new AddLoanStatementEntryJob($request, $event->currentRepaymentDeduction->loan));
        }
    }
}