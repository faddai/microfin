<?php

use App\Entities\Loan;
use App\Jobs\AddLoanJob;
use App\Jobs\DeclineLoanJob;


class DeclineLoanJobTest extends TestCase
{
    public function test_loan_can_be_declined()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(factory(Loan::class)->make()->toArray());

        $loan = dispatch(new AddLoanJob($this->request));

        dispatch(new DeclineLoanJob($this->request, $loan));

        $loan = $loan->fresh();

        self::assertEquals(Loan::DECLINED, $loan->status);
        self::assertNotNull($loan->declined_by);
        self::assertNotNull($loan->declined_at);
    }
}
