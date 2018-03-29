<?php

use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\Tenure;
use App\Events\LoanCreatedEvent;
use App\Jobs\AddLoanJob;
use App\Jobs\DeclineLoanJob;
use App\Jobs\DeductRepaymentForLoansWithMissedDeductionWindowJob;
use App\Jobs\RestructureLoanJob;
use Carbon\Carbon;
use Tests\TestCase;

class RestructureLoanJobTest extends TestCase
{
    public function test_can_restructure_a_loan()
    {
        // create and disburse a backdated loan, make no payment
        // use balance from loan to create a new loan
        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoanApplication();

        self::assertInstanceOf(Loan::class, $loan);
        self::assertEquals(1000, $loan->getPrincipalAmount(false));
        self::assertEquals(1250, $loan->getBalance(false)); // no payments have been made yet
        self::assertCount(5, $loan->schedule);

        $loan->schedule->each(function (LoanRepayment $repayment) {
            self::assertEquals(LoanRepayment::DEFAULTED, $repayment->status);
        });

        // restructure loan
        $this->expectsEvents(LoanCreatedEvent::class);

        $newLoan = $this->restructureLoan($loan, ['disbursed_at' => $loan->schedule->last()->due_date]);

        self::assertEquals(Loan::PENDING, $newLoan->status);
        self::assertEquals($loan->getBalance(), $newLoan->getPrincipalAmount());
        self::assertEquals(Loan::RESTRUCTURED, $loan->status);
        self::assertNotNull($loan->restructured_by);
        self::assertNotNull($loan->restructured_at);
        self::assertNotNull($newLoan->parent_loan_id);
    }

    public function test_can_reverse_restructure_when_the_newly_created_loan_is_declined()
    {
        // create and disburse a loan
        // restructure loan (renders loan inactive)
        // decline newly created loan
        // update the restructured loan to have its previous status (running loan)

        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoanApplication(['interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY]);

        $restructure = $this->restructureLoan($loan);

        $declinedLoan = $this->dispatch(new DeclineLoanJob($this->request, $restructure));

        $loan = $loan->fresh();

        self::assertEquals(Loan::DECLINED, $declinedLoan->status);
        self::assertNotNull($declinedLoan->declined_by);
        self::assertInstanceOf(Carbon::class, $declinedLoan->declined_at);
        self::assertEquals(Loan::DISBURSED, $loan->status);
        self::assertEquals($restructure->parent_loan_id, $loan->id); // don't get rid of linking a restructure to its parent
        self::assertNull($loan->restructured_at);
        self::assertNull($loan->restructured_by);
    }

    public function test_if_loan_is_restructured()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = $this->createLoanApplication(['interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY]);

        $restructure = $this->restructureLoan($loan);

        self::assertTrue($loan->isRestructured());
        self::assertTrue($restructure->isPending());
        self::assertNotEquals($restructure->amount, $loan->amount);
        self::assertNotEquals($restructure->rate, $loan->rate);
        self::assertNotEquals($restructure->interest_calculation_strategy, $loan->interest_calculation_strategy);
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function createLoanApplication(array $data = [])
    {
        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'amount' => 1000,
                    'rate' => 5,
                    'disbursed_at' => Carbon::today()->subWeekdays(23 * 6),
                    'tenure_id' => Tenure::whereNumberOfMonths(5)->first()->id,
                ])
                ->toArray()
        );

        $this->request->merge($data);

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->approveAndDisburseLoan($loan);

        $loan->client()->update(['account_balance' => 0]);

        $this->dispatch(new DeductRepaymentForLoansWithMissedDeductionWindowJob);

        $loan = $loan->fresh('schedule');

        return $loan;
    }

    /**
     * @param Loan $loan
     * @param array $data
     * @return Loan
     */
    private function restructureLoan(Loan $loan, array $data = []): Loan
    {
        $this->request->merge([
            'amount' => $loan->getBalance(false),
            'rate' => 4.5,
            'interest_calculation_strategy' => Loan::STRAIGHT_LINE_STRATEGY,
        ]);

        $this->request->merge($data);

        return $this->dispatch(new RestructureLoanJob($this->request, $loan));
    }

}
