<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class AccrueInterestAndFeesFromBeginningOfYearUpToDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microfin:recalibrate-missed-accruals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accrues interest/fees from Jan 1 to date of last data migration. This should be run once.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Post daily interest accrued for all working days from Jan 1 to date
        $beginningDate = Carbon::today()->startOfYear();

        while ($beginningDate->lte(Carbon::today())) {

            if ($beginningDate->isWeekday()) {
                $this->call('microfin:accrue-daily-interest', ['date' => $beginningDate]);
            }

            $beginningDate->addDay();
        }
    }
}
