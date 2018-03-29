<?php

use App\Entities\Accounting\LedgerEntry;
use App\Entities\Client;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Events\LoanDisbursedEvent;
use App\Jobs\AddLoanJob;
use App\Jobs\AutomatedLoanRepaymentJob;
use Carbon\Carbon;
use Tests\TestCase;


class AutomatedLoanRepaymentJobTest extends TestCase
{
    public function test_that_deductions_happen_for_running_loans()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class)->make([
                'amount' => 10000,
                'rate' => 6,
                'created_at' => Carbon::parse('Dec 16, 2016'),
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 4])->id,
                'client_id' => factory(Client::class, 'corporate')->create(['account_balance' => 20000])->id,
            ])->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertNotNull($loan->schedule);
        self::assertFalse($loan->isRunning());

        $this->artisan('microfin:recalibrate-missed-deductions');

        self::assertCount(0, $loan->payments);
    }

    public function test_deduct_repayment_amount_from_client_with_no_account_balance()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $disbursalDate = Carbon::parse('Dec 15, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 0]);

        $this->request->merge(
            factory(Loan::class)
                ->make([
                    'disbursed_at' => $disbursalDate,
                    'repayment_plan_id' => $repaymentPlan->id,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                    'amount' => 6000,
                    'rate' => 4.5,
                    'client_id' => $client->id
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->approveAndDisburseLoan($loan);

        // we expect 5 repayments to be made
        self::assertCount(5, $loan->schedule);
        self::assertEquals($dueDate, $loan->schedule->first()->due_date);

        // do deductions
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $loan = $loan->fresh();

        self::assertEquals(LoanRepayment::DEFAULTED,  $loan->schedule->first()->status);
        self::assertFalse($loan->schedule->first()->has_been_paid);
        self::assertNull($loan->schedule->first()->repayment_timestamp);
    }

    public function test_deduct_loan_repayment_due_from_client_with_enough_balance()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $disbursalDate = Carbon::parse('Dec 12, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::WEEKLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 2000]);

        $this->request->merge(
            factory(Loan::class)
                ->make([
                    'disbursed_at' => $disbursalDate,
                    'repayment_plan_id' => $repaymentPlan->id,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                    'amount' => 1000,
                    'rate' => 5,
                    'client_id' => $client->id
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->approveAndDisburseLoan($loan);

        self::assertEquals($dueDate, $loan->schedule->first()->due_date);
        self::assertCount(12, $loan->schedule); // check to make sure the right number of repayments is generated

        // effect repayment
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->fresh()->payments->first();

        self::assertTrue($repayment->has_been_paid);
        self::assertEquals(83.33, $repayment->getPaidPrincipal());
        self::assertEquals(12.50, $repayment->getPaidInterest());
        self::assertEquals(95.83, $repayment->getAmount());
    }

    public function test_deduct_loan_repayment_from_client_with_enough_balance_to_cover_interest_part_payment()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $disbursalDate = Carbon::parse('Dec 12, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::FORTNIGHTLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 200]);

        $this->request->merge(
            factory(Loan::class)
                ->make([
                    'disbursed_at' => $disbursalDate,
                    'repayment_plan_id' => $repaymentPlan->id,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 2])->id,
                    'amount' => 20000,
                    'rate' => 3,
                    'client_id' => $client->id
                ])
            ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->approveAndDisburseLoan($loan);

        self::assertCount(4, $loan->schedule);
        self::assertEquals($dueDate, $loan->schedule->first()->due_date);

        // effect repayment
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->fresh()->payments->first();

        // make sure it is not recorded as a full repayment
        self::assertNull($repayment);

        $firstScheduledRepayment = $loan->fresh()->schedule->first();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $firstScheduledRepayment->status);
        self::assertEquals(5100, $firstScheduledRepayment->getOutstandingRepaymentAmount(false));
        self::assertEquals(0.0, $loan->client->getAccountBalance(false));
    }

    /**
     * Disburse a loan of 2000 at 3% monthly rate with a monthly repayment
     * lasting for 2 months.
     *
     * Repayment amount = 1060
     * Repayment interest = 60 (0.03 x 2000)
     * Repayment principal = 1000
     *
     * At the time of the first repayment, Client has an amount of 1000 as
     * his account balance. This isn't enough to pay the full repayment amount
     * so, the interest (60) is deducted. The remaining balance (940) is then
     * used to repay part of the repayment principal.
     */
    public function test_deduct_loan_repayment_from_client_with_enough_balance_to_cover_interest_and_principal_part_payment()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $disbursalDate = Carbon::parse('Dec 12, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 1000]);

        $this->request->merge(
            factory(Loan::class)
                ->make([
                    'disbursed_at' => $disbursalDate,
                    'repayment_plan_id' => $repaymentPlan->id,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 2])->id,
                    'amount' => 2000,
                    'rate' => 3,
                    'client_id' => $client->id
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->approveAndDisburseLoan($loan);

        // effect repayment
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $repayment = $loan->payments->first();

        // repayment amount is 1060
        // make sure it is not recorded as a full repayment because client doesn't have that much
        self::assertNull($repayment);

        $firstScheduledRepayment = $loan->fresh()->schedule->first();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $firstScheduledRepayment->status);
        self::assertEquals(60, $firstScheduledRepayment->paid_interest);
        self::assertEquals(940, $firstScheduledRepayment->paid_principal);
        self::assertEquals(60, $firstScheduledRepayment->getOutstandingRepaymentAmount(false));
        self::assertEquals(0.0, $loan->client->getAccountBalance(false));
    }

    /**
     * @expectedException App\Exceptions\InsufficientAccountBalanceException
     */
    public function test_deduction_shouldnt_take_a_client_account_to_a_negative_balance()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $client = factory(Client::class, 'individual')->create(['account_balance' => 50]);

        $this->request->merge(
            factory(Loan::class)->make([
                'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 2])->id,
                'amount' => 2000,
                'rate' => 3,
                'client_id' => $client->id
            ])
            ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->approveAndDisburseLoan($loan);

        $repayment = $loan->schedule->first();

        $this->callPrivateMethod($repayment, 'decrementClientAccountBalance', ['amount' => 100]);
    }

    public function test_able_to_deduct_repayment_amount_for_a_loan_with_fees()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $disbursalDate = Carbon::parse('Dec 15, 2016');

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $dueDate = $disbursalDate->copy()->addWeekdays($repaymentPlan->number_of_days);

        $this->request->merge(
            factory(Loan::class)->make([
                'disbursed_at' => $disbursalDate,
                'repayment_plan_id' => $repaymentPlan->id,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                'amount' => 6000,
                'rate' => 4.5,
                'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                'fees' => [['rate' => 17, 'id' => 3]]
            ])
            ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertCount(5, $loan->schedule);

        $this->approveAndDisburseLoan($loan);

        // we expect 5 repayments to be made
        self::assertCount(5, $loan->fresh()->schedule);
        self::assertEquals($dueDate, $loan->schedule->first()->due_date);
        self::assertEquals(204, $loan->schedule->first()->fees);

        // do deductions
        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        self::assertEquals(LoanRepayment::DEFAULTED, $loan->fresh()->schedule->first()->status);

        // Update Client's account balance to be able to cover part of fees for the first repayment
        $loan->client()->update(['account_balance' => 200]);

        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $firstScheduledRepayment = $loan->fresh()->schedule->first();

        self::assertEquals(0, $firstScheduledRepayment->paid_fees);
        self::assertEquals(200, $firstScheduledRepayment->paid_interest);
        self::assertEquals(LoanRepayment::PART_PAYMENT, $firstScheduledRepayment->status);

        // add enough funds to pay off fees and part of interest
        $loan->client()->update(['account_balance' => 500]);

        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $firstScheduledRepayment = $loan->fresh()->schedule->first();

        self::assertEquals(0, $firstScheduledRepayment->paid_fees);
        self::assertEquals(270, $firstScheduledRepayment->paid_interest);
        self::assertEquals(430, $firstScheduledRepayment->paid_principal);
        self::assertEquals(LoanRepayment::PART_PAYMENT, $firstScheduledRepayment->status);
        self::assertEquals(0, $loan->client->getAccountBalance());
        self::assertEquals(974, $firstScheduledRepayment->getOutstandingRepaymentAmount(false));

        $loan->client()->update(['account_balance' => 1000]);

        $this->dispatch(new AutomatedLoanRepaymentJob($dueDate));

        $firstScheduledRepayment = $loan->fresh()->schedule->first();

        self::assertTrue($firstScheduledRepayment->isFullyPaid());
        self::assertEquals(204, $firstScheduledRepayment->paid_fees);
        self::assertEquals(26, $loan->client->getAccountBalance());
    }

    public function test_does_not_amortize_fees_deducted_upfront()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class)
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => Carbon::parse('Jan 11, 2017'),
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                    'amount' => 6000,
                    'rate' => 4.5,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                    'fees' => [
                        Fee::whereName(Fee::ADMINISTRATION)->first()->toArray(),
                        Fee::whereName(Fee::ARRANGEMENT)->first()->fill(['is_paid_upfront' => 1])->toArray(),
                    ],
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(0, $repayment->getFees());
        });
    }

    public function test_can_deduct_amortized_fees_and_post_gl_entries()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursed = Carbon::parse('Jan 11, 2017');

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => $disbursed,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                    'amount' => 6000,
                    'rate' => 4.5,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 1700])->id,
                    'fees' => collect([
                        Fee::whereName(Fee::ADMINISTRATION)->first(), // upfront = 5%
                        Fee::whereName(Fee::DISBURSEMENT)->first(), // amortized fee = 5%
                        Fee::whereName(Fee::ARRANGEMENT)->first()->fill(['is_paid_upfront' => 1, 'rate' => 5]) // upfront = 5%
                    ])->toArray(),
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $amortizedFeeOnRepayment = 0.05 * 6000 / 5;
        $adminFee = 0.05 * 6000;
        $arrangementFee = 0.05 * 6000;

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        $firstRepayment = $loan->fresh()->schedule->first();

        self::assertEquals($adminFee + $arrangementFee, $loan->getUpfrontFees());
        self::assertEquals(1530, $firstRepayment->amount);
        self::assertEquals(1200, $firstRepayment->principal);
        self::assertEquals(270, $firstRepayment->interest);
        self::assertEquals($amortizedFeeOnRepayment, $firstRepayment->fees);
        self::assertEquals(60, $firstRepayment->getPaidFees(false));
        self::assertEquals(170, $loan->client->getAccountBalance(false));

        // check corresponding ledger entries
        $entries = LedgerEntry::all();

        self::assertCount(4, $entries);
        self::assertEquals(1530, $entries->first()->dr); // total repayment amount deducted
        self::assertEquals(270, $entries->get(1)->cr); // interest
        self::assertEquals(1200, $entries->get(2)->cr); // principal
        self::assertEquals(60, $entries->get(3)->cr); // disbursement fee
    }

    public function test_can_post_gl_entries_after_deducting_amortized_fees_over_multiple_deductions_for_a_single_repayment()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursed = Carbon::parse('Jan 11, 2017');

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => $disbursed,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                    'amount' => 6000,
                    'rate' => 4.5,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 1600])->id,
                    'fees' => collect([
                        Fee::whereName(Fee::ADMINISTRATION)->first(), // upfront = 5%
                        Fee::whereName(Fee::DISBURSEMENT)->first(), // amortized fee = 5%
                        Fee::whereName(Fee::ARRANGEMENT)->first() // amortized fee = 7%
                    ])->toArray(),
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $disbursementFeeForRepayment = (5 / 100) * $loan->amount / $loan->tenure->number_of_months; // 60
        $arrangementFeeForRepayment = (7 / 100) * $loan->amount / $loan->tenure->number_of_months; // 84
        $amortizedFees = $disbursementFeeForRepayment + $arrangementFeeForRepayment;

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        $loan = $loan->fresh('schedule');

        $firstRepayment = $loan->schedule->first();

        self::assertEquals(1614, $firstRepayment->amount);
        self::assertEquals(1200, $firstRepayment->principal);
        self::assertEquals(270, $firstRepayment->interest);
        self::assertEquals($amortizedFees, $firstRepayment->fees);
        self::assertEquals(130, $firstRepayment->getPaidFees(false));
        self::assertEquals(60, $disbursementFeeForRepayment);
        self::assertEquals(84, $arrangementFeeForRepayment);

        // check corresponding ledger entries
        $entries = LedgerEntry::all();

        self::assertCount(5, $entries);
        self::assertEquals(1600, $entries->first()->dr); // total repayment amount deducted
        self::assertEquals(270, $entries->get(1)->cr); // interest
        self::assertEquals(1200, $entries->get(2)->cr); // principal
        self::assertEquals(60, $entries->get(3)->cr); // disbursement fee
        self::assertEquals(70, $entries->get(4)->cr); // arrangement fee

        // let's credit this Client's account and continue with repayment
        $loan->client->update(['account_balance' => 2000]);

        $this->dispatch(new AutomatedLoanRepaymentJob($firstRepayment->due_date));

        $entries = LedgerEntry::all();

        self::assertCount(7, $entries);
        self::assertEquals(14, $entries->get(5)->dr);
        self::assertEquals(14, $entries->get(6)->cr); // remaining fee on first repayment
    }

    public function test_can_post_gl_entries_after_deducting_interest_over_multiple_deductions_for_a_single_repayment()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursed = Carbon::parse('Jan 11, 2017');

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => $disbursed,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                    'amount' => 6000,
                    'rate' => 4.5,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 50])->id,
                    'fees' => collect([
                        Fee::whereName(Fee::ADMINISTRATION)->first(), // upfront = 5%
                        Fee::whereName(Fee::DISBURSEMENT)->first(), // amortized fee = 5%
                        Fee::whereName(Fee::ARRANGEMENT)->first() // amortized fee = 7%
                    ])->toArray(),
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        $loan = $loan->fresh('schedule');

        $firstRepayment = $loan->schedule->first();

        self::assertEquals(1614, $firstRepayment->amount);
        self::assertEquals(1200, $firstRepayment->principal);
        self::assertEquals(270, $firstRepayment->interest);
        self::assertEquals(50, $firstRepayment->getPaidInterest(false));

        // check corresponding ledger entries
        $entries = LedgerEntry::all();

        self::assertCount(2, $entries);
        self::assertEquals(50, $entries->first()->dr); // total repayment amount deducted
        self::assertEquals(50, $entries->get(1)->cr); // interest

        // let's credit this Client's account and continue with repayment
        $loan->client->update(['account_balance' => 2000]);

        $this->dispatch(new AutomatedLoanRepaymentJob($firstRepayment->due_date));

        $entries = LedgerEntry::all();

        self::assertCount(7, $entries);
        self::assertEquals(1564, $entries->get(2)->dr);
        self::assertEquals(220, $entries->get(3)->cr); // remaining interest on first repayment
        self::assertEquals(1200, $entries->get(4)->cr);
        self::assertEquals(60, $entries->get(5)->cr);
        self::assertEquals(84, $entries->get(6)->cr);
        self::assertEquals(436, $loan->client->getAccountBalance());
    }

    public function test_can_post_gl_entries_after_deducting_principal_over_multiple_deductions_for_a_single_repayment()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursed = Carbon::parse('Jan 11, 2017');

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => $disbursed,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                    'amount' => 6000,
                    'rate' => 4.5,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 1200])->id,
                    'fees' => collect([
                        Fee::whereName(Fee::ADMINISTRATION)->first(), // upfront = 5%
                        Fee::whereName(Fee::DISBURSEMENT)->first(), // amortized fee = 5%
                        Fee::whereName(Fee::ARRANGEMENT)->first() // amortized fee = 7%
                    ])->toArray(),
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        $loan = $loan->fresh('schedule');

        $firstRepayment = $loan->schedule->first();

        self::assertEquals(1614, $firstRepayment->amount);
        self::assertEquals(1200, $firstRepayment->principal);
        self::assertEquals(270, $firstRepayment->interest);
        self::assertEquals(930, $firstRepayment->getPaidPrincipal(false));

        // check corresponding ledger entries
        $entries = LedgerEntry::all();

        self::assertCount(3, $entries);
        self::assertEquals(1200, $entries->first()->dr); // total repayment amount deducted
        self::assertEquals(270, $entries->get(1)->cr); // interest
        self::assertEquals(930, $entries->get(2)->cr); // principal

        // let's credit this Client's account and continue with repayment
        $loan->client->update(['account_balance' => 2000]);

        $this->dispatch(new AutomatedLoanRepaymentJob($firstRepayment->due_date));

        $entries = LedgerEntry::all();

        self::assertCount(7, $entries);
        self::assertEquals(414, $entries->get(3)->dr);
        self::assertEquals(270, $entries->get(4)->cr);
        self::assertEquals(60, $entries->get(5)->cr);
        self::assertEquals(84, $entries->get(6)->cr);
        self::assertEquals(1586, $loan->client->getAccountBalance(false));
    }

    public function test_dont_update_repayment_to_defaulted_when_client_has_no_balance_if_some_amount_has_already_been_paid()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'amount' => 10000,
                    'rate' => 9,
                    'fees' => collect([
                        Fee::whereName(Fee::ADMINISTRATION)->first(), // upfront = 5%
                        Fee::whereName(Fee::DISBURSEMENT)->first(), // amortized fee = 5%
                    ])->toArray(),
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $disbursed_at = $approved_at = Carbon::today()->subWeekdays(24);

        $this->request->replace(compact('disbursed_at', 'approved_at'));

        $loan = $this->approveAndDisburseLoan($loan, $this->request);

        $loan->client->update(['account_balance' => 100]);

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        $loan = $loan->fresh('schedule');

        self::assertEquals(LoanRepayment::PART_PAYMENT, $loan->schedule->first()->status);

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        $loan = $loan->fresh();

        self::assertEquals(LoanRepayment::PART_PAYMENT, $loan->schedule->first()->status);
    }


}
