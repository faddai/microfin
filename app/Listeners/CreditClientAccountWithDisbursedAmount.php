<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/03/2017
 * Time: 17:21
 */

namespace App\Listeners;

use App\Entities\Accounting\Ledger;
use App\Entities\Loan;
use App\Events\LoanDisbursedEvent;
use App\Jobs\AddClientDepositJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditClientAccountWithDisbursedAmount
{
    /**
     * Handle the event.
     *
     * @param  LoanDisbursedEvent $event
     * @return mixed
     * @throws \App\Exceptions\ClientTransactionException
     */
    public function handle(LoanDisbursedEvent $event)
    {
        $loan = $event->loan;

        DB::transaction(function () use ($loan) {
            // add client transaction
            return $this->addClientDepositTransaction($loan);
        });
    }

    /**
     * @param Loan $loan
     * @return mixed
     * @throws \App\Exceptions\ClientTransactionException
     */
    private function addClientDepositTransaction(Loan $loan)
    {
        $request = new Request([
            'cr' => $loan->getPrincipalAmount(false) - $loan->getUpfrontFees(false),
            'narration' => sprintf('Loan disbursed - %s', $loan->number),
            'client_id' => $loan->client->id,
            'ledger_id' => Ledger::whereCode(Ledger::CURRENT_ACCOUNT_CODE)->first()->id,
            'user_id' => $loan->disbursedBy->id,
            'value_date' => $loan->disbursed_at,
        ]);

        $request->setUserResolver(function () use ($loan) { return $loan->disbursedBy; });

        return dispatch_now(new AddClientDepositJob($request, $loan->client, true));
    }
}