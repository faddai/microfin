<?php

namespace App\Listeners;

use App\Jobs\AddUserJob;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class RecordUserLastSuccessfulLogin implements ShouldQueue
{
    use InteractsWithQueue, DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param Login $event
     */
    public function handle(Login $event)
    {
        $this->dispatch(new AddUserJob(new Request(['last_login' => Carbon::now()]), $event->user));
    }
}
