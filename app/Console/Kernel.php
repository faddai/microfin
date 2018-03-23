<?php

namespace App\Console;

use App\Console\Commands\AccrueInterestAndFeesFromBeginningOfYearUpToDate;
use App\Console\Commands\DeductLoanRepaymentCommand;
use App\Console\Commands\DeductRepaymentForLoansWithMissedDeductionWindowCommand;
use App\Console\Commands\PostAccruedReceivablesToLoanAccountStatementCommand;
use App\Console\Commands\PostDailyInterestAccruedToGeneralLedgerCommand;
use App\Console\Commands\RegisterRootUserCommand;
use App\Entities\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('microfin:post-receivables-to-loan-statement')
            ->weekdays()
            ->at('17:00');

        $schedule
            ->command('microfin:repay')
            ->weekdays()
            ->at('17:00');

        $schedule
            ->command('microfin:recalibrate-missed-deductions')
            ->weekdays()
            ->between('8:00', '17:00')
            ->when(function () {
                return LoanRepayment::due()->unpaid()->whereStatus(null)->count() > 0;
            })
            ->withoutOverlapping();

        $schedule
            ->command('microfin:accrue-daily-interest')
            ->weekdays()
            ->at('17:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');

        $this->load(__DIR__.'/Commands');
    }
}
