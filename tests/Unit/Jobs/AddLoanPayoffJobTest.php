<?php

use App\Entities\Loan;
use App\Jobs\AddLoanPayoffJob;
use App\Entities\LoanPayoff;
use Tests\TestCase;

class AddLoanPayoffJobTest extends TestCase
{
    public function test_can_save_loan_payoff()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = factory(Loan::class, 'customer')->create();

        $this->request->merge([
            'principal' => 1200,
            'interest' => 100,
            'fees' => 50,
            'remarks' => 'Lorem ipsum'
        ]);

        $payoff = $this->dispatch(new AddLoanPayoffJob($this->request, $loan));

        self::assertInstanceOf(LoanPayoff::class, $payoff);
        self::assertEquals(LoanPayoff::PENDING, $payoff->status);
        self::assertEquals(1200, $payoff->principal);
        self::assertEquals(100, $payoff->interest);
        self::assertEquals(50, $payoff->fees);
        self::assertEquals(1350, $payoff->amount);
        self::assertEquals(0, $payoff->penalty);
        self::assertNotNull($payoff->remarks);
    }

    public function test_can_update_a_loan_payoff_after_it_has_been_saved()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = factory(Loan::class, 'customer')->create();

        $this->request->merge([
            'principal' => 1200,
            'interest' => 100,
            'fees' => 50,
            'remarks' => 'Lorem ipsum'
        ]);

        $payoff = $this->dispatch(new AddLoanPayoffJob($this->request, $loan));

        $this->request->merge(['principal' => 1000, 'remarks' => 'All done']);

        $payoff = $this->dispatch(new AddLoanPayoffJob($this->request, $loan, $payoff));

        self::assertEquals(1000, $payoff->principal);
        self::assertEquals(1150, $payoff->amount);
        self::assertEquals('All done', $payoff->remarks);
        self::assertEquals($loan->id, $payoff->loan_id);
    }

    public function test_can_clean_string_formatted_numbers_during_payoff()
    {
        $this->setAuthenticatedUserForRequest();

        $loan = factory(Loan::class, 'customer')->create();

        $this->request->merge([
            'principal' => '1,200',
            'interest' => '100',
            'fees' => '50.23'
        ]);

        $payoff = $this->dispatch(new AddLoanPayoffJob($this->request, $loan));

        self::assertEquals(1200, $payoff->principal);
        self::assertEquals(1350.23, $payoff->amount);
        self::assertEquals($loan->id, $payoff->loan_id);
    }

}
