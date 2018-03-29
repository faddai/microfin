<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/10/2016
 * Time: 4:52 PM
 */

use App\Entities\Client;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\Tenure;
use App\Events\LoanDisbursedEvent;
use App\Jobs\AddLoanJob;
use App\Jobs\ApproveLoanJob;
use App\Jobs\DisburseLoanJob;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use App\Jobs\LoanSearchJob;
use Carbon\Carbon;
use Tests\TestCase;

class LoanTest extends TestCase
{
    public function test_get_amount_paid_out_of_total_loan_amount()
    {
        $loan = factory(Loan::class, 'customer')
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => Carbon::parse('June 6 2016'),
                'amount' => 3000,
                'rate' => 5,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 1160])->id,
            ]);

        $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        self::assertCount(3, $loan->schedule);

        $this->artisan('microfin:repay', ['dueDate' => $loan->disbursed_at->addWeekdays
        ($loan->repaymentPlan->number_of_days)]);

        $loan = $loan->fresh();

        $totalAmountRepaid = $this->callPrivateMethod($loan, 'getAmountPaid', ['format' => false]);

        self::assertEquals(10, $loan->client->getAccountBalance(false));
        self::assertEquals(1150, $totalAmountRepaid);
    }

    public function test_get_pending_loans()
    {
        $this->setAuthenticatedUserForRequest();

        $createdAt = Carbon::today();

        factory(Loan::class, 3)
            ->make()
            ->each(function (Loan $loan) use ($createdAt) {
                $loan->forceFill(['created_at' => $createdAt]);

                $this->request->replace($loan->toArray());

                $this->dispatch(new AddLoanJob($this->request));
            });

        $this->request->replace([
            'status' => Loan::PENDING,
            'startDate' => $createdAt,
            'endDate' => $createdAt,
        ]);

        $loans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertCount(3, $loans);

        $loans->each(function (Loan $loan) {
            self::assertEquals(Loan::PENDING, $loan->status);
        });
    }

    public function test_get_approved_loans()
    {
        $this->setAuthenticatedUserForRequest();

        $loans = factory(Loan::class, 3)->make()->each(function (Loan $loan) {
            $this->request->replace($loan->toArray());

            return $this->dispatch(new AddLoanJob($this->request));
        });

        $loans->each(function (Loan $loan) {
            $this->request->merge(['approved_at' => Carbon::today()]);

            return $this->dispatch(new ApproveLoanJob($this->request, $loan));
        });

        $this->request->replace([
            'status' => Loan::APPROVED,
            'startDate' => Carbon::today(),
            'endDate' => Carbon::today(),
        ]);

        $approvedLoans = $this->dispatch(new LoanSearchJob($this->request));

        self::assertCount(3, $approvedLoans);

        $approvedLoans->each(function (Loan $loan) {
            self::assertEquals(Loan::APPROVED, $loan->status);
        });
    }

    public function test_get_disbursed_loans()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $createdAt = Carbon::today();

        $loans = factory(Loan::class, 3)->make()->each(function (Loan $loan) use ($createdAt) {

            $loan->forceFill(['created_at' => $createdAt]);

            $this->request->replace($loan->toArray());

            return $this->dispatch(new AddLoanJob($this->request));
        });

        $loans->each(function (Loan $loan) use ($createdAt) {
            $this->request->merge(['approved_at' => $createdAt]);
            return $this->dispatch(new ApproveLoanJob($this->request, $loan));
        });

        $this->request->replace([
            'status' => Loan::APPROVED,
            'startDate' => $createdAt,
            'endDate' => $createdAt,
        ]);

        $approvedLoans = $this->dispatch(new LoanSearchJob($this->request));

        $disbursedLoans = $approvedLoans->each(function (Loan $loan, $_) {

            if ($_ < 2) {
                return $this->dispatch(new DisburseLoanJob($this->request, $loan));
            }

            return $loan;
        });

        self::assertNotEquals(3, $disbursedLoans);

        $disbursedLoans->each(function (Loan $loan, $_) {
            if ($_ < 2) {
                self::assertEquals(Loan::DISBURSED, $loan->status);
            } else {
                self::assertEquals(Loan::APPROVED, $loan->status);
            }
        });
    }

    public function test_can_create_a_loan_with_a_zero_rate_using_straight_line_strategy()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'rate' => 0,
                    'amount' => 22993.54,
                    'tenure_id' => Tenure::whereNumberOfMonths(3)->first()->id,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertEquals(Loan::STRAIGHT_LINE_STRATEGY, $loan->interest_calculation_strategy);
        self::assertEquals(0, $loan->rate);
        self::assertEquals(0, $loan->getMonthlyRateInPercentage());
        self::assertEquals(7664.51, $loan->schedule->first()->amount, '', 0.1);
    }

    public function test_can_create_a_loan_with_a_zero_rate_using_reducing_balance_strategy()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'rate' => 0,
                    'amount' => 22993.54,
                    'tenure_id' => Tenure::whereNumberOfMonths(3)->first()->id,
                    'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertEquals(Loan::REDUCING_BALANCE_STRATEGY, $loan->interest_calculation_strategy);
        self::assertEquals(0, $loan->rate);
        self::assertEquals(0, $loan->getMonthlyRateInPercentage());
        self::assertEquals(0, $loan->schedule->first()->amount);
    }

    public function test_can_get_correct_loan_balance_for_a_loan_with_no_fees()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursed_at = $approved_at = Carbon::today()->subWeekdays(24 * 5);

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'amount' => 10000,
                    'rate' => 9,
                    'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->request->replace(compact('approved_at', 'disbursed_at'));

        $this->approveAndDisburseLoan($loan, $this->request);

        // repayment statuses are automatically set to default for backdated loans
        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(LoanRepayment::DEFAULTED, $repayment->status);
        });

        // calc total loan = principal + interest
        // 10000 + $loan->getTotalInterest()
        self::assertEquals(14500, $loan->getBalance(false));

        // overwrite Client account balance
        $loan->client->update(['account_balance' => 500]);

        $this->artisan('microfin:repay', ['dueDate' => $loan->schedule->first()->due_date]);

        self::assertEquals(14000, $loan->fresh()->getBalance(false));
        self::assertEquals('Active', $loan->fresh()->getStatus());
    }

    public function test_can_get_correct_loan_balance_for_a_loan_with_fees_that_has_upfront_charges()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursed_at = $approved_at = Carbon::today()->subWeekdays(24 * 5);

        $columns = ['id', 'rate', 'is_paid_upfront'];

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'amount' => 10000,
                    'rate' => 9,
                    'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
                    'fees' => collect([
                        Fee::whereName(Fee::ADMINISTRATION)->first($columns), // upfront = 5%
                        Fee::whereName(Fee::DISBURSEMENT)->first($columns)->fill(['rate' => 10]), // amortized fee = 10%
                    ])->toArray()
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->request->replace(compact('approved_at', 'disbursed_at'));

        $this->approveAndDisburseLoan($loan, $this->request);

        // repayment statuses are automatically set to default for backdated loans
        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(LoanRepayment::DEFAULTED, $repayment->status);
        });

        // calc total loan = principal + interest + fees (including upfront charges)
        self::assertEquals(16000, $loan->getTotalLoanAmount(false));
        self::assertEquals(15500, $loan->getBalance(false));
        self::assertEquals(500, $loan->getAmountPaid(false));

        // overwrite Client account balance
        $loan->client->update(['account_balance' => 500]);

        // repayment with amount in Client's account
        $this->artisan('microfin:repay', ['dueDate' => $loan->schedule->first()->due_date]);

        self::assertEquals(15000, $loan->fresh()->getBalance(false));

        // pay full amount for the loan
        $loan->client->update(['account_balance' => 15000]);

        $loan->schedule->each(function (LoanRepayment $repayment) {
            $this->artisan('microfin:repay', ['dueDate' => $repayment->due_date]);
        });

        $loan = $loan->fresh();

        self::assertEquals(0, $loan->getBalance(false));
        self::assertEquals(16000, $loan->getAmountPaid(false));
        self::assertEquals('Closed', $loan->getStatus());
    }

}
