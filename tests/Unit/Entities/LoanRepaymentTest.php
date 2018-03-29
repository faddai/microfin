<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

use App\Entities\Client;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Jobs\AddLoanJob;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use Carbon\Carbon;
use Tests\TestCase;

class LoanRepaymentTest extends TestCase
{
    public function test_able_to_deduct_an_amount_from_client_account_balance()
    {
        $loan = factory(Loan::class)
            ->states('approved', 'disbursed')
            ->create([
                'client_id' => factory(Client::class)->create(['account_balance' => 400])->id,
            ]);

        $repayments = $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        $firstRepayment = $repayments->first();

        self::assertInstanceOf(LoanRepayment::class, $firstRepayment);

        $this->callPrivateMethod($firstRepayment, 'decrementClientAccountBalance', ['amount' => 200]);

        self::assertEquals(200, $this->callPrivateMethod($firstRepayment, 'getClientAccountBalance'));
    }

    /**
     * @expectedException App\Exceptions\InsufficientAccountBalanceException
     */
    public function test_deducting_an_amount_that_exceeds_client_account_balance_results_in_an_exception()
    {
        $loan = factory(Loan::class)
            ->states('approved', 'disbursed')
            ->create([
                'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 400])->id,
            ]);

        $repayments = $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        $firstRepayment = $repayments->first();

        $this->callPrivateMethod($firstRepayment, 'decrementClientAccountBalance', ['amount' => 500]);
    }

    public function test_that_correct_repayment_status_indicator_is_shown()
    {
        $loan = factory(Loan::class, 'customer')
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => Carbon::parse('Jan 25, 2016'),
                'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 400])->id,
                'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id,
                'amount' => 1000,
                'rate' => 4,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                'interest_calculation_strategy' => Loan::STRAIGHT_LINE_STRATEGY,
            ]);

        $schedule = $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));

        self::assertEquals(373.33, $schedule->first()->amount, '', 0.1);

        $schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(
                '<span class="label label-default">No Deduction made</span>',
                $repayment->getStatus()->get('label')
            );
        });

        $this->artisan('microfin:recalibrate-missed-deductions');

        self::assertEquals(
            '<span class="label label-success">Paid</span>',
            $loan->schedule->first()->getStatus()->get('label')
        );

        self::assertEquals(
            '<span class="label label-warning">Part payment</span>',
            $loan->schedule->get(1)->getStatus()->get('label')
        );

        self::assertEquals(
            '<span class="label label-danger">Defaulted</span>',
            $loan->schedule->last()->getStatus()->get('label')
        );
    }

    /**
     * Create 2 loans for a Client
     * Backdate first loan to 24 days ago and the second, 46 days ago
     * Loan 1 should have 1 defaulting repayment
     * Loan 2 should have 2 defaulting repayments
     * In all, Client should have 3 defaulting repayments
     */
    public function test_scopeGetDueRepaymentsForAClient()
    {
        $this->setAuthenticatedUserForRequest();

        $client = factory(Client::class, 'individual')->create(['account_balance' => 0]);

        factory(Loan::class, 2)
            ->states('approved', 'disbursed')
            ->make(['client_id' => $client->id, 'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id])
            ->each(function (Loan $loan, $i) {

                $loan->fill(['disbursed_at' => Carbon::today()->subWeekdays($i === 1 ? 46 : 24)]);

                $this->request->replace($loan->toArray());

                $this->dispatch(new AddLoanJob($this->request));
            });

        $this->artisan('microfin:recalibrate-missed-deductions');

        self::assertCount(2, $client->loans);
        self::assertCount(1, $client->loans->first()->schedule->where('status', LoanRepayment::DEFAULTED));
        self::assertCount(2, $client->loans->last()->schedule->where('status', LoanRepayment::DEFAULTED));
        self::assertCount(3, LoanRepayment::getDueRepaymentsForAClient($client));
    }
}