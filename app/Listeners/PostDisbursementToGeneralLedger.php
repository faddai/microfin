<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/03/2017
 * Time: 17:20
 */

namespace App\Listeners;


use App\Entities\Accounting\Ledger;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Events\LoanDisbursedEvent;
use App\Jobs\AddLedgerTransactionJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostDisbursementToGeneralLedger
{
    public function handle(LoanDisbursedEvent $event)
    {
        $loan = $event->loan;

        return DB::transaction(function () use ($loan) {
            return $this->postTransactionToLedger($loan);
        });
    }

    /**
     * When a loan is disbursed, entries are posted to the
     * 1. The ledger designated to the Loan Product to receive Repayments
     * 2. The Current Account
     * 3. The Income ledger of each of the upfront fees (if any) applied to the loan
     *
     * @param Loan $loan
     * @return mixed
     */
    private function postTransactionToLedger(Loan $loan)
    {
        $narration = 'Loan disbursed - ' . $loan->number;

        $entries = collect([
            [
                'dr' => $loan->getPrincipalAmount(false),
                // Ledger to collect proceeds depending on loan product
                'ledger_id' => $loan->product->principalLedger->id,
                'narration' => $narration,
            ],
            [
                'cr' => $loan->getPrincipalAmount(false) - $loan->getUpfrontFees(false),
                'ledger_id' => Ledger::whereCode(Ledger::CURRENT_ACCOUNT_CODE)->first()->id, // Current Account
                'narration' => $narration,
            ],
        ]);

        // add entries for individual upfront fees
        $loan->fees
            ->filter(function (Fee $fee) {
                return $fee->pivot->is_paid_upfront;
            })
            ->each(function (Fee $fee) use ($entries, $narration) {
                $entries->push([
                    'cr' => (float)$fee->pivot->amount,
                    'ledger_id' => $fee->incomeLedger->id,
                    'narration' => $narration. ' - '. $fee->name,
                ]);
            });

        return dispatch_now(new AddLedgerTransactionJob(new Request([
            'user_id' => $loan->disbursedBy->id,
            'branch_id' => $loan->disbursedBy->branch->id,
            'loan_id' => $loan->id,
            'entries' => $entries,
        ])));
    }
}