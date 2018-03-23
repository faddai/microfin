<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/02/2017
 * Time: 11:36
 */

namespace App\Jobs;

use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Events\DepositAddedEvent;
use App\Exceptions\ClientDepositException;
use App\Exceptions\ClientTransactionException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;


class AddClientDepositJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ClientTransaction
     */
    private $transaction;

    /**
     * @var bool
     */
    private $nominal;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Client $client
     * @param bool $nominal A flag to determine whether transaction should be posted to the General Ledger
     * @throws ClientTransactionException
     */
    public function __construct(Request $request, Client $client, $nominal = false)
    {
        $this->request = $request;
        $this->client = $client;
        $narration = $request->filled('narration') ?
            $request->get('narration') : 'Client deposit - '. $this->client->account_number;
        $this->nominal = $nominal;

        if ($this->request->user()->branch === null) {
            throw new ClientTransactionException('You must be assigned to a branch to perform this transaction');
        }

        $this->transaction = new ClientTransaction([
            'user_id' => $request->user()->id,
            'narration' => $narration,
            'branch_id' => $request->user()->branch->id,
            'uuid' => $request->get('uuid', Uuid::uuid4()->toString()),
            'value_date' => $request->get('value_date', Carbon::now()),
        ]);
    }

    /**
     * Execute the job.
     * @return ClientTransaction
     * @throws \App\Exceptions\ClientDepositException
     */
    public function handle(): ClientTransaction
    {
        logger('Add a Client Deposit', ['request' => $this->request->all()]);

        return DB::transaction(function () {

            if (! $this->isValidCreditTransaction()) {
                throw new ClientDepositException('Invalid amount specified for the transaction');
            }

            $this->creditClientAccountWithDepositAmount();

            $deposit = $this->saveDepositTransaction();

            // don't fire event if this is a nominal entry because
            // the event has a listener that posts ledger entries
            ! $this->nominal && event(new DepositAddedEvent($deposit));

            return $deposit;
        });
    }

    /**
     * @throws ClientDepositException
     */
    private function creditClientAccountWithDepositAmount()
    {
        $this->client->increment('account_balance', $this->request->get('cr'));
    }

    /**
     * @return ClientTransaction
     */
    private function saveDepositTransaction(): ClientTransaction
    {
        foreach ($this->transaction->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->transaction->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->transaction->client_id = $this->client->id;

        $this->transaction->save();

        return $this->transaction;
    }

    /**
     * @return bool
     */
    private function isValidCreditTransaction()
    {
        return $this->request->filled('cr') && $this->request->get('cr') > 0;
    }
}