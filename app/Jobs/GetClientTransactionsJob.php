<?php

namespace App\Jobs;

use Illuminate\Http\Request;
use App\Entities\ClientTransaction;

class GetClientTransactionsJob
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
    }

    /**
     * Execute the job.
     *
     *
     */
    public function handle()
    {
        return ClientTransaction::with('client.clientable', 'ledger')
            ->where('branch_id', $this->request->user()->branch->id)
            ->latest()
            ->paginate(config('microfin.limit'));
    }
}
