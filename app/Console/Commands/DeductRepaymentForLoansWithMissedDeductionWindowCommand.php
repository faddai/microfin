<?php

namespace App\Console\Commands;

use App\Jobs\DeductRepaymentForLoansWithMissedDeductionWindowJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DeductRepaymentForLoansWithMissedDeductionWindowCommand extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microfin:recalibrate-missed-deductions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Goes through repayments to attempt to do deductions for those that might have missed their repayment window';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->dispatch(new DeductRepaymentForLoansWithMissedDeductionWindowJob);

        $this->info('Deductions have finished processing');
    }
}
