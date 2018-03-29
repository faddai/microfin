<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/03/2017
 * Time: 4:01 PM
 */

use App\Entities\Loan;
use App\Entities\Role;
use App\Jobs\AddLoanJob;
use App\Notifications\NewLoanCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyUsersWhoCanApproveLoanListenerTest extends TestCase
{
    public function test_that_an_email_notification_is_sent_to_approvers_when_loan_is_created()
    {
        $this->setAuthenticatedUserForRequest();

        $approvers = $this->createUserWithARole(Role::BRANCH_MANAGER, 3);

        Notification::fake();

        $this->request->merge(factory(Loan::class)->make()->toArray());

        $this->dispatch(new AddLoanJob($this->request));

        Notification::assertSentTo($approvers, NewLoanCreatedNotification::class);
    }

    public function test_that_updating_a_loan_does_not_send_an_approval_notification()
    {
        $this->setAuthenticatedUserForRequest();

        $approver = $this->createUserWithARole(Role::BRANCH_MANAGER);

        Notification::fake();

        $loan = factory(Loan::class)->create();

        $this->request->merge(factory(Loan::class)->make([
            'status' => Loan::APPROVED,
            'client_id' => $loan->client->id,
        ])->toArray());

        $this->dispatch(new AddLoanJob($this->request, $loan));

        Notification::assertNotSentTo($approver, NewLoanCreatedNotification::class);
    }
}