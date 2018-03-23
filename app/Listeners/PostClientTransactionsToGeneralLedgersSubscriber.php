<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/02/2017
 * Time: 11:36
 */

namespace App\Listeners;

use App\Entities\Accounting\Ledger;
use App\Events\ClientWithdrawalEvent;
use App\Events\DepositAddedEvent;
use App\Jobs\AddLedgerTransactionJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;


/**
 * Class PostClientTransactionsToGeneralLedgersSubscriber
 *
 * This is responsible for posting all Client Transactions (deposit, withdrawal)
 * that are not nominal to the Current Account Ledger and the selected Ledger
 *
 * @package App\Listeners
 */
class PostClientTransactionsToGeneralLedgersSubscriber
{

    use DispatchesJobs;

    /**
     * Post ledger entry for a withdrawal transaction
     *
     * @param $event
     */
    public function onClientWithdrawal($event)
    {
        $request = new Request([
            'entries' => [
                [
                    'cr' => $event->transaction->dr,
                    'narration' => $event->transaction->narration,
                    'ledger_id' => $event->transaction->ledger_id,
                ],
                [
                    'dr' => $event->transaction->dr,
                    'narration' => $event->transaction->narration,

                    // @todo add ability to configure this ledger
                    // for now, going to hard code a ledger from the short term liability category
                    'ledger_id' => Ledger::where('code', Ledger::CURRENT_ACCOUNT_CODE)->first()->id,
                ]
            ]
        ]);

        $request->merge($this->getTransactionDetails($event));

        // entries posted when there is a withdrawal
        $this->dispatch(new AddLedgerTransactionJob($request));
    }

    /**
     * Post ledger entry for a deposit transaction
     *
     * @param $event
     */
    public function onDepositAdded($event)
    {
        $request = new Request([
            'entries' => [
                [
                    'dr' => $event->transaction->cr,
                    'narration' => $event->transaction->narration,
                    'ledger_id' => $event->transaction->ledger_id,
                ],
                [
                    'cr' => $event->transaction->cr,
                    'narration' => $event->transaction->narration,

                    // @todo add ability to configure this ledger
                    // for now, going to hard code a ledger from the short term liability category
                    'ledger_id' => Ledger::where('code', Ledger::CURRENT_ACCOUNT_CODE)->first()->id,
                ]
            ]
        ]);

        $request->merge($this->getTransactionDetails($event));

        $this->dispatch(new AddLedgerTransactionJob($request));
    }

    /**
     * @param $event
     * @return array
     */
    private function getTransactionDetails($event)
    {
        return [
            'user_id' => $event->transaction->user_id,
            'branch_id' => $event->transaction->branch_id,
            'value_date' => $event->transaction->value_date,
        ];
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            ClientWithdrawalEvent::class,
            'App\Listeners\PostClientTransactionsToGeneralLedgersSubscriber@onClientWithdrawal'
        );

        $events->listen(
            DepositAddedEvent::class,
            'App\Listeners\PostClientTransactionsToGeneralLedgersSubscriber@onDepositAdded'
        );
    }
}