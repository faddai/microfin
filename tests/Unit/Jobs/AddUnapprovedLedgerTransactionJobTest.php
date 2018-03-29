<?php

use App\Entities\Accounting\LedgerEntry;
use App\Entities\Accounting\UnapprovedLedgerTransaction;
use App\Jobs\AddUnapprovedLedgerTransactionJob;
use Tests\TestCase;

class AddUnapprovedLedgerTransactionJobTest extends TestCase
{
    public function test_can_add_an_unapproved_ledger_transaction()
    {
        $this->setAuthenticatedUserForRequest();

        $entries = [
            factory(LedgerEntry::class)->make(['cr' => 1000])->toArray(),
            factory(LedgerEntry::class)->make(['dr' => 1000])->toArray(),
        ];

        $this->request->merge(compact('entries'));

        $transaction = $this->dispatch(new AddUnapprovedLedgerTransactionJob($this->request));

        self::assertInstanceOf(UnapprovedLedgerTransaction::class, $transaction);
    }
}
