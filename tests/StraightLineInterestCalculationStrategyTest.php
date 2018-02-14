<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 1:49 PM
 */

use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Events\LoanDisbursedEvent;
use App\Jobs\AddLoanJob;
use Carbon\Carbon;


class StraightLineInterestCalculationStrategyTest extends TestCase
{
    public function test_able_to_generate_correct_loan_repayments_schedule_for_a_newly_created_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = factory(Loan::class)->make([
            'amount' => 10000,
            'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
            'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id,
            'rate' => 9,
            'interest_calculation_strategy' => Loan::STRAIGHT_LINE_STRATEGY,
            'fees' => [['rate' => 17, 'id' => 2]],
        ]);

        $this->request->merge($loan->toArray());

        $loan = dispatch(new AddLoanJob($this->request));

        $schedule = $loan->schedule;

        self::assertCount(24, $schedule);

        // amount, principal, interest and fees components are going to the same through, let's make sure of that
        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(1387.50, $repayment->amount, '', 0.1);
            self::assertEquals(900, $repayment->interest);
            self::assertEquals(416.67, $repayment->principal, '', 0.1);
            self::assertEquals(70.83, $repayment->fees, '', 0.1);
        });

        self::assertEquals(
            $loan->created_at->copy()->addWeekdays($loan->repaymentPlan->number_of_days)->startOfDay(),
            $schedule->first()->due_date
        );
        self::assertEquals(
            $loan->created_at->copy()->addWeekdays($loan->repaymentPlan->number_of_days * 2)->startOfDay(),
            $schedule->get(1)->due_date
        );
        self::assertEquals(1700, $loan->getTotalFees(false));
    }

    public function test_able_to_generate_correct_loan_repayments_schedule_for_after_disbursing_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $this->expectsEvents(LoanDisbursedEvent::class);

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $this->request->merge(
            factory(Loan::class)
                ->make([
                    'created_at' => Carbon::now()->isWeekend() ? Carbon::now()->addWeekday() : Carbon::now(),
                    'amount' => 10000,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
                    'repayment_plan_id' => $repaymentPlan->id,
                    'rate' => 9,
                    'interest_calculation_strategy' => Loan::STRAIGHT_LINE_STRATEGY,
                    'fees' => [['rate' => 17, 'id' => 2]],
                ])
                ->toArray()
        );

        $loan = dispatch(new AddLoanJob($this->request));

        $schedule = $loan->schedule;

        self::assertCount(24, $schedule);

        // amount, principal, interest and fees components are going to the same through, let's make sure of that
        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(1387.50, $repayment->amount, '', 0.1);
            self::assertEquals(900, $repayment->interest);
            self::assertEquals(416.67, $repayment->principal, '', 0.1);
            self::assertEquals(70.83, $repayment->fees, '', 0.1);
        });

        self::assertEquals(
            $loan->created_at->copy()->addWeekdays($repaymentPlan->number_of_days)->startOfDay(),
            $schedule->first()->due_date
        );

        self::assertEquals(
            $loan->created_at->copy()->addWeekdays($repaymentPlan->number_of_days * 2)->startOfDay(),
            $schedule->get(1)->due_date
        );

        self::assertEquals(1700, $loan->getTotalFees(false));

        // disburse loan and run assertions all over again
        $loan = $this->approveAndDisburseLoan($loan);

        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(1387.50, $repayment->amount, '', 0.1);
            self::assertEquals(900, $repayment->interest);
            self::assertEquals(416.67, $repayment->principal, '', 0.1);
            self::assertEquals(70.83, $repayment->fees, '', 0.1);
        });

        self::assertEquals(
            $loan->disbursed_at->copy()->addWeekdays($repaymentPlan->number_of_days)->startOfDay(),
            $schedule->first()->due_date
        );

        self::assertEquals(
            $loan->disbursed_at->copy()->addWeekdays($repaymentPlan->number_of_days * 2)->startOfDay(),
            $schedule->get(1)->due_date
        );

        self::assertEquals(1700, $loan->getTotalFees(false));
    }

    public function test_can_generate_correct_monthly_repayment_schedule()
    {
        $this->setAuthenticatedUserForRequest();

        $approvedAndDisbursedAt = Carbon::parse('Dec 12, 2016');

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'rate' => 9,
                    'amount' => 10000,
                    'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
                    'approved_at' => $approvedAndDisbursedAt,
                    'disbursed_at' => $approvedAndDisbursedAt,
                ])
                ->toArray()
        );

        $loan = dispatch(new AddLoanJob($this->request));

        self::assertCount(5, $loan->schedule);
        self::assertEquals('2017-01-11', $loan->schedule->first()->due_date->format('Y-m-d'));
        self::assertEquals('2017-01-11', $approvedAndDisbursedAt->addWeekdays(22)->format('Y-m-d'));
    }

    /**
     * @param $product
     * @param $amount
     * @param $rate
     * @param $tenure
     * @dataProvider getLoansProvider
     */
    public function test_can_generate_straight_line_repayment_schedule_which_is_exactly_equal_to_the_loan_amount(
        $product, $amount, $rate, $tenure
    ) {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, $product)
                ->make([
                    'amount' => $amount,
                    'rate' => $rate,
                    'tenure_id' => Tenure::whereNumberOfMonths($tenure)->first()->id,
                ])
                ->toArray()
        );

        $loan = dispatch(new AddLoanJob($this->request));

        self::assertEquals($amount, $loan->schedule->sum('principal'));
    }

    public function getLoansProvider()
    {
        return [
            ['staff', 6000, 2, 6],
            ['grz', 4000, 3.91, 9],
        ];
    }
}