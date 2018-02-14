<?php

namespace App\Listeners;

use App\Entities\User;
use App\Events\LoanCreatedEvent;
use App\Notifications\NewLoanCreatedNotification;


class NotifyUsersWhoCanApproveLoan
{
    /**
     * Handle the event.
     *
     * @param  LoanCreatedEvent  $event
     * @return void
     */
    public function handle(LoanCreatedEvent $event)
    {
        $loan = $event->loan;

        User::approvers()->each(function (User $approver) use ($loan) {
            $approver->notify(new NewLoanCreatedNotification($loan));
        });
    }
}
