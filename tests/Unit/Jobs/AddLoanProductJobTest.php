<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 26/02/2017
 * Time: 16:39
 */

use App\Entities\LoanProduct;
use App\Jobs\AddLoanProductJob;
use Tests\TestCase;


class AddLoanProductJobTest extends TestCase
{
    public function test_can_add_a_loan_product()
    {
        $this->request->merge(
            factory(LoanProduct::class)->make(['name' => 'Staff Loan', 'code' => 2312])->toArray()
        );

        $product = $this->dispatch(new AddLoanProductJob($this->request));

        self::assertInstanceOf(LoanProduct::class, $product);
    }

    public function test_can_update_an_existing_loan_product()
    {
        $product = factory(LoanProduct::class)->create(['name' => 'Gentle Borrowers', 'code' => 10210]);

        self::assertInstanceOf(LoanProduct::class, $product);
        self::assertEquals('Gentle Borrowers', $product->name);
        self::assertEquals(10210, $product->code);

        $this->request->merge([
            'name' => 'Government Officials',
            'code' => 11122
        ]);

        $product = $this->dispatch(new AddLoanProductJob($this->request, $product));

        self::assertInstanceOf(LoanProduct::class, $product);
        self::assertNotEquals('Gentle Borrowers', $product->name);
        self::assertNotEquals(10210, $product->code);
    }
}
