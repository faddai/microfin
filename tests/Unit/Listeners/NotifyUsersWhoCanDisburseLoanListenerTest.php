<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/03/2017
 * Time: 4:02 PM
 */

use App\Entities\Loan;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\ApproveLoanJob;
use App\Notifications\LoanApprovedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyUsersWhoCanDisburseLoanListenerTest extends TestCase
{
    public function test_able_to_notify_disbursers_that_a_loan_has_been_approved()
    {
        $this->setAuthenticatedUserForRequest(Permission::APPROVE_LOAN);

        Notification::fake();

        $role = Role::firstOrCreate(['name' => 'accountant']);
        $role->attachPermission(Permission::firstOrCreate(['name' => Permission::DISBURSE_LOAN]));

        $accountants = factory(User::class, 3)->create()
            ->each(function (User $accountant) use ($role) {
                $accountant->attachRole($role);
            });

        $loan = factory(Loan::class)->create();

        $this->dispatch(new ApproveLoanJob($this->request, $loan));

        Notification::assertSentTo($accountants, LoanApprovedNotification::class);
    }

}
