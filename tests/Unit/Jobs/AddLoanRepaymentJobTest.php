<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 1:49 PM
 */

use App\Entities\Client;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Jobs\AddLoanRepaymentJob;
use Tests\TestCase;


class AddLoanRepaymentJobTest extends TestCase
{
    /**
     * Add loan repayment to the loan schedule
     *
     * No actual payment is made in this case because there's no approved and disbursed loan
     */
    public function test_not_able_to_deduct_repayment_for_an_unapproved_loan()
    {
        $loan = factory(Loan::class)->create([
            'amount' => 3000,
            'rate' => 2.5,
            'client_id' => factory(Client::class)->create(['account_balance' => 4000])->id,
        ]);

        $this->request->merge(
            factory(LoanRepayment::class)->make(['loan_id' => $loan->id])->toArray()
        );

        $repayment = $this->dispatch(new AddLoanRepaymentJob($this->request));

        self::assertInstanceOf(LoanRepayment::class, $repayment);
        self::assertNull($repayment->repayment_timestamp);
        self::assertFalse($repayment->isFullyPaid());
    }
}