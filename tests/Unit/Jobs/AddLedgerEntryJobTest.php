<?php

use App\Entities\Accounting\LedgerEntry;
use App\Jobs\AddLedgerEntryJob;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;


class AddLedgerEntryJobTest extends TestCase
{
    /**
     * @expectedException App\Exceptions\LedgerEntryException
     */
    public function test_can_throw_exception_for_attempting_to_add_entry_for_an_unspecified_transaction()
    {
        $this->request->merge(factory(LedgerEntry::class)->make()->toArray());

        $this->dispatch(new AddLedgerEntryJob($this->request));
    }

    /**
     * @expectedException App\Exceptions\LedgerEntryException
     */
    public function test_can_throw_exception_for_attempting_to_add_entry_for_an_invalid_transaction()
    {
        $this->request->merge(['transaction_id' => 'skjsd']);

        $this->request->merge(factory(LedgerEntry::class)->make()->toArray());

        $this->dispatch(new AddLedgerEntryJob($this->request));
    }

    public function test_save_a_valid_entry()
    {
        $this->request->merge(['transaction_id' => Uuid::uuid4()->toString()]);

        $this->request->merge(factory(LedgerEntry::class)->make(['cr' => 20])->toArray());

        $entry = $this->dispatch(new AddLedgerEntryJob($this->request));

        self::assertInstanceOf(LedgerEntry::class, $entry);
        self::assertEquals(0, $entry->dr);
        self::assertEquals(20, $entry->cr);
    }
}