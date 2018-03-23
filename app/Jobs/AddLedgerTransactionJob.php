<?php

namespace App\Jobs;

use App\Entities\Accounting\LedgerTransaction;
use App\Exceptions\UnbalancedLedgerEntryException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;


class AddLedgerTransactionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, DispatchesJobs;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var LedgerTransaction
     */
    private $transaction;

    /**
     * @var Collection
     */
    private $ledgerEntries;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->ledgerEntries = collect($this->request->get('entries', []));
        $this->transaction = new LedgerTransaction([
            'user_id' => $this->request->user()->id ?? $this->request->get('user_id'),
            'branch_id' => $this->request->user()->branch->id ?? $this->request->get('branch_id'),
            'loan_id' => $this->request->get('loan_id'),
        ]);
    }

    /**
     * Execute the job.
     *
     * @return LedgerTransaction
     * @throws \App\Exceptions\UnbalancedLedgerEntryException
     */
    public function handle()
    {
        logger('Add a Ledger transaction', ['request' => $this->request->all()]);

        return DB::transaction(function () {

            $this->createTransaction();

            $this->ledgerEntries->each(function ($entry) {

                $entry = collect($entry);

                $this->dispatch(new AddLedgerEntryJob(new Request([
                    'transaction_id' => $this->transaction->uuid,
                    'dr' => $entry->get('dr', 0),
                    'cr' => $entry->get('cr', 0),
                    'ledger_id' => $entry->get('ledger_id'),
                    'narration' => $entry->get('narration')
                ])));
            });

            return $this->transaction;
        });
    }

    /**
     * @return LedgerTransaction
     * @throws \App\Exceptions\UnbalancedLedgerEntryException
     */
    private function createTransaction()
    {
        if (! $this->isABalancedTransaction()) {
            throw new UnbalancedLedgerEntryException(
                sprintf('[Dr = %s, Cr = %s] Ledger entries: %s',
                    $this->ledgerEntries->sum('dr'),
                    $this->ledgerEntries->sum('cr'),
                    $this->ledgerEntries->toJson()
                )
            );
        }

        foreach ($this->transaction->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->transaction->{$fillable} = $this->request->get($fillable);
            }
        }

        if (! $this->request->filled('uuid')) {
            $this->transaction->uuid = Uuid::uuid4()->toString();
        }

        $this->transaction->save();

        return $this->transaction;
    }

    /**
     * Given a collection of LedgerEntry, determine the entries balance out
     * Don't compare floats directly (using ==), you're likely to bump into floating point mess
     *
     * @see http://floating-point-gui.de/errors/comparison/
     * @see http://php.net/manual/en/language.types.float.php#115850 (current implementation)
     * @return bool
     */
    private function isABalancedTransaction()
    {
        $dr = number_format($this->ledgerEntries->sum('dr'), 2);
        $cr = number_format($this->ledgerEntries->sum('cr'), 2);

        return $dr === $cr;
    }

}
