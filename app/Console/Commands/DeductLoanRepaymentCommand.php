<?php

namespace App\Console\Commands;

use App\Jobs\AutomatedLoanRepaymentJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeductLoanRepaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microfin:repay {dueDate?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes loan repayments that are due on a given date (default is today). Use date format accepted by Carbon';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $dueDate = $this->argument('dueDate');

        $dueDate = $dueDate ? Carbon::parse($dueDate) : Carbon::today();

        dispatch(new AutomatedLoanRepaymentJob($dueDate));
    }
}
