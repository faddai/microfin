<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/10/2016
 * Time: 4:44 PM
 */
use App\Entities\Loan;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Entities\User;
use App\Events\LoanApprovedEvent;
use App\Events\LoanCreatedEvent;
use App\Jobs\AddLoanJob;
use App\Jobs\ApproveLoanJob;
use Carbon\Carbon;
use Tests\TestCase;

class LoanRepaymentsControllerTest extends TestCase
{
    public function test_get_all_due_repayments()
    {
        $this->request->setUserResolver(function () {
            return factory(User::class)->create();
        });

        $this->makeLoans()
            ->make()
            ->each(function (Loan $loan) {
                $this->request->replace($loan->toArray());

                $this->expectsEvents(LoanCreatedEvent::class, LoanApprovedEvent::class);

                $loan = $this->dispatch(new AddLoanJob($this->request));

                $this->dispatch(new ApproveLoanJob($this->request, $loan));
            });

        $this->actingAs(factory(User::class)->create())
            ->get('repayments/due')
            ->assertSee('Repayments Due');
    }

    private function makeLoans()
    {
        return collect([
            factory(Loan::class)->make([
                'start_date' => Carbon::parse('Last week'),
                'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::WEEKLY])->id,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 2])->id,
                'rate' => 4,
            ]),

        ]);
    }
}
