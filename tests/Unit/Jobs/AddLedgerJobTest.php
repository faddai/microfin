<?php

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerCategory;
use App\Jobs\Accounting\AddLedgerCategoryJob;
use App\Jobs\Accounting\AddLedgerJob;
use Tests\TestCase;


class AddLedgerJobTest extends TestCase
{
    public function test_can_add_an_account()
    {
        $this->request->merge(factory(Ledger::class)->make(['name' => 'Staff salaries'])->toArray());

        $ledger = $this->dispatch(new AddLedgerJob($this->request));

        self::assertInstanceOf(Ledger::class, $ledger);
        self::assertEquals('Staff salaries', $ledger->name);
        self::assertNotNull($ledger->code);
    }

    public function test_can_generate_a_valid_code_for_newly_created_account()
    {
        $shareCapitalLedgerCategory = LedgerCategory::with('ledgers')->firstOrCreate(['name' => 'Share Capital']);

        self::assertEquals(1001, $shareCapitalLedgerCategory->ledgers->first()->code);

        $this->request->merge(
            factory(Ledger::class)->make(['category_id' => $shareCapitalLedgerCategory->id])->toArray()
        );

        $ledger = $this->dispatch(new AddLedgerJob($this->request));

        $shareCapitalLedgerCategory->load('ledgers');

        self::assertInstanceOf(Ledger::class, $ledger);
        self::assertEquals($shareCapitalLedgerCategory->id, $ledger->category->id);
    }

    /**
     * @expectedException \Exception
     */
    public function test_can_throw_an_error_if_no_ledger_category_is_provided()
    {
        $this->request->merge(['name' => 'Salaries']);

        $this->dispatch(new AddLedgerJob($this->request));
    }

    public function test_can_update_an_existing_ledger()
    {
        $ledger = factory(Ledger::class)->create(['name' => 'Salaries']);

        $this->request->merge(['name' => 'Employee welfare - Lusaka']);

        self::assertEquals('Salaries', $ledger->name);

        $this->dispatch(new AddLedgerJob($this->request, $ledger));

        self::assertEquals('Employee welfare - Lusaka', $ledger->name);
        self::assertEquals($ledger->category->id, $ledger->category->id);
    }

    public function test_get_code_for_ledger_with_a_newly_created_ledger_category()
    {
        $this->request->merge(['name' => 'Bank Drafts']);

        $category = $this->dispatch(new AddLedgerCategoryJob($this->request));

        $this->request->replace(['name' => 'Allowance for MD', 'category_id' => $category->id]);

        $ledger = $this->dispatch(new AddLedgerJob($this->request));

        self::assertEquals(9, $category->id);
        self::assertEquals(9001, $ledger->code);

        // create another ledger just to be sure
        $this->request->merge(['name' => faker()->name]);

        $ledger = $this->dispatch(new AddLedgerJob($this->request));

        self::assertEquals(9002, $ledger->code);
    }

    /**
     * @expectedException Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function test_cannot_add_a_ledger_with_a_non_existant_category()
    {
        $this->request->merge(['name' => faker()->name, 'category_id' => 90]);

        $this->dispatch(new AddLedgerJob($this->request));
    }

    public function test_can_indicate_whether_a_ledger_has_debit_or_credit_balance()
    {
        self::assertTrue(Ledger::whereCode(4001)->first()->is_left);
        self::assertTrue(Ledger::whereCode(8008)->first()->is_left);

        LedgerCategory::whereType(LedgerCategory::CAPITAL)
            ->first()
            ->ledgers
            ->each(function (Ledger $ledger) {
                self::assertTrue($ledger->isCreditAccount());
                self::assertFalse($ledger->isDebitAccount());
            });
    }
}
