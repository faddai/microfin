<?php

use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\LoanStatement;
use App\Entities\LoanStatementEntry;
use App\Entities\Tenure;
use App\Jobs\AddClientDepositJob;
use App\Jobs\AddClientWithdrawalJob;
use App\Jobs\AddLoanJob;
use App\Jobs\AddLoanStatementEntryJob;
use App\Jobs\ApproveLoanJob;
use App\Jobs\DisburseLoanJob;
use Carbon\Carbon;
use Tests\TestCase;

class AddLoanStatementEntryJobTest extends TestCase
{
    public function test_can_add_an_entry_to_a_loan_statement()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(factory(Loan::class, 'staff')->make()->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);

        $this->request->replace([
            'dr' => 2000,
            'narration' => 'Loan repayment',
        ]);

        $entry = $this->dispatch(new AddLoanStatementEntryJob($this->request, $loan));

        self::assertInstanceOf(LoanStatementEntry::class, $entry);
        self::assertEquals(2000, $entry->dr);
        self::assertEquals(0, $entry->cr);
        self::assertEquals('Loan repayment', $entry->narration);
        self::assertEquals($loan->id, $entry->statement->loan->id);
    }

    /**
     * @todo fix test
     */
    public function _test_can_record_transactions_that_happen_on_a_loan_without_fees()
    {
        // create and disburse a loan
        // withdraw the disbursed amount from Client's account
        // post interest and or fees to loan statement
        // trigger a deposit so that repayment deduction can be effected

        $this->setAuthenticatedUserForRequest();

        $disbursedAt = Carbon::today()->subWeekdays(24 * 5);

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'amount' => 10000,
                    'rate' => 5,
                    'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->request->replace(['disbursed_at' => $disbursedAt, 'approved_at' => $disbursedAt]);

        $this->dispatch(new DisburseLoanJob($this->request, $this->dispatch(new ApproveLoanJob($this->request, $loan))));

        // Withdraw money from Client's account
        $this->request->replace(factory(ClientTransaction::class)->make(['dr' => 10000])->toArray());

        $this->dispatch(new AddClientWithdrawalJob($this->request, $loan->client));

        $loan = $loan->fresh();

        // loan disbursal entries
        self::assertEquals(10000, $loan->amount);
        self::assertInstanceOf(LoanStatement::class, $loan->statement);
        self::assertCount(4, $loan->statement->entries);
        self::assertEquals(10000, $loan->statement->entries->first()->dr);
        self::assertEquals($disbursedAt, $loan->statement->entries->first()->value_date);
        self::assertEquals($disbursedAt, $loan->statement->entries->first()->created_at);
        self::assertEquals(-10000, $loan->statement->entries->first()->balance);

        // loan was disbursed in the past so, let's play a little catchup
        $this->artisan('microfin:recalibrate-missed-deductions');

        // post accrued interest to loan statement
        $this->artisan('microfin:post-receivables-to-loan-statement');

        // add a deposit to trigger a deduction
        $this->request->replace(factory(ClientTransaction::class)->make(['cr' => 2400])->toArray());

        $this->dispatch(new AddClientDepositJob($this->request, $loan->client));

        $loan = Loan::first();

        // repayment deduction entries
        self::assertEquals(2500, $loan->schedule->first()->amount);
        self::assertCount(5, $loan->statement->entries);
        self::assertEquals(500, $loan->statement->entries->get(1)->dr);
        self::assertEquals(2400, $loan->statement->entries->get(4)->cr);
    }

    public function test_can_post_transactions_to_the_loan_statement_for_loans_with_charged_fees()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursedAt = Carbon::parse('Oct 10, 2016');

        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'amount' => 18000,
                    'rate' => 8.5,
                    'tenure_id' => Tenure::whereNumberOfMonths(6)->first()->id,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                    'fees' => collect([
                        Fee::whereName(Fee::ADMINISTRATION)->first()->fill(['rate' => 5]),
                        Fee::whereName(Fee::ARRANGEMENT)->first()->fill(['rate' => 7]),
                        Fee::whereName(Fee::DISBURSEMENT)->first()->fill(['rate' => 5]),
                        Fee::whereName(Fee::PROCESSING)->first()->fill(['rate' => 5]),
                    ])->toArray(),
                    'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->request->replace(['disbursed_at' => $disbursedAt, 'approved_at' => $disbursedAt]);

        $this->dispatch(new DisburseLoanJob($this->request, $this->dispatch(new ApproveLoanJob($this->request, $loan))));

        // Withdraw whatever remains in Client's account after upfront fees deductions
        $this->request->replace(
            factory(ClientTransaction::class)->make(['dr' => $loan->client->getAccountBalance(false)])->toArray()
        );

        $this->dispatch(new AddClientWithdrawalJob($this->request, $loan->client));

        $loan = $loan->fresh();

        // loan disbursal entries
        self::assertEquals(18000, $loan->amount);
        self::assertInstanceOf(LoanStatement::class, $loan->statement);
        self::assertCount(25, $loan->statement->entries);
        self::assertEquals(18000, $loan->statement->entries->first()->dr);
        self::assertEquals($disbursedAt, $loan->statement->entries->first()->value_date);
        self::assertEquals($disbursedAt, $loan->statement->entries->first()->created_at);
        self::assertEquals(-18000, $loan->statement->entries->first()->balance);

        // loan was disbursed in the past so, let's play a little catchup
        $this->artisan('microfin:recalibrate-missed-deductions');

        // post accrued interest and fees to loan statement
        $this->artisan('microfin:post-receivables-to-loan-statement', ['--include-matured-loans' => true]);

        $loan = $loan->fresh();

        self::assertEquals(1530, $loan->statement->entries->get(1)->dr); // accrued interest
        self::assertEquals(-19530, $loan->statement->entries->get(1)->balance);
        self::assertEquals(210, $loan->statement->entries->get(2)->dr); // arrangement fee due
        self::assertEquals(-19740, $loan->statement->entries->get(2)->balance);
        self::assertEquals(150, $loan->statement->entries->get(3)->dr); // arrangement fee due
        self::assertEquals(-19890, $loan->statement->entries->get(3)->balance);
        self::assertEquals(150, $loan->statement->entries->get(4)->dr); // arrangement fee due
        self::assertEquals(-20040, $loan->statement->entries->get(4)->balance);

        self::assertEquals(1324.05, $loan->statement->entries->get(5)->dr, '', 0.1); // accrued interest
        self::assertEquals(-21364.05, $loan->statement->entries->get(5)->balance, '', 0.1);
        self::assertEquals(210, $loan->statement->entries->get(6)->dr); // arrangement fee due
        self::assertEquals(-21574.05, $loan->statement->entries->get(6)->balance, '', 0.1);
        self::assertEquals(150, $loan->statement->entries->get(7)->dr); // arrangement fee due
        self::assertEquals(-21724.05, $loan->statement->entries->get(7)->balance, '', 0.1);
        self::assertEquals(150, $loan->statement->entries->get(8)->dr); // arrangement fee due
        self::assertEquals(-21874.05, $loan->statement->entries->get(8)->balance, '', 0.1);
   }

    public function test_that_loan_statement_entries_are_not_posted_multiple_times_on_a_due_date()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->states('approved', 'disbursed')
                ->make(['disbursed_at' => Carbon::today()->subWeekdays(24)])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertTrue($loan->isDisbursed());

        // run command 3x but expect only the first one to actually post an entry
        // Posting an entry to the loan statement should be idempotent
        collect(range(1, 3))
            ->each(function () {
                $this->artisan('microfin:post-receivables-to-loan-statement');
            });

        self::assertCount(1, LoanStatementEntry::all());
    }

}
