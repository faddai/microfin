<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/02/2018
 * Time: 3:56 PM
 */

use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Jobs\AddClientDepositJob;
use App\Jobs\AddLoanJob;
use App\Jobs\AutomatedLoanRepaymentJob;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use Carbon\Carbon;
use Tests\TestCase;

class DeductLoanRepaymentAfterClientDepositTransactionTest extends TestCase
{
    public function test_able_to_deduct_repayment_amount_for_a_partly_paid_interest()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursalDate = Carbon::parse('September 9, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 100]);

        $this->request->merge(
            factory(Loan::class, 'customer')
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => $disbursalDate,
                    'repayment_plan_id' => $repaymentPlan->id,
                    'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
                    'amount' => 6000,
                    'rate' => 4.5,
                    'client_id' => $client->id
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $firstScheduledRepayment = $loan->schedule->first();

        // we expect 5 repayments to be made
        self::assertCount(5, $loan->schedule);
        self::assertEquals($dueDate, $firstScheduledRepayment->due_date);
        self::assertEquals(1470, $firstScheduledRepayment->amount);
        self::assertEquals(270, $firstScheduledRepayment->interest);
        self::assertEquals(1200, $firstScheduledRepayment->principal);

        // do deductions
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->fresh()->schedule->first();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $repayment->status);
        self::assertEquals(100, $repayment->paid_interest);
        self::assertEquals(1370, $repayment->getOutstandingRepaymentAmount(false));

        // deposit some money into Client's account
        $this->request->replace(
            factory(ClientTransaction::class)->states('deposit')->make(['cr' => 2000])->toArray()
        );

        $deposit = $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh('payments', 'schedule');

        $firstPayment = $loan->payments->first();

        self::assertInstanceOf(ClientTransaction::class, $deposit);
        self::assertEquals(2000, $deposit->cr);
        self::assertTrue($firstPayment->has_been_paid);
        self::assertEquals(1470, $firstPayment->getTotalAmountPaid(false));

        // rest of the Client's balance goes towards the servicing of the next due repayment
        self::assertEquals(630, $loan->schedule->get(1)->getTotalAmountPaid(false));
    }

    public function test_deduct_repayment_amount_to_topup_a_previous_partly_paid_interest()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursalDate = Carbon::parse('Dec 15, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 100]);

        $loan = factory(Loan::class, 'staff')
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => $disbursalDate,
                'repayment_plan_id' => $repaymentPlan->id,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                'amount' => 6000,
                'rate' => 4.5,
                'client_id' => $client->id
            ]);

        $scheduledRepayments = $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        // we expect 5 repayments to be made
        self::assertCount(5, $scheduledRepayments); // I = 270, P = 1200, A = 1470

        // do deductions
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->schedule->first();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $repayment->status);
        self::assertEquals(100, $repayment->paid_interest);
        self::assertEquals(1370, $repayment->getOutstandingRepaymentAmount(false));

        // deposit some money into Client's account to pay part of outstanding repayment amount
        $this->request->merge(
            factory(ClientTransaction::class)->states('deposit')->make(['cr' => 1300])->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh('payments');

        $firstPayment = $loan->schedule->first();

        self::assertNull($loan->payments->first());
        self::assertEquals(LoanRepayment::PART_PAYMENT, $firstPayment->status);
        self::assertEquals(1400, $firstPayment->paid_interest + $firstPayment->paid_principal);
        self::assertEquals(70, $firstPayment->getOutstandingRepaymentAmount(false));
        self::assertEquals(0, $loan->client->getAccountBalance(false));

        // deposit some money into Client's account
        $this->request->merge(['cr' => 100]);

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh('payments');

        $firstPayment = $loan->schedule->first();

        self::assertTrue($firstPayment->isFullyPaid());
        self::assertEquals(1470, $firstPayment->paid_interest + $firstPayment->paid_principal);
        self::assertEquals(0, $firstPayment->getOutstandingRepaymentAmount());
        self::assertEquals(30, $loan->schedule->get(1)->getTotalAmountPaid(false));
    }

    public function test_deduct_repayment_amount_for_a_partly_paid_principal()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursalDate = Carbon::parse('Dec 15, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 1200]);

        $loan = factory(Loan::class, 'staff')
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
        self::assertEquals(1470, $schedule->first()->amount); // repayment amount
        self::assertEquals(270, $schedule->first()->getInterest(false)); // repayment interest = 0.045 * 6000
        self::assertEquals(1200, $schedule->first()->getPrincipal(false)); // repayment principal = 1470 - (0.045 * 6000)

        // do deductions
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->schedule->first();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $repayment->status);
        self::assertEquals(270, $repayment->paid_interest);
        self::assertEquals(930, $repayment->paid_principal);

        // deposit some money into Client's account
        $this->request->merge(
            factory(ClientTransaction::class)->states('deposit')->make(['cr' => 400])->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh('payments');

        $firstPayment = $loan->payments->first();

        self::assertTrue($firstPayment->has_been_paid);
        self::assertEquals(1470, $firstPayment->getTotalAmountPaid(false));
        self::assertEquals(130, $loan->schedule->get(1)->getTotalAmountPaid(false));
    }

    /**
     * Pay the Outstanding principal of repayment amount to fulfill the full repayment of a loan
     */
    public function test_deduct_loan_repayment_from_client_with_enough_balance_to_pay_outstanding_principal()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursalDate = Carbon::parse('Dec 12, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 1000]);

        $loan = factory(Loan::class, 'staff')
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => $disbursalDate,
                'repayment_plan_id' => $repaymentPlan->id,
                'tenure_id' => Tenure::whereNumberOfMonths(2)->first()->id,
                'amount' => 2000,
                'rate' => 3,
                'client_id' => $client->id
            ]);

        $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        // effect repayment
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->payments->first();

        // repayment amount is 1060 x 2months
        // make sure it is not recorded as a full repayment because client doesn't have that much
        self::assertNull($repayment);

        $firstScheduledRepayment = $loan->schedule->first();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $firstScheduledRepayment->status);
        self::assertEquals(60, $firstScheduledRepayment->paid_interest);
        self::assertEquals(940, $firstScheduledRepayment->paid_principal);
        self::assertEquals(60, $firstScheduledRepayment->getOutstandingRepaymentAmount(false));
        self::assertEquals(0.0, $loan->client->getAccountBalance(false));

        $this->request->replace(
            factory(ClientTransaction::class)->states('deposit')->make(['cr' => 60])->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        self::assertEquals(0.0, $loan->client->getAccountBalance(false));

        $loan = $loan->fresh('payments');

        self::assertEquals(1000, $loan->schedule->first()->paid_principal);

        $repayment = $loan->payments->first();

        self::assertInstanceOf(LoanRepayment::class, $repayment);

        $schedule = $loan->fresh()->schedule->first();

        self::assertTrue($schedule->isFullyPaid());
        self::assertNotNull($schedule->repayment_timestamp);
    }

    /**
     * Pay the Outstanding interest of repayment amount to fulfill the full repayment of a loan
     */
    public function test_deduct_loan_repayment_from_client_with_enough_balance_to_pay_outstanding_interest()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursalDate = Carbon::parse('Dec 12, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 50]);

        $loan = factory(Loan::class, 'staff')
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => $disbursalDate,
                'repayment_plan_id' => $repaymentPlan->id,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 2])->id,
                'amount' => 2000,
                'rate' => 3,
                'client_id' => $client->id
            ]);

        $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        // effect repayment
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->payments->first();

        // repayment amount is 1060 x 2months
        // make sure it is not recorded as a full repayment because client doesn't have that much
        self::assertNull($repayment);

        $firstScheduledRepayment = $loan->schedule->first();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $firstScheduledRepayment->status);
        self::assertEquals(50, $firstScheduledRepayment->paid_interest);
        self::assertEquals(0, $firstScheduledRepayment->paid_principal);
        self::assertEquals(1010, $firstScheduledRepayment->getOutstandingRepaymentAmount(false));
        self::assertEquals(0.0, $this->callPrivateMethod($firstScheduledRepayment, 'getClientAccountBalance'));

        // Add a deposit for Client
        $this->request->replace(
            factory(ClientTransaction::class)->states('deposit')->make(['cr' => 60])->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        self::assertEquals(0.0, $loan->client->getAccountBalance());

        $loan = $loan->fresh('payments');

        $schedule = $loan->schedule->first();

        self::assertEquals(60, $schedule->paid_interest);
        self::assertEquals(50, $schedule->paid_principal);
        self::assertEquals(950, $schedule->getOutstandingRepaymentAmount(false));
        self::assertNull($loan->payments->first());

        /**
         * Add another deposit that would allow client to be able to repay the Outstanding
         * Repayment amount.
         */
        $this->request->merge(['cr' => 960]);

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh();

        $repayment = $loan->payments->first();

        self::assertInstanceOf(LoanRepayment::class, $repayment);
        self::assertTrue($repayment->isFullyPaid());
    }

    public function test_deduct_loan_repayment_from_client_with_enough_balance_to_pay_multiple_outstanding_interests()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = factory(Loan::class, 'staff')
            ->states('approved', 'disbursed')
            ->make([
                'disbursed_at' => Carbon::parse('June 12, 2016'),
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                'amount' => 2000,
                'rate' => 3,
                'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 1250])->id
            ]);

        $this->request->merge($loan->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(726.67, $loan->schedule->first()->amount, '', 0.1);
        self::assertEquals(60, $loan->schedule->first()->interest);
        self::assertEquals(666.67, $loan->schedule->first()->principal, '', 0.1);

        $this->artisan('microfin:recalibrate-missed-deductions');

        $loan = $loan->fresh('payments', 'schedule');

        $schedule = $loan->schedule;

        self::assertCount(1, $loan->payments);
        self::assertTrue($loan->payments->first()->isFullyPaid());

        $secondScheduledRepayment = $schedule->get(1);

        self::assertEquals(LoanRepayment::PART_PAYMENT, $secondScheduledRepayment->status);
        self::assertEquals(60, $secondScheduledRepayment->paid_interest);
        self::assertEquals(463.33, $secondScheduledRepayment->paid_principal, '', 0.01);

        $lastScheduledRepayment = $schedule->last();

        self::assertEquals(LoanRepayment::DEFAULTED, $lastScheduledRepayment->status);
        self::assertEquals(0, $lastScheduledRepayment->paid_interest);
        self::assertEquals(0, $lastScheduledRepayment->paid_principal);

        self::assertInstanceOf(ClientTransaction::class, $loan->client->transactions->first());
    }

    public function test_can_identify_and_deduct_repayment_for_a_loan_out_of_multiple_running_loans()
    {
        $this->setAuthenticatedUserForRequest();

        // create 3 more running loans
        factory(Loan::class, 'customer', 3)
            ->states('approved', 'disbursed')
            ->make()
            ->each(function (Loan $loan) {
                // associate a client to the loan
                $loan->fill([
                    'disbursed_at' => Carbon::today()->subMonths(random_int(1, 6)),
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id
                ]);

                $this->request->replace($loan->toArray());

                $this->dispatch(new AddLoanJob($this->request));
            });

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => Carbon::parse('June 12, 2016'),
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                    'amount' => 2000,
                    'rate' => 3,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id
                ])
                ->toArray()
        );

        $this->dispatch(new AddLoanJob($this->request));

        // these are backdated loans, recalibrate
        $this->artisan('microfin:recalibrate-missed-deductions');

        // credit Client's account
        $this->request->replace(
            factory(ClientTransaction::class)->states('deposit')->make(['cr' => 1250])->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, Loan::all()->last()->client));

        $loan = Loan::all()->last();

        self::assertCount(4, Loan::running());
        self::assertEquals(2000, $loan->amount);
        self::assertEquals(726.67, $loan->schedule->first()->getAmount(), '', 0.1);
        self::assertEquals(60, $loan->schedule->first()->getPaidInterest(false));
        self::assertEquals(666.67, $loan->schedule->first()->getPaidPrincipal(), '', 0.1);
        self::assertEquals(0, $loan->client->getAccountBalance(false));
    }

    public function test_deduct_repayment_amount_from_a_defaulting_client()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursalDate = Carbon::parse('Dec 15, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 0]);

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

        // do deductions
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayments = $loan->schedule;

        self::assertEquals(LoanRepayment::DEFAULTED, $repayments->first()->status);
        self::assertEquals(0, $repayments->first()->loan->client->account_balance);

        // deposit some money into Client's account
        $this->request->merge(
            factory(ClientTransaction::class)->states('deposit')->make(['cr' => 2000])->toArray()
        );

        $this->dispatch(new AddClientDepositJob($this->request, $client));

        $loan = $loan->fresh('payments');

        $firstPayment = $loan->payments->first();

        self::assertTrue($firstPayment->has_been_paid);
        self::assertEquals(1470, $firstPayment->getTotalAmountPaid(false));
        self::assertEquals(530, $loan->schedule->get(1)->getTotalAmountPaid(false));
    }
}
