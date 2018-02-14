<?php

namespace App\Jobs\Exports;

use App\Entities\Loan;
use App\Entities\LoanStatementEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetDataForLoanStatementExport
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Loan
     */
    private $loan;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Loan $loan
     */
    public function __construct(Request $request, Loan $loan)
    {
        $this->request = $request;
        $this->loan = $loan;
    }

    /**
     * Execute the job.
     *
     * @return Collection
     */
    public function handle()
    {
        $loan = $this->loan->load('statement.entries');

        $balance = 0;

        $statement = $loan->statement->entries->map(function (LoanStatementEntry $entry, $i) use (&$balance) {

            $balance += array_sum([$entry->dr * -1, $entry->cr]);

            return [
                '#' => $i + 1,
                'Txn Date' => $entry->created_at ? $entry->created_at->format(config('microfin.dateFormat')) : 'n/a',
                'Value Date' => $entry->value_date ? $entry->value_date->format(config('microfin.dateFormat')) : 'n/a',
                'Narration' => $entry->narration,
                'Debit' => $entry->getDebitAmount(),
                'Credit' => $entry->getCreditAmount(),
                'Balance' => number_format($balance, 2),
            ];

        });

        $statement->meta = collect([
            'Customer Name' => $loan->client->getFullName(),
            'Customer Number' => '\''. $loan->client->account_number, // make a number appear as string so it doesn't get truncated
            'Loan Number' => '\''. $loan->number,
            'Maturity Date' => $loan->maturity_date ? '"'. $loan->maturity_date->format(config('microfin.dateFormat')) .'"' : 'n/a',
            'Balance' => '"'. number_format($loan->getBalance(false) * -1, 2) .'"',
            'From Date' => '"'. $statement->first()['Value Date'] .'"',
            'To Date' => '"'. $statement->last()['Value Date'] .'"',
        ]);

        return $statement;
    }
}
