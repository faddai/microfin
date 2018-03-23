<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/02/2017
 * Time: 16:31
 */

namespace App\Console\Commands;

use App\Jobs\PostDailyInterestAccruedToGeneralLedgerJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PostDailyInterestAccruedToGeneralLedgerCommand extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microfin:accrue-daily-interest {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accrues loan interests daily and posted to designated ledgers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->argument('date') ?? Carbon::today();

        $this->info('Posting accrued daily interest');

        $processed = $this->dispatch(new PostDailyInterestAccruedToGeneralLedgerJob($date));

        $this->info('Total loans processed: '. $processed);
    }
}
