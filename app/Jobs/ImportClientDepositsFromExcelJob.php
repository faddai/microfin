<?php

namespace App\Jobs;

use App\Entities\Accounting\Ledger;
use App\Entities\Branch;
use App\Entities\Client;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Collections\CellCollection;
use Maatwebsite\Excel\Collections\RowCollection;
use Maatwebsite\Excel\Facades\Excel;

class ImportClientDepositsFromExcelJob
{

    use DispatchesJobs;

    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \App\Exceptions\ClientTransactionException
     * @throws \Exception
     */
    public function handle()
    {
        DB::transaction(function () {
            Excel::load($this->request->get('file'), function ($reader) {
                $workbook = $reader->get();

                $reader->formatDates(false);

                $workbook->each(function (RowCollection $sheet) {

                    $sheet->map(function (CellCollection $transaction) {
                        $client = Client::whereAccountNumber(trim(str_replace('\'', '', $transaction->customer_number)))
                            ->firstOrFail();

                        $ledger =  Ledger::whereCode(''. $transaction->ledger_code)->firstOrFail();

                        $txn = collect([
                            'branch_id' => $this->getBranchId($transaction),
                            'value_date' => $transaction->value_date,
                            'created_at' => $transaction->transaction_date,
                            'ledger_id' => $ledger->id,
                            'cr' => $transaction->amount,
                            'narration' => $transaction->narration,
                        ]);

                        $txn->client = $client;

                        return $txn;
                    })
                    ->each(function (Collection $transaction) {

                        $request = new Request($transaction->toArray());

                        $request->setUserResolver(function () {
                            return $this->request->user();
                        });

                        $this->dispatch(new AddClientDepositJob($request, $transaction->client));

                        logger('Transaction', $transaction->toArray());
                    });
                });
            });
        });
    }

    /**
     * @param $transaction
     * @return mixed
     * @throws \Exception
     */
    private function getBranchId($transaction)
    {
        $branch = Branch::whereCode($transaction->business_unit_code)->first() ?? $this->request->user()->branch;

        if (! $branch) {
            throw new \Exception('A transaction branch is missing for one or more transactions');
        }

        return $branch->id;
    }
}
