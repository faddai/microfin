<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/03/2017
 * Time: 4:02 PM
 */

use App\Entities\Client;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\Tenure;
use App\Events\LoanRepaymentDeductedEvent;
use App\Jobs\DeductRepaymentForLoansWithMissedDeductionWindowJob;
use App\Jobs\GenerateLoanRepaymentScheduleJob;
use App\Jobs\GetEffectiveLoanDeductionsJob;
use Carbon\Carbon;
use Tests\TestCase;

class GetEffectiveLoanDeductionsJobTest extends TestCase
{
    /**
     * Create 3 loans and generate their repayment schedules
     * deduct repayments
     */
    public function test_able_to_get_effective_loan_deductions()
    {
        $this->expectsEvents(LoanRepaymentDeductedEvent::class);

        $this->setAuthenticatedUserForRequest();

        // loan with repayment amount
        $loan1 = factory(Loan::class)
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => Carbon::parse('2 month ago'),
                'amount' => 2000,
                'rate' => 2.5,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 2])->id,
                'start_date' => Carbon::parse('December 8, 2016'),
                'client_id' => factory(Client::class)->create(['account_balance' => 1200])->id,
            ]);

        $loan2 = factory(Loan::class)
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => Carbon::parse('2 month ago'),
                'amount' => 3000,
                'rate' => 3,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                'start_date' => Carbon::parse('November 9, 2016'),
                'client_id' => factory(Client::class)->create(['account_balance' => 1200])->id,
            ]);

        $loan3 = factory(Loan::class)
            ->states('approved', 'disbursed')
            ->create([
                'disbursed_at' => Carbon::parse('1 month ago'),
                'amount' => 4000,
                'rate' => 4,
                'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 3])->id,
                'start_date' => Carbon::parse('November 9, 2016'),
                'client_id' => factory(Client::class)->create(['account_balance' => 1200])->id,
            ]);

        collect([$loan1, $loan2, $loan3])->each(function (Loan $loan) {
            $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan));
        });

        $this->dispatch(new DeductRepaymentForLoansWithMissedDeductionWindowJob);

        $this->request->merge(['disbursed_at' => Carbon::today()->subWeekday()]);

        $this->dispatch(new GetEffectiveLoanDeductionsJob($this->request));

        $loan1Schedule = LoanRepayment::schedule($loan1);
        $loan2Schedule = LoanRepayment::schedule($loan2);
        $loan3Schedule = LoanRepayment::schedule($loan3);

        self::assertCount(2, $loan1Schedule);
        self::assertEquals(1050, $loan1Schedule->first()->amount);
        self::assertEquals(1090, $loan2Schedule->first()->amount);
        self::assertEquals(1493.33, $loan3Schedule->first()->amount, '', 0.1);

        self::assertCount(2, LoanRepayment::all());
    }
}