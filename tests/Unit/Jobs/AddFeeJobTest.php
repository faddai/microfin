<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/03/2017
 * Time: 02:02
 */

use App\Entities\Fee;
use App\Jobs\AddFeeJob;
use Setup\FeesTableSeeder;
use Tests\TestCase;


class AddFeeJobTest extends TestCase
{
    public function test_can_save_a_fee()
    {
        $this->request->merge([
            'rate' => 3,
            'name' => faker()->name,
            'is_paid_upfront' => 1
        ]);

        $fee = $this->dispatch(new AddFeeJob($this->request));

        self::assertInstanceOf(Fee::class, $fee);
        self::assertTrue($fee->isPaidUpfront());
        self::assertNotEmpty($fee->name);
        self::assertNotEmpty($fee->rate);
    }

    public function test_can_update_a_fee()
    {
        $fee = factory(Fee::class)->create(['rate' => 1.4, 'is_paid_upfront' => 0]);

        $this->request->merge([
            'rate' => 4,
            'is_paid_upfront' => 1,
            'type' => Fee::PERCENTAGE
        ]);

        $fee = $this->dispatch(new AddFeeJob($this->request, $fee));

        self::assertEquals(4, $fee->rate);
        self::assertTrue($fee->is_paid_upfront);
        self::assertNotNull($fee->type);
    }

    public function test_can_update_a_fee_that_was_previously_paid_upfront_to_be_otherwise()
    {
        $fee = factory(Fee::class)->create(['is_paid_upfront' => 1]);

        self::assertTrue($fee->isPaidUpfront());

        $fee = $this->dispatch(new AddFeeJob($this->request, $fee));

        self::assertFalse($fee->isPaidUpfront());
    }

    public function test_can_add_fee_with_a_fixed_amount()
    {
        $this->request->merge(factory(Fee::class)->make(['type' => Fee::FIXED, 'rate' => 2200])->toArray());

        $fee = $this->dispatch(new AddFeeJob($this->request));

        self::assertEquals(Fee::FIXED, $fee->type);
        self::assertEquals(2200, $fee->rate);
    }

    public function test_can_save_receivable_ledger_for_all_fees()
    {
        $this->seed(FeesTableSeeder::class);

        Fee::all()->each(function (Fee $fee) {
            self::assertNotNull($fee->receivableLedger);
            self::assertNotNull($fee->incomeLedger);
        });
    }
}