<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/04/2017
 * Time: 16:41
 */

namespace App\Listeners;


use App\Entities\Accounting\Ledger;
use App\Entities\Fee;
use App\Entities\LoanRepayment;
use App\Events\LoanRepaymentDeductedEvent;
use App\Jobs\AddLedgerTransactionJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class PostRepaymentDeductionToGeneralLedger
{

    use DispatchesJobs;

    /**
     * @var float
     */
    private $principalPaid = 0.0;

    /**
     * @var float
     */
    private $interestPaid = 0.0;

    /**
     * @var float
     */
    private $feesPaid = 0.0;

    /**
     * @var LoanRepayment
     */
    private $previousDeduction;

    /**
     * @var LoanRepayment
     */
    private $currentDeduction;

    public function handle(LoanRepaymentDeductedEvent $event)
    {
        $this->previousDeduction = $event->previousRepaymentDeduction;
        $this->currentDeduction = $event->currentRepaymentDeduction;

        $this->principalPaid = $this->currentDeduction->getPaidPrincipal(false) - $this->previousDeduction->getPaidPrincipal(false);
        $this->interestPaid = $this->currentDeduction->getPaidInterest(false) - $this->previousDeduction->getPaidInterest(false);
        $this->feesPaid = $this->currentDeduction->getPaidFees(false) - $this->previousDeduction->getPaidFees(false);

        $this->anActualDeductionHasHappened() && $this->postLedgerTransaction();
    }


    /**
     * When a deduction happens, record interest, principal and fee in gl
     */
    private function postLedgerTransaction()
    {
        logger('Deduction payload:', [
            'principalPaid' => $this->principalPaid,
            'interestPaid' => $this->interestPaid,
            'feesPaid' => $this->feesPaid
        ]);

        $narration = 'Loan installment receipt - '. $this->currentDeduction->loan->number;
        $sumOfAlreadyPaidFeeAmount = 0;
        $entries = collect();

        $entries->push([
            'dr' => array_sum([$this->principalPaid, $this->interestPaid, $this->feesPaid]),
            'ledger_id' => Ledger::whereCode(Ledger::CURRENT_ACCOUNT_CODE)->first()->id,
            'narration' => $narration,
        ]);

        if ($this->interestPaid > 0) {
            $entries->push([
                'cr' => $this->interestPaid,
                // Ledger to collect interest income depending on loan product
                'ledger_id' => $this->currentDeduction->loan->product->interestReceivableLedger->id,
                'narration' => $narration .' - interest',
            ]);
        }

        if ($this->principalPaid > 0) {
            $entries->push([
                'cr' => $this->principalPaid,
                // Ledger to collect principal depending on loan product
                'ledger_id' => $this->currentDeduction->loan->product->principalLedger->id,
                'narration' => $narration .' - principal',
            ]);
        }

        // add entries for individual amortized fees
         $this->feesPaid > 0 && $this->currentDeduction->loan->fees
            ->reject(function (Fee $fee) use (&$sumOfAlreadyPaidFeeAmount) {
                return $fee->pivot->is_paid_upfront || $this->feeAmountIsAlreadyPaidInFull($fee, $sumOfAlreadyPaidFeeAmount);
            })
            ->each(function (Fee $fee) use (&$entries, $narration) {
                $feeDeducted = $this->getFeeAmountDeducted($fee);

                $entries->push([
                    // get the individual fee component for repayment
                    'cr' => $feeDeducted,
                    'ledger_id' => $fee->incomeLedger->id,
                    'narration' => $narration . ' - ' . $fee->name,
                ]);

                $this->feesPaid -= $feeDeducted;
            });

        $this->dispatch(new AddLedgerTransactionJob(new Request([
            'branch_id' => $this->currentDeduction->loan->createdBy->branch->id,
            'entries' => $entries,
            'loan_id' => $this->currentDeduction->loan->id
        ])));
    }

    /**
     * @param Fee $fee
     * @return float|int
     */
    private function getFeeAmountDeducted(Fee $fee)
    {
        $feeAmountOnRepayment = $fee->pivot->amount / $this->currentDeduction->loan->tenure->number_of_months;

        return $this->feesPaid > 0 && $feeAmountOnRepayment <= $this->feesPaid ? $feeAmountOnRepayment : $this->feesPaid;
    }

    /**
     * Avoid posting 0s to the GL
     * @return bool
     */
    private function anActualDeductionHasHappened()
    {
        return array_sum([$this->interestPaid, $this->principalPaid, $this->feesPaid]) > 0;
    }

    /**
     * Identify fees which have been paid from previous deduction(s). We don't want to post entries
     * for such fees multiple times.
     * @param Fee $fee
     * @param $sumOfAlreadyPaidFeeAmount
     * @return bool
     */
    private function feeAmountIsAlreadyPaidInFull(Fee $fee, &$sumOfAlreadyPaidFeeAmount): bool
    {
        $feeAmountOnRepayment = $fee->pivot->amount / $this->currentDeduction->loan->tenure->number_of_months;

        $sumOfAlreadyPaidFeeAmount = $feeAmountOnRepayment + $sumOfAlreadyPaidFeeAmount;

        return $this->previousDeduction->paid_fees >= $sumOfAlreadyPaidFeeAmount;
    }
}