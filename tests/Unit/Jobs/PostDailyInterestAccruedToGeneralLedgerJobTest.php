<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/02/2017
 * Time: 16:30
 */

use App\Entities\Accounting\LedgerTransaction;
use App\Entities\Loan;
use App\Entities\LoanProduct;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Jobs\AddLoanJob;
use App\Jobs\PostDailyInterestAccruedToGeneralLedgerJob;
use Carbon\Carbon;
use Tests\TestCase;


class PostDailyInterestAccruedToGeneralLedgerJobTest extends TestCase
{
    public function test_can_post_daily_interest_on_a_loan_to_the_general_ledger()
    {
        $this->setAuthenticatedUserForRequest();

        // create and disburse a customer loan
        $this->request->merge(
            factory(Loan::class, 'customer')
                ->states('approved', 'disbursed')
                ->make([
                    'amount' => 10000,
                    'disbursed_at' => Carbon::parse('Nov 15, 2016'),
                    'rate' => 9,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                    'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertCount(1, Loan::running());

        $processed = $this->dispatch(new PostDailyInterestAccruedToGeneralLedgerJob($loan->schedule->first()->due_date));

        self::assertEquals(1, $processed);
    }

    public function test_does_not_auto_accrue_interest_on_loans_that_have_reached_their_maturity_date()
    {
        /**
         * Create a backdated (matured) loan and attempt to accrue its daily interest
         * No accrual date is passed, defaults to today
         */
        $this->setAuthenticatedUserForRequest();

        // create and disburse a customer loan
        $this->request->merge(
            factory(Loan::class, 'customer')
                ->states('approved', 'disbursed')
                ->make([
                    'amount' => 10000,
                    'disbursed_at' => Carbon::parse('Nov 15, 2016'),
                    'rate' => 9,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                    'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertInstanceOf(Loan::class, $loan);
        self::assertCount(1, Loan::running());

        $processed = $this->dispatch(new PostDailyInterestAccruedToGeneralLedgerJob);

        self::assertEquals(0, $processed);
    }

    public function test_can_post_daily_interest_for_multiple_loans_to_the_general_ledger()
    {
        $this->setAuthenticatedUserForRequest();

        $disbursedAt = Carbon::parse('Nov 15, 2016');

        // create and disburse a customer loan
        factory(Loan::class, 'customer', 3)
            ->states('approved', 'disbursed')
            ->make([
                'amount' => 10000,
                'disbursed_at' => $disbursedAt,
                'rate' => 9,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
            ])
            ->map(function (Loan $loan) {
                $loan->amount = faker()->randomNumber(6);
                return $loan;
            })
            ->each(function (Loan $loan) {
                $this->request->merge($loan->toArray());

                $this->dispatch(new AddLoanJob($this->request));
            });

        self::assertCount(3, Loan::running());

        // all these loans have disbursal dates in the past so let's go through them
        // and post missed accruals
        $processed = $this->dispatch(new PostDailyInterestAccruedToGeneralLedgerJob(
            $disbursedAt->copy()->addWeekdays(Loan::first()->repaymentPlan->number_of_days)
        ));

        self::assertEquals(3, $processed);
        self::assertCount(3, LedgerTransaction::all());
        self::assertNotNull(LedgerTransaction::first()->loan_id);
    }

    /**
     * @expectedException App\Exceptions\LedgerEntryException
     * @expectedExceptionMessage You cannot post an entry for a Loan Product without configured Interest Ledgers
     */
    public function test_throws_an_exception_when_posting_ledger_entries_for_a_product_without_configured_ledgers()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class)
                ->states('approved', 'disbursed')
                ->make([
                    'amount' => 10000,
                    'disbursed_at' => Carbon::parse('3 months ago'),
                    'rate' => 9,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 5])->id,
                    'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id,
                    'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                    'loan_product_id' => factory(LoanProduct::class)->create(['name' => 'ZZZ', 'code' => 2119])->id,
                ])
                ->toArray()
        );

        $this->dispatch(new AddLoanJob($this->request));

        // let's make sure there is at least 1 running loan
        self::assertCount(1, Loan::running());

        $this->dispatch(new PostDailyInterestAccruedToGeneralLedgerJob);
    }
}