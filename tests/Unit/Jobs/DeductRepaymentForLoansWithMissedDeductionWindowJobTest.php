<?php

use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Events\LoanRepaymentDeductedEvent;
use App\Jobs\AddClientDepositJob;
use App\Jobs\AddClientWithdrawalJob;
use App\Jobs\AddLoanJob;
use App\Jobs\DeductRepaymentForLoansWithMissedDeductionWindowJob;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use Carbon\Carbon;
use Tests\TestCase;


class DeductRepaymentForLoansWithMissedDeductionWindowJobTest extends TestCase
{
    public function test_able_to_recalibrate_and_deduct_repayments()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanRepaymentDeductedEvent::class);

        $disbursalDate = Carbon::parse('June 15, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 3000]);

        $loan = factory(Loan::class)
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => $disbursalDate,
                'repayment_plan_id' => $repaymentPlan->id,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                'amount' => 2000,
                'rate' => 4.5,
                'client_id' => $client->id
            ]);

        $schedule = $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        // we expect 5 repayments to be made
        self::assertCount(5, $schedule);
        self::assertEquals($dueDate, $schedule->first()->due_date);

        $schedule->each(function (LoanRepayment $repayment) {
            self::assertNull($repayment->status);
        });

        $this->dispatch(new DeductRepaymentForLoansWithMissedDeductionWindowJob);

        $loan = $loan->fresh('payments');

        self::assertCount(5, $loan->payments); // 2450 total loan amount repaid (Principal = 2000, Interest = 450)
        self::assertEquals(550, $loan->client->account_balance);
    }

    /**
     * Create a loan and add a deposit for the client. That should cause deductions immediately
     */
    public function test_able_to_deduct_repayments_that_have_missed_their_repayment_date()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursalDate = Carbon::parse('June 15, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 3000]);

        $loan = factory(Loan::class, 'customer')
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => $disbursalDate,
                'repayment_plan_id' => $repaymentPlan->id,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                'amount' => 6000,
                'rate' => 4.5,
                'client_id' => $client->id
            ]);

        $schedule = $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        // we expect 5 repayments to be made
        self::assertCount(5, $schedule);
        self::assertEquals($dueDate, $schedule->first()->due_date);
        self::assertNull($schedule->first()->status);
        self::assertEquals(1470, $schedule->first()->amount);
        self::assertEquals(1200, $schedule->first()->principal);

        // deposit some money into Client's account
        $this->request->merge(
            factory(ClientTransaction::class)
            ->make(['cr' => 1530, 'loan_id' => $loan->id])
            ->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $this->dispatch(new DeductRepaymentForLoansWithMissedDeductionWindowJob);

        // Client now has an account balance of 4530 which can fully repay the first 3 repayments
        $loan = $loan->fresh('payments');

        $payments = $loan->payments;

        self::assertCount(3, $payments);

        $payments->each(function (LoanRepayment $repayment) {
            self::assertEquals(1470, $repayment->amount);
            self::assertEquals(270, $repayment->paid_interest);
            self::assertEquals(1200, $repayment->paid_principal);
        });

        $fourthRepayment = $loan->schedule->get(3);

        self::assertEquals(LoanRepayment::PART_PAYMENT, $fourthRepayment->status);
        self::assertEquals(120, $fourthRepayment->paid_interest);
        self::assertEquals(0, $loan->client->account_balance);

        $lastRepayment = $loan->schedule->last();

        self::assertEquals(LoanRepayment::DEFAULTED, $lastRepayment->status);
        self::assertEquals(0, $lastRepayment->paid_interest);
        self::assertFalse($lastRepayment->has_been_paid);

        /**
         * Deposit enough funds to be able to pay fully interest on 4th repayment
         */
        $this->request->merge(['cr' => 150]);

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh('payments', 'schedule');

        self::assertCount(3, $loan->payments);

        $fourthRepayment = $loan->schedule->get(3);

        self::assertEquals(LoanRepayment::PART_PAYMENT, $fourthRepayment->status);
        self::assertEquals(270, $fourthRepayment->paid_interest);
        self::assertEquals(0, $fourthRepayment->paid_principal);
        self::assertEquals(0, $loan->client->account_balance);

        $lastRepayment = $loan->schedule->last();

        self::assertEquals(LoanRepayment::DEFAULTED, $lastRepayment->status);
        self::assertEquals(0, $lastRepayment->paid_interest);

        /**
         * Deposit funds to cover outstanding and defaulting repayments
         */
        $this->request->merge(['cr' => 3000]);

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh('payments', 'client');

        self::assertCount(5, $loan->payments);
        self::assertEquals(330, $loan->client->account_balance);
    }

    public function test_can_update_repayment_with_right_status()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                    'amount' => 38000,
                    'tenure_id' => Tenure::whereNumberOfMonths(60)->first()->id,
                    'rate' => 3.84
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(2092.53, $loan->schedule->first()->amount, '', 0.1);

        $this->request->replace([
            'approved_at' => Carbon::parse('September 9, 2015'),
            'disbursed_at' => Carbon::parse('September 9, 2015'),
        ]);

        $this->approveAndDisburseLoan($loan, $this->request);

        // withdraw loan amount for the Client
        $this->request->replace(
            factory(ClientTransaction::class)
                ->make(['dr' => $loan->client->getAccountBalance(false)])
                ->toArray()
        );

        $this->dispatch(new AddClientWithdrawalJob($this->request, $loan->client));

        $this->artisan('microfin:recalibrate-missed-deductions');

        $loan = $loan->fresh();

        $loan->schedule->each(function (LoanRepayment $repayment, $i) {

            if ($i < 11) { // the first 11 repayments have been defaulted
                self::assertEquals(LoanRepayment::DEFAULTED, $repayment->status);
            }

        });

        // make a deposit for Client
        $this->request->replace(factory(ClientTransaction::class)->states('deposit')->make(['cr' => 23870])->toArray());
        $this->dispatch(new AddClientDepositJob($this->request, $loan->client));

        $loan = $loan->fresh();

        $loan->schedule->each(function (LoanRepayment $repayment, $i) {

            if ($i < 11) { // the first 11 repayments should be paid now
                self::assertEquals(LoanRepayment::FULL_PAYMENT, $repayment->status);
            } elseif ($i === 11) {
                self::assertEquals(LoanRepayment::PART_PAYMENT, $repayment->status);
            } elseif ($i > 11 && $i < 18) {
                self::assertEquals(LoanRepayment::DEFAULTED, $repayment->status);
            }

        });

        self::assertEquals(23870, $loan->getAmountPaid(false), '', 0.1);
        self::assertEquals(0, $loan->client->getAccountBalance());
    }

}
