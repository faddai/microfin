<?php

namespace App\Jobs\Accounting;

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AddLedgerJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Ledger
     */
    private $ledger;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Ledger $ledger
     */
    public function __construct(Request $request, Ledger $ledger = null)
    {
        $this->request = $request;
        $this->ledger = $ledger ?? new Ledger;
    }

    /**
     * Execute the job.
     *
     * @return Ledger
     * @throws \Exception
     */
    public function handle()
    {
        return DB::transaction(function () {
            return $this->addOrUpdateAccount();
        });
    }

    /**
     * @return Ledger
     * @throws \Exception
     */
    private function addOrUpdateAccount()
    {
        if (! $this->ledger->exists && ! $this->request->filled('category_id')) {
            throw new \Exception('You must specify a ledger category');
        }

        foreach ($this->ledger->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->ledger->{$fillable} = $this->request->get($fillable);
            }
        }

        if (! $this->ledger->exists) {
            $this->ledger->code = $this->getLedgerCode();
        }

        $this->ledger->save();

        cache()->forget('ledgers');

        return $this->ledger;
    }

    /**
     * @return mixed
     */
    private function getLedgerCode()
    {
        $categoryId = $this->request->get('category_id');

        $category = LedgerCategory::with('ledgers')->findOrFail($categoryId);

        if ($lastLedger = $category->ledgers->last()) {
            return $lastLedger->code + 1;
        }

        return (int) $categoryId.'001';
    }
}
