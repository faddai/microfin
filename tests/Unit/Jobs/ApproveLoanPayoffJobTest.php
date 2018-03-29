<?php

use App\Entities\Client;
use App\Entities\Loan;
use App\Entities\LoanPayoff;
use App\Entities\LoanRepayment;
use App\Entities\Tenure;
use App\Events\DepositAddedEvent;
use App\Jobs\AddLoanJob;
use App\Jobs\AddLoanPayoffJob;
use App\Jobs\ApproveLoanPayoffJob;
use Carbon\Carbon;
use Tests\TestCase;

class ApproveLoanPayoffJobTest extends TestCase
{
    private function createLoan(array $data = [])
    {
        $this->request->merge(
            factory(Loan::class, 'customer')
                ->states('approved', 'disbursed')
                ->make($data)
                ->toArray()
        );

        return $this->approveAndDisburseLoan(
            $this->dispatch(new AddLoanJob($this->request, null, false)),
            $this->request
        );
    }

    private function payoffLoan(Loan $loan, array $data = [])
    {
        $this->request->replace(factory(LoanPayoff::class)->make($data)->toArray());

        return $this->dispatch(new AddLoanPayoffJob($this->request, $loan));
    }

    public function test_that_a_paid_off_loan_ceases_being_an_active_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoan([
            'amount' => 1000,
            'rate' => 9,
            'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
            'disbursed_at' => Carbon::today()->subWeekdays(24 * 3)
        ]);

        // repayments for the first 3 months
        $loan->schedule->take(3)->each(function (LoanRepayment $repayment) {
            $this->artisan('microfin:repay', ['dueDate' => $repayment->due_date]);
        });

        // active & running loan
        self::assertCount(1, Loan::active()->get());

        $payoff = $this->payoffLoan($loan, ['principal' => 400, 'interest' => 180]);

        $approved = $this->dispatch(new ApproveLoanPayoffJob($this->request, $payoff));

        $approvedPayoff = $approved->fresh();

        self::assertEquals(LoanPayoff::APPROVED, $approvedPayoff->status);
        self::assertEquals(Loan::PAID_OFF, $approvedPayoff->loan->status);
        self::assertTrue($approvedPayoff->loan->isFullyPaid());
        self::assertFalse($approvedPayoff->loan->isRunning());
        self::assertCount(0, Loan::active()->get());
    }

    public function test_can_approve_loan_payoff_before_maturity_without_penalty()
    {
        /**
         * Scenario:
         *
         * Client takes a facility of 1000 for 5 months. He has a monthly repayment amount of 290
         * He pays 3 installments and decides to pay off the loan before maturity
         */
        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoan([
            'amount' => 1000,
            'rate' => 9,
            'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
            'disbursed_at' => Carbon::today()->subWeekdays(24 * 3)
        ]);

        // repayments for the first 3 months
        $loan->schedule->take(3)
            ->each(function (LoanRepayment $repayment) {
                $this->artisan('microfin:repay', ['dueDate' => $repayment->due_date]);
            })
            ->each(function (LoanRepayment $repayment) {
                self::assertEquals(LoanRepayment::FULL_PAYMENT, $repayment->fresh()->status);
            });

        $loan = $loan->fresh();

        self::assertEquals(870, $loan->getAmountPaid(false));
        self::assertEquals(580, $loan->getBalance(false));
        self::assertEquals(600, $loan->product->principalLedger->entries->sum('cr')); // paid principal
        self::assertEquals(270, $loan->product->interestReceivableLedger->entries->sum('cr')); // paid interest

        $payoff = $this->payoffLoan($loan, ['principal' => 400, 'interest' => 180]);

        self::assertInstanceOf(LoanPayoff::class, $payoff);
        self::assertEquals(400, $payoff->principal);
        self::assertEquals(180, $payoff->interest);
        self::assertEquals(0, $payoff->penalty);
        self::assertEquals(0, $payoff->fees);

        $approved = $this->dispatch(new ApproveLoanPayoffJob($this->request, $payoff));

        self::assertTrue($approved->loan->isFullyPaid());
        self::assertFalse($approved->loan->isRunning()); // paid off loan ceases being active
        self::assertEquals(Loan::PAID_OFF, $approved->loan->status);
        self::assertEquals(450, $approved->loan->product->interestReceivableLedger->entries->sum('cr'));
        self::assertEquals(1000, $approved->loan->product->principalLedger->entries->sum('cr'));
        self::assertEquals(1, Loan::paidOff()->count());
    }

    public function test_can_approve_loan_payoff_for_a_loan_with_defaulted_repayments()
    {
        /**
         * Scenario:
         *
         * Client takes a facility of 1000 for 5 months. He has a monthly repayment amount of 290
         * He pays 3 installments and decides to pay off the loan after defaulting on the remaining
         * 2 repayments. This Client is charged a penalty of 200
         */
        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoan([
            'amount' => 1000,
            'rate' => 9,
            'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
            'disbursed_at' => Carbon::today()->subWeekdays(24 * 6)
        ]);

        // make available funds that can settle 3 repayments available in Client's account
        $loan->client->update(['account_balance' => 290 * 3]);

        $loan->schedule
            ->each(function (LoanRepayment $repayment) {
                $this->artisan('microfin:repay', ['dueDate' => $repayment->due_date]);
            })
            ->each(function (LoanRepayment $repayment, $key) {

                $rep = $repayment->fresh();

                if ($key < 3) {
                    self::assertEquals(LoanRepayment::FULL_PAYMENT, $rep->status);
                } else {
                    self::assertEquals(LoanRepayment::DEFAULTED, $rep->status);
                }

            });

        $loan = $loan->fresh();

        self::assertEquals(870, $loan->getAmountPaid(false));
        self::assertEquals(580, $loan->getBalance(false));
        self::assertEquals(600, $loan->product->principalLedger->entries->sum('cr')); // paid principal
        self::assertEquals(270, $loan->product->interestReceivableLedger->entries->sum('cr')); // paid interest

        /**
         * Clients comes to payoff loan. Principal and Interest that needs to be paid have been negotiated
         * Client has negotiated to pay less than what has been scheduled for him to pay
         */
        $payoff = $this->payoffLoan($loan, ['principal' => 300, 'interest' => 180, 'penalty' => 200]);

        self::assertInstanceOf(LoanPayoff::class, $payoff);
        self::assertEquals(300, $payoff->principal);
        self::assertEquals(180, $payoff->interest);
        self::assertEquals(200, $payoff->penalty);
        self::assertEquals(0, $payoff->fees);

        $approved = $this->dispatch(new ApproveLoanPayoffJob($this->request, $payoff));

        self::assertTrue($approved->loan->isFullyPaid());
        self::assertEquals(270 + (180 + 200), $approved->loan->product->interestReceivableLedger->entries->sum('cr'));
        self::assertEquals(900, $approved->loan->product->principalLedger->entries->sum('cr'));
    }

    public function test_can_make_deposit_into_client_account_without_triggering_repayment_deductions()
    {
        $this->setAuthenticatedUserForRequest();

        $this->doesntExpectEvents(DepositAddedEvent::class);

        $loan = $this->createLoan([
            'amount' => 1000,
            'rate' => 9,
            'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 2402.4902])->id
        ]);

        $payoff = $this->payoffLoan($loan, ['principal' => 180, 'interest' => 500]);

        $this->dispatch(new ApproveLoanPayoffJob($this->request, $payoff));

        // whatever account balance the client had before pay off should remain the same
        // realistically, a Client will have 0 balance prior to payoff
        self::assertEquals(2402.4902, $payoff->loan->client->account_balance);
    }

    public function test_can_post_payoff_to_loan_account_statement()
    {
        $this->addWeekdayToCarbonNowOnWeekend();

        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoan(['amount' => 1000, 'rate' => 9]);

        $payoff = $this->payoffLoan($loan, ['principal' => 180, 'interest' => 500]);

        $this->dispatch(new ApproveLoanPayoffJob($this->request, $payoff));

        $loan = $loan->fresh();

        self::assertEquals(680, $loan->statement->entries->last()->cr);
        self::assertEquals('Loan payoff - ' . $loan->number, $loan->statement->entries->last()->narration);
    }

    public function test_can_mark_repayments_as_paid_after_successfully_paying_off_a_loan()
    {
        /**
         * Create a loan and fulfil 2 payments normally
         * The remaining repayments should be taken care of after payoff
         */
        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoan([
            'amount' => 1000,
            'rate' => 9,
            'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
            'disbursed_at' => Carbon::today()->subWeekdays(24 * 4),
        ]);

        $loan->client()->update(['account_balance' => 290 * 2]); // deposit enough funds to cover 2 repayments

        $loan->schedule->take(2)->each(function (LoanRepayment $repayment) {
            $this->artisan('microfin:repay', ['dueDate' => $repayment->due_date]);
        });

        $loan = $loan->fresh();

        $loan->schedule->each(function (LoanRepayment $repayment, $i) {
            if ($i < 2) {
                self::assertEquals(LoanRepayment::FULL_PAYMENT, $repayment->status);
            } elseif ($i > 1 && $i < 4) {
                self::assertEquals(LoanRepayment::DEFAULTED, $repayment->status);
            } else {
                self::assertNull($repayment->status); // undue repayment
            }
        });

        $payoff = $this->payoffLoan($loan, ['principal' => 180, 'interest' => 500]);

        $this->dispatch(new ApproveLoanPayoffJob($this->request, $payoff));

        $loan = $loan->fresh();

        self::assertEquals(680, $loan->statement->entries->last()->cr);
        self::assertEquals('Loan payoff - ' . $loan->number, $loan->statement->entries->last()->narration);
        self::assertNotNull($loan->client->transactions->last()->value_date);
        self::assertEquals(0, $loan->client->getAccountBalance());

        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(LoanRepayment::FULL_PAYMENT, $repayment->status);
            self::assertEquals('<span class="label label-success">Paid</span>', $repayment->getStatus()->get('label'));
        });
    }

}
