<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 1:49 PM
 */
namespace Tests\Unit;

use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Jobs\AddLoanJob;
use Carbon\Carbon;
use Tests\TestCase;

class ReducingBalanceInterestCalculationStrategyTest extends TestCase
{
    public function test_able_to_generate_correct_loan_repayments_schedule_for_a_newly_created_loan()
    {
        $this->setAuthenticatedUserForRequest();

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY]);

        $loan = factory(Loan::class)->make([
            'amount' => 10000,
            'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
            'repayment_plan_id' => $repaymentPlan->id,
            'rate' => 9,
            'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
            'fees' => [['rate' => 17, 'id' => 2]],
        ]);

        $this->request->merge($loan->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $schedule = $loan->schedule;

        self::assertCount(24, $schedule);
        self::assertEquals(1101.06, $schedule->first()->amount, '', 0.1);
        self::assertEquals(900, $schedule->first()->interest, '', 0.1);
        self::assertEquals(130.23, $schedule->first()->principal, '', 0.1);
        self::assertEquals(70.83, $schedule->first()->fees, '', 0.1);
        self::assertEquals(
            $loan->created_at->copy()->startOfDay()->addWeekdays($repaymentPlan->number_of_days),
            $schedule->first()->due_date
        );
        self::assertEquals(
            $loan->created_at->copy()->startOfDay()->addWeekdays($repaymentPlan->number_of_days * 2),
            $schedule->get(1)->due_date
        );
        self::assertEquals(1700, $loan->getTotalFees(false));
        self::assertEquals(10000, $schedule->sum('principal'), '', 0.1);
        self::assertEquals(14725.50, $schedule->sum('interest'), '', 0.1);
    }

    public function test_able_to_generate_loan_repayments_schedule_for_a_loan_with_weekly_repayments()
    {
        $this->setAuthenticatedUserForRequest();

        $repaymentPlan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::WEEKLY]);

        $loan = factory(Loan::class)->make([
            'amount' => 10000,
            'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
            'repayment_plan_id' => $repaymentPlan->id,
            'rate' => 9,
            'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
            'fees' => [['rate' => 17, 'id' => 2]],
        ]);

        $this->request->merge($loan->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $schedule = $loan->schedule;

        self::assertCount(96, $schedule);
        self::assertEquals(261.98, $schedule->first()->amount, '', 0.1);
        self::assertEquals(225, $schedule->first()->interest, '', 0.1);
        self::assertEquals(19.28, $schedule->first()->principal, '', 0.1);
        self::assertEquals(17.71, $schedule->first()->fees, '', 0.1);
        self::assertEquals(
            $loan->created_at->copy()->startOfDay()->addWeekdays($repaymentPlan->number_of_days),
            $schedule->first()->due_date
        );
        self::assertEquals(
            $loan->created_at->copy()->startOfDay()->addWeekdays($repaymentPlan->number_of_days * 2),
            $schedule->get(1)->due_date
        );
        self::assertEquals(1700, $loan->getTotalFees(false));
    }

    public function test_can_generate_reducing_balance_repayment_schedule_which_is_exactly_equal_to_the_loan_amount()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'staff')
                ->make([
                    'amount' => 6000,
                    'rate' => 2,
                    'tenure_id' => Tenure::whereNumberOfMonths(6)->first()->id,
                    'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(6000, $loan->schedule->sum('principal'));
    }

    /**
     * @todo fix test
     */
    public function _able_to_generate_correct_loan_repayments_schedule_for_after_disbursing_loan() {

        $this->setAuthenticatedUserForRequest();

        $reducingBalanceSchedule = $this->getSampleReducingBalanceSchedule();

        $loan = factory(Loan::class)
            ->states('approved', 'disbursed')
            ->make([
                'disbursed_at' => Carbon::parse('Jan 03, 2017'),
                'amount' => 10000,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
                'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id,
                'rate' => 9,
                'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                'fees' => [['rate' => 17, 'id' => 2]],
            ]);

        $this->request->merge($loan->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $schedule = $loan->schedule;

        $cummulativeInterest = 0;

        $principal = 0;

        $schedule->each(function (LoanRepayment $repayment, $_) use ($reducingBalanceSchedule, &$cummulativeInterest, &$principal) {

            $openingBalance = 10000 - $principal;

            logger('Repayment', [
                'openingBalance' => $openingBalance,
                'principal' => $principal,
                'interest' => $repayment->getInterest(),
                'fees' => $repayment->getFees(),
            ]);

            self::assertEquals(round($reducingBalanceSchedule[$_][0], 2), $openingBalance, '', 0.1);
            self::assertEquals($reducingBalanceSchedule[$_][1], $repayment->getPrincipal(false), '', 0.1);
            self::assertEquals($reducingBalanceSchedule[$_][2], $repayment->getInterest(false), '', 0.1);
            self::assertEquals($reducingBalanceSchedule[$_][3], $repayment->getFees(false), '', 0.1);

            $cummulativeInterest += round($repayment->interest, 2);

            self::assertEquals(
                $reducingBalanceSchedule[$_][4],
                $cummulativeInterest,
                "#{$_} Expected {$reducingBalanceSchedule[$_][4]} Cummulative interest but got {$cummulativeInterest}",
                0.1
            );

            $principal += $repayment->getPrincipal(false);
        });

        self::assertEquals(1700, $loan->getTotalFees(false));
        self::assertEquals($loan->disbursed_at->copy()->addWeekdays(20), $schedule->first()->due_date);
        self::assertEquals($loan->disbursed_at->copy()->addWeekdays(20 * 12), $schedule->last()->due_date);
    }

    public function getSampleReducingBalanceSchedule()
    {
        return [
            // Opening Bal Principal Interest Fees Cumm. Interest
            [10000.00, 130.23, 900.00, 70.83, 900.00],
            [9869.77, 141.95, 888.28, 70.83,  1788.28],
            [9727.82, 154.72, 875.51, 70.83,  2663.79],
            [9573.10, 168.65, 861.58, 70.83,  3525.37],
            [9404.45, 183.82, 846.41, 70.83,  4371.78],
            [9220.63, 200.37, 829.86, 70.83,  5201.64],
            [9020.26, 218.40, 811.83, 70.83,  6013.47],
            [8801.86, 238.06, 792.17, 70.83,  6805.64],
            [8563.80, 259.48, 770.75, 70.83,  7576.39],
            [8304.32, 282.84, 747.39, 70.83,  8323.78],
            [8021.48, 308.29, 721.94, 70.83, 9045.72],
            [7713.19, 336.04, 694.19, 70.83, 9739.91],
            [7377.15, 366.28, 663.95, 70.83, 10403.86],
            [7010.87, 399.25, 630.98, 70.83, 11034.84],
            [6611.62, 435.18, 595.05, 70.83, 11629.89],
            [6176.44, 474.34, 555.89, 70.83, 12185.78],
            [5702.10, 517.04, 513.19, 70.83, 12698.97],
            [5185.06, 563.57, 466.66, 70.83, 13165.63],
            [4621.49, 614.29, 415.94, 70.83, 13581.57],
            [4007.20, 669.58, 360.65, 70.83, 13942.22],
            [3337.62, 729.84, 300.39, 70.83, 14242.61],
            [2607.78, 795.52, 234.71, 70.83, 14477.32],
            [1812.26, 867.12, 163.11, 70.83, 14640.43],
            [45.14,   945.14, 85.07,  70.83, 14725.50]
        ];
    }
}
