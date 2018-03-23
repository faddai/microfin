<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 23/02/2017
 * Time: 11:32
 */

namespace App\Jobs;

use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Events\ClientWithdrawalEvent;
use App\Exceptions\ClientTransactionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;


class AddClientWithdrawalJob
{
    /**
     * @var Request
     */
    private $request;
    
    /**
     * @var bool
     */
    private $nominal;

    /**
     * @var Client
     */
    private $client;

    /**
     * AddClientWithdrawalJob constructor.
     * @param Request $request
     * @param Client $client
     * @param bool $nominal
     * @throws ClientTransactionException
     */
    public function __construct(Request $request, Client $client, $nominal = false)
    {
        $this->request = $request;
        $this->client = $client;
        $narration = $request->filled('narration') ?
            $request->get('narration') : 'Client withdrawal - '. $this->client->account_number;
        $this->nominal = $nominal;

        if ($this->request->user()->branch === null) {
            throw new ClientTransactionException('You must be assigned to a branch to perform this transaction');
        }

        $this->transaction = new ClientTransaction([
            'user_id' => $request->user()->id,
            'branch_id' => $request->user()->branch->id,
            'narration' => $narration,
            'uuid' => $request->get('uuid', Uuid::uuid4()->toString()),
        ]);
    }

    /**
     * @return mixed
     * @throws \App\Exceptions\ClientTransactionException
     */
    public function handle()
    {
        return DB::transaction(function () {

            if (! $this->isValidDebitTransaction()) {
                throw new ClientTransactionException('Invalid withdraw, please check the amount you\'re trying to withdraw');
            }

            $this->debitWithdrawnAmountFromClientAccount();

            $this->saveWithdrawalTransaction();

            ! $this->nominal && event(new ClientWithdrawalEvent($this->transaction));

            return $this->transaction;
        });
    }

    /**
     * @return ClientTransaction
     * @throws ClientTransactionException
     */
    private function saveWithdrawalTransaction()
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
     * @return mixed
     */
    private function debitWithdrawnAmountFromClientAccount()
    {
        return $this->client->decrement('account_balance', $this->request->get('dr'));
    }

    /**
     * @return bool
     */
    private function isValidDebitTransaction()
    {
        return $this->request->filled('dr') &&
            $this->request->get('dr') > 0 &&
            $this->client->isDeductable($this->request->get('dr'));
    }
}