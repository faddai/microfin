<?php

use App\Entities\Client;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\LoanStatementEntry;
use App\Entities\Tenure;
use App\Jobs\AddLoanJob;
use App\Jobs\ApproveLoanJob;
use App\Jobs\DisburseLoanJob;
use Carbon\Carbon;
use Tests\TestCase;


class DisburseLoanJobTest extends TestCase
{
    public function test_loan_is_able_to_be_disbursed()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(factory(Loan::class, 'customer')->make()->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->dispatch(new ApproveLoanJob($this->request, $loan));

        self::assertEquals(Loan::APPROVED, $loan->fresh()->status);

        $this->dispatch(new DisburseLoanJob($this->request, $loan));

        $loan = $loan->fresh();

        self::assertEquals(Loan::DISBURSED, $loan->status);
        self::assertNotNull($loan->disburser_id);
        self::assertNotNull($loan->disbursed_at);
        self::assertNotNull($loan->maturity_date);

        // only loan disbursal entry is posted to the loan statement
        self::assertCount(1, LoanStatementEntry::all());
    }

    public function test_can_add_loan_disbursal_remarks_when_disbursing_a_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(factory(Loan::class, 'customer')->make([
            'disbursal_remarks' => faker()->sentence
        ])->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->approveAndDisburseLoan($loan);

        self::assertNotNull($loan->disbursal_remarks);
    }

    /**
     * @expectedException App\Exceptions\LoanDisbursalException
     */
    public function test_cannot_be_disbursed_until_it_is_approved()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = factory(Loan::class)->create();

        $this->dispatch(new DisburseLoanJob($this->request, $loan));
    }

    public function test_can_deduct_upfront_loan_fees_during_disbursal()
    {
        $this->setAuthenticatedUserForRequest();

        $fees = collect([
            Fee::whereName(Fee::ARRANGEMENT)->first()->fill(['rate' => 1.5]),
            Fee::whereName(Fee::ADMINISTRATION)->first()->fill(['rate' => 2.5]),
            Fee::whereName(Fee::PROCESSING)->first()->fill(['rate' => 2])
        ])->toArray();

        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'amount' => 10000,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                    'fees' => $fees,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(150.0, $loan->fees->first()->pivot->amount);
        self::assertFalse((bool) $loan->fees->first()->pivot->is_paid_upfront);
        self::assertTrue((bool) $loan->fees->get(1)->pivot->is_paid_upfront);
        self::assertEquals(200.0, $loan->fees->last()->pivot->amount);
        self::assertEquals(250, $loan->getUpfrontFees(false));

        $this->approveAndDisburseLoan($loan);

        self::assertEquals(9750, $loan->client->getAccountBalance(false));
        self::assertEquals(-10000, LoanStatementEntry::first()->balance);
    }

    public function test_that_deducted_upfront_fees_doesnt_get_amortized_as_part_of_fees_to_be_repaid()
    {
        $this->setAuthenticatedUserForRequest();

        $fees = collect([
            Fee::whereName(Fee::ARRANGEMENT)->first()->fill(['rate' => 1.5]),
            Fee::whereName(Fee::ADMINISTRATION)->first()->fill(['rate' => 2.5]),
            Fee::whereName(Fee::PROCESSING)->first()->fill(['rate' => 2])
        ])->toArray();

        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'amount' => 10000,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                    'fees' => $fees,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(150.0, $loan->fees->first()->pivot->amount);
        self::assertEquals(200.0, $loan->fees->last()->pivot->amount);
        self::assertEquals(250, $loan->getUpfrontFees(false));
        self::assertEquals(600, $loan->getTotalFees(false));
        self::assertEquals(29.167, $loan->schedule->first()->fees, '', 0.1);

        $this->approveAndDisburseLoan($loan);

        // fees to be repaid should reduce because 250 has been paid upfront, remaining 350
        self::assertEquals(29.167, $loan->schedule->first()->fees, '', 0.1);
        self::assertEquals(9750, $loan->client->getAccountBalance(false));
    }

    public function test_can_disburse_a_backdated_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'grz')
                ->make([
                    'amount' => 10000,
                    'rate' => 9,
                    'tenure_id' => Tenure::whereNumberOfMonths(6)->first()->id,
                    'client_id' => factory(Client::class)->create(['account_balance' => 0])->id,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertCount(6, $loan->schedule);

        $this->request->replace(['disbursed_at' => Carbon::today()->subDays(22 * 6)]);

        $this->approveAndDisburseLoan($loan, $this->request);

        $loan = $loan->fresh();

        $loan->schedule->take(4)->each(function (LoanRepayment $repayment) {
            self::assertEquals(LoanRepayment::DEFAULTED, $repayment->status);
            self::assertFalse($repayment->has_been_paid);
            self::assertNull($repayment->repayment_timestamp);
        });

        self::assertFalse($loan->schedule->get(4)->isDue());
        self::assertNull($loan->schedule->get(4)->status);
        self::assertFalse($loan->schedule->get(5)->isDue());
        self::assertNull($loan->schedule->get(5)->status);

        // loan amount granted remains untouched
        self::assertEquals(10000, $loan->client->getAccountBalance(false));
        self::assertCount(6, $loan->schedule);
    }

    public function test_can_post_accrued_receivables_to_loan_statement_for_backdated_loans()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'grz')
                ->make([
                    'amount' => 10000,
                    'rate' => 9,
                    'tenure_id' => Tenure::whereNumberOfMonths(6)->first()->id,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertCount(6, $loan->schedule);

        $this->request->replace(['disbursed_at' => Carbon::today()->subWeekdays(100)]); // disbursed 100 days ago

        $this->dispatch(new DisburseLoanJob($this->request, $this->dispatch(new ApproveLoanJob($this->request, $loan))));

        $loan = $loan->fresh();
        $entries = LoanStatementEntry::all();

        self::assertEquals(900, $loan->schedule->first()->interest);
        self::assertCount(5, $entries);
        self::assertEquals('Loan disbursed', $entries->first()->narration);
        self::assertEquals(10000, $entries->first()->dr);
        self::assertEquals(-10000, $entries->first()->balance);

        self::assertEquals('Interest accrued', $entries->get(1)->narration);
        self::assertEquals(900, $entries->get(1)->dr);
        self::assertEquals(-10900, $entries->get(1)->balance);

        self::assertEquals('Interest accrued', $entries->get(2)->narration);
        self::assertEquals(900, $entries->get(2)->dr);
        self::assertEquals(-11800, $entries->get(2)->balance);

        self::assertEquals('Interest accrued', $entries->get(3)->narration);
        self::assertEquals(900, $entries->get(3)->dr);
        self::assertEquals(-12700, $entries->get(3)->balance);

        self::assertEquals('Interest accrued', $entries->get(4)->narration);
        self::assertEquals(900, $entries->get(4)->dr);
        self::assertEquals(-13600, $entries->get(4)->balance);
    }

}
