<?php

use App\Entities\Loan;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Jobs\AddLoanJob;
use Carbon\Carbon;
use Tests\TestCase;


/**
 * @todo Refactor tests to be more readable and independent
 *
 * Class GenerateLoanRepaymentScheduleJobTest
 */
class GenerateLoanRepaymentScheduleJobTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setAuthenticatedUserForRequest();
    }

    /**
     * Add a loan and verify whether a repayment schedule is generated
     */
    public function test_generate_schedule_for_loan()
    {
        $plan = RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::WEEKLY])->id;

        list($monthlyInterest, $totalInterest, $totalLoanAmount) = $this->getLoan($plan);

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $repaymentAmount = $totalLoanAmount / $loan->getNumberOfRepayments();

        self::assertEmpty($loan->payments);
        self::assertNotEmpty($loan->schedule);
        self::assertEquals($totalInterest, $loan->getTotalInterest(false));
        self::assertEquals($totalLoanAmount, $loan->getTotalLoanAmount(false));
        self::assertEquals($repaymentAmount, $loan->getRepaymentAmount(false), '', 0.1);
        self::assertEquals(12, $loan->getNumberOfRepayments());
        self::assertEquals(12, $loan->schedule->count());
    }

    public function test_a_valid_maturity_date_is_set()
    {
        $disburseAt = Carbon::parse('Feb 21, 2017');

        $this->request->merge(
            factory(Loan::class)
                ->states('approved', 'disbursed')
                ->make([
                    'disbursed_at' => $disburseAt,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                    'grace_period' => 3
                ])
                ->toArray()
            );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(
            $disburseAt->copy()->addWeekdays($loan->repaymentPlan->number_of_days + 3),
            $loan->schedule->first()->due_date
        );
        self::assertEquals(
            $disburseAt->copy()->addWeekdays(($loan->repaymentPlan->number_of_days * 3) + 3),
            $loan->maturity_date
        );
    }

    public function test_a_valid_interest_on_repayment_is_returned()
    {
        list($monthlyInterest, $totalInterest, $totalLoanAmount) = $this->getLoan();

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $repaymentAmount = $totalLoanAmount / $loan->getNumberOfRepayments();
        $principal = $repaymentAmount - $monthlyInterest;

        self::assertEquals($monthlyInterest, $loan->getMonthlyInterest(false));
        self::assertEquals($totalInterest, $loan->getTotalInterest(false));
        self::assertEquals($totalLoanAmount, $loan->getTotalLoanAmount(false));
        self::assertEquals($repaymentAmount, $loan->getRepaymentAmount(false), '', 0.1);
    }

    /**
     * @param int $repaymentPlanId
     * @param int $tenure
     * @return array
     */
    private function getLoan($repaymentPlanId = null, $tenure = null)
    {
        $monthlyRate = 5;
        $loanAmount = 1000;
        $tenure = $tenure ?: 3;
        $repaymentPlanId = $repaymentPlanId ?: RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id;

        $this->request->merge(factory(Loan::class)->make([
            'amount' => $loanAmount,
            'rate' => $monthlyRate,
            'tenure_id' => $tenure,
            'repayment_plan_id' => $repaymentPlanId,
        ])->toArray());

        $monthlyInterest = ($monthlyRate / 100) * $loanAmount; // 0.05 * 1000 = 50;
        $totalInterest = $tenure * $monthlyInterest; // 3 * 50 = 150

        $totalLoanAmount = $totalInterest + $loanAmount; // 150 + 1000 = 1150

        return array($monthlyInterest, $totalInterest, $totalLoanAmount);
    }
}
