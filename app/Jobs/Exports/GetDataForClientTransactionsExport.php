<?php

namespace App\Jobs\Exports;

use App\Entities\Client;
use App\Entities\ClientTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetDataForClientTransactionsExport
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
     * Create a new job instance.
     *
     * @param Request $request
     * @param Client $client
     */
    public function __construct(Request $request, Client $client)
    {
        $this->request = $request;
        $this->client = $client;
    }

    /**
     * Execute the job.
     *
     * @return Collection
     */
    public function handle()
    {
        $balance = 0;

        $transactions = $this->client->transactions->map(function (ClientTransaction $transaction) use (&$balance) {

            $balance += $transaction->cr > 0 ? $transaction->cr : $transaction->dr * -1;

            return [
                'Value Date' => $transaction->value_date ? $transaction->value_date->format(config('microfin.dateFormat')) : 'n/a',
                'Narration' => $transaction->narration,
                'Dr' => number_format($transaction->dr, 2),
                'Cr' => number_format($transaction->cr, 2),
                'Balance' => number_format($balance, 2),
            ];
        });

        $transactions->meta = collect([
            'Customer Name' => $this->client->getFullName(),
            'Customer Number' => '\''. $this->client->account_number, // make a number appear as string so it doesn't get truncated
            'Customer Address' => $this->client->address,
            'From Date' => '"'. $transactions->first()['Value Date'] .'"',
            'To Date' => '"'. $transactions->last()['Value Date'] .'"',
        ]);

        return $transactions;
    }
}
