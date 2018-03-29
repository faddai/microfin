<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 07/04/2017
 * Time: 18:46
 */

use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\Loan;
use App\Entities\Tenure;
use App\Jobs\AutomatedLoanRepaymentJob;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use Tests\TestCase;

class PostDeductedLoanRepaymentAmountToClientTransactionsTest extends TestCase
{
    public function test_can_post_a_nominal_entry_in_client_transaction_after_repayment_deduction()
    {
        $loan = factory(Loan::class, 'staff')
            ->states('approved', 'disbursed')
            ->create([
                'amount' => 10000,
                'rate' => 5,
                'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
                'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 3000])->id,
            ]);

        $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        self::assertCount(1, Loan::running());
        self::assertEquals(2500, $loan->schedule->first()->amount);

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        self::assertInstanceOf(ClientTransaction::class, $loan->client->transactions->first());
        self::assertEquals(2500, $loan->client->transactions->first()->dr);
        self::assertEquals(500, $loan->client->getAccountBalance(false));
    }

}
