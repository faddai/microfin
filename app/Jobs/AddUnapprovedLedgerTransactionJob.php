<?php

namespace App\Jobs;

use App\Entities\Accounting\UnapprovedLedgerTransaction;
use Illuminate\Http\Request;

class AddUnapprovedLedgerTransactionJob
{
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
        $this->transaction = new UnapprovedLedgerTransaction([
            'user_id' => $this->request->user()->id,
            'branch_id' => $this->request->user()->branch->id,
        ]);

    }

    /**
     * Execute the job.
     *
     * @return UnapprovedLedgerTransaction
     */
    public function handle()
    {
        foreach ($this->transaction->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->transaction->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->transaction->save();

        return $this->transaction;
    }
}
