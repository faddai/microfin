<?php

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerEntry;
use App\Entities\Accounting\LedgerTransaction;
use App\Jobs\AddLedgerTransactionJob;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;


class AddLedgerTransactionJobTest extends TestCase
{

    public function test_able_to_create_a_transaction_with_a_valid_transaction_id()
    {
        $valueDate = Carbon::parse('2 months ago')->format('Y-m-d');

        $this->request->merge(
            factory(LedgerTransaction::class)
                ->make([
                    'uuid' => '',
                    'value_date' => $valueDate,
                    'entries' => [
                        factory(LedgerEntry::class)->make(['dr' => 200])->toArray(),
                        factory(LedgerEntry::class)->make(['cr' => 200])->toArray(),
                    ]
                ])
                ->toArray()
        );

        $transaction = $this->dispatchNow(new AddLedgerTransactionJob($this->request));

        self::assertInstanceOf(LedgerTransaction::class, $transaction);
        self::assertNotNull($transaction->uuid);
        self::assertTrue(Uuid::isValid($transaction->uuid));
        self::assertNotNull($transaction->branch);
        self::assertEquals($valueDate, $transaction->value_date->format('Y-m-d'));
    }

    public function test_able_to_save_the_user_who_created_the_transaction()
    {
        $this->request->merge(factory(LedgerTransaction::class)->make([
            'entries' => [
                factory(LedgerEntry::class)->make(['dr' => 200])->toArray(),
                factory(LedgerEntry::class)->make(['cr' => 200])->toArray(),
            ]
        ])->toArray());

        $transaction = $this->dispatchNow(new AddLedgerTransactionJob($this->request));

        self::assertNotNull($transaction->user);
    }

    public function test_able_to_save_branch_where_the_transaction_originated_from()
    {
        $this->request->merge(factory(LedgerTransaction::class)->make([
            'entries' => [
                factory(LedgerEntry::class)->make(['dr' => 200])->toArray(),
                factory(LedgerEntry::class)->make(['cr' => 200])->toArray(),
            ]
        ])->toArray());

        $transaction = $this->dispatchNow(new AddLedgerTransactionJob($this->request));

        self::assertNotNull($transaction->branch);
    }

    /**
     * create a transaction involving 2 ledgers
     */
    public function test_able_to_post_ledger_entries_as_part_of_a_transaction() {

        $txn = factory(LedgerTransaction::class)->make();

        $this->request->merge(array_merge($txn->toArray(), [
                'entries' => [
                    factory(LedgerEntry::class)->make(['dr' => 2000])->toArray(),
                    factory(LedgerEntry::class)->make([
                        'cr' => 2000,
                        'ledger_id' => Ledger::firstOrCreate(['code' => 4002])->id,
                    ])->toArray()
                ]
            ]
        ));

        $transaction = $this->dispatchNow(new AddLedgerTransactionJob($this->request));

        self::assertInstanceOf(LedgerTransaction::class, $transaction);
        self::assertInstanceOf(LedgerEntry::class, $transaction->entries->first());
        self::assertCount(2, $transaction->entries);
        self::assertEquals($txn->uuid, $transaction->uuid);
    }

    /**
     * @expectedException App\Exceptions\UnbalancedLedgerEntryException
     */
    public function test_throws_an_exception_for_unbalanced_entries()
    {
        $this->request->merge([
            factory(LedgerTransaction::class)->make()->toArray(),
            'entries' => [
                factory(LedgerEntry::class)->make(['dr' => 2000])->toArray(),
                factory(LedgerEntry::class)->make(['dr' => 100])->toArray(),
                factory(LedgerEntry::class)->make([
                    'cr' => 2000,
                    'ledger_id' => Ledger::whereCode(4002)->first()->id,
                ])->toArray()
            ]
        ]);

        $transaction = $this->dispatchNow(new AddLedgerTransactionJob($this->request));

        self::assertInstanceOf(LedgerTransaction::class, $transaction);
    }
}
