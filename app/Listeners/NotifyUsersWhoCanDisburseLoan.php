<?php

namespace App\Listeners;

use App\Entities\User;
use App\Events\LoanApprovedEvent;
use App\Notifications\LoanApprovedNotification;


class NotifyUsersWhoCanDisburseLoan
{
    /**
     * Handle the event.
     *
     * @param  LoanApprovedEvent  $event
     * @return void
     */
    public function handle(LoanApprovedEvent $event)
    {
        User::disbursers()->each(function (User $disburser) use ($event) {
            $disburser->notify(new LoanApprovedNotification($event->loan));
        });
    }
}
