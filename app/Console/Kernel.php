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
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        RegisterRootUserCommand::class,
        PostAccruedReceivablesToLoanAccountStatementCommand::class,
        DeductLoanRepaymentCommand::class,
        DeductRepaymentForLoansWithMissedDeductionWindowCommand::class,
        PostDailyInterestAccruedToGeneralLedgerCommand::class,
        AccrueInterestAndFeesFromBeginningOfYearUpToDate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('loan:post-receivables-to-loan-statement')
            ->weekdays()
            ->at('17:00');

        $schedule
            ->command('loan:repay')
            ->weekdays()
            ->at('17:00');

        $schedule
            ->command('loan:recalibrate-missed-deductions')
            ->weekdays()
            ->between('8:00', '17:00')
            ->when(function () {
                return LoanRepayment::due()->unpaid()->whereStatus(null)->count() > 0;
            })
            ->withoutOverlapping();

        $schedule
            ->command('loan:accrue-daily-interest')
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
    }
}
