<?php

use App\Entities\Loan;
use App\Entities\Permission;
use App\Entities\User;
use App\Events\LoanApprovedEvent;
use App\Events\LoanCreatedEvent;
use App\Jobs\AddLoanJob;
use App\Jobs\ApproveLoanJob;
use Tests\TestCase;


class ApproveLoanJobTest extends TestCase
{
    public function test_that_authenticated_user_loan_approval_permission()
    {
        $this->setAuthenticatedUserForRequest(Permission::APPROVE_LOAN);

        self::assertTrue($this->request->user()->can(Permission::APPROVE_LOAN));
    }

    public function test_able_to_approve_loan()
    {
        $this->expectsEvents([LoanCreatedEvent::class]);

        $this->setAuthenticatedUserForRequest(Permission::APPROVE_LOAN);

        $this->request->merge(factory(Loan::class)->make()->toArray());

        $loan = $this->dispatch(new AddLoanJob($this->request));

        $this->expectsEvents(LoanApprovedEvent::class);

        $this->dispatch(new ApproveLoanJob($this->request, $loan));

        self::assertNotNull($loan->approver_id);
        self::assertNotNull($loan->maturity_date);
        self::assertNotNull($loan->approved_at);
        self::assertInstanceOf(User::class, $loan->approvedBy);
    }
}