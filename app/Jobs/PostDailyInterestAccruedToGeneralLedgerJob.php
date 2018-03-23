<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/02/2017
 * Time: 16:31
 */

namespace App\Jobs;

use App\Entities\Loan;
use App\Exceptions\LedgerEntryException;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PostDailyInterestAccruedToGeneralLedgerJob
{
    use DispatchesJobs;

    /**
     * @var Carbon
     */
    private $accrualDate;

    public function __construct(Carbon $date = null)
    {
        $this->accrualDate = $date ?? Carbon::today();
    }

    /**
     * Execute the job.
     *
     * @return int
     * @throws \App\Exceptions\LedgerEntryException
     */
    public function handle()
    {
        return DB::transaction(function () {

            $processed = 0;

            // retrieve actively running loans
            // get the daily applicable interest for the period?
            // create a ledger transaction to post the entries
            Loan::active()
                ->get()
                ->filter(function (Loan $loan) {
                    return $this->getLoansThatHaventMatured($loan);
                })
                ->each(function (Loan $loan) use (&$processed) {
                    $this->postTransactionToLedger($loan->getDailyInterestForTheMonth($this->accrualDate), $loan);
                    $processed++;
                });

            logger(sprintf('Posted %d Daily Interest Accruals to the General Ledger', $processed));

            return $processed;
        });
    }

    /**
     * Filtering is done at this level because not all loans have maturity date set in the database
     * This is because the maturity date is retrieved via an accessor
     *
     * @todo save maturity date to the database so that query can be scoped to return immatured loans
     * @see Loan::getMaturityDateAttribute()
     * @param Loan $loan
     * @return bool
     */
    private function getLoansThatHaventMatured(Loan $loan) {
        return $loan->maturity_date > $this->accrualDate->format('Y-m-%');
    }

    /**
     * @param $dailyInterest
     * @param Loan $loan
     * @return mixed
     * @throws LedgerEntryException
     */
    private function postTransactionToLedger($dailyInterest, Loan $loan)
    {
        if (! $loan->product || $loan->product->interestReceivableLedger === null) {
            throw new LedgerEntryException('You cannot post an entry for a Loan Product without configured Interest Ledgers');
        }

        $narration = 'Daily Interest Accrued - ' . $loan->number;

        $valueDate = $this->accrualDate;

        logger($narration, compact('dailyInterest', 'valueDate'));

        return $this->dispatch(new AddLedgerTransactionJob(new Request([
            'branch_id' => $loan->createdBy->branch->id,
            'loan_id' => $loan->id,
            'entries' => [
                [
                    'dr' => $dailyInterest,
                    // Ledger to collect proceeds depending on loan product
                    'ledger_id' => $loan->product->interestReceivableLedger->id,
                    'narration' => $narration,
                ],
                [
                    'cr' => $dailyInterest,
                    'ledger_id' => $loan->product->interestIncomeLedger->id,
                    'narration' => $narration,
                ]
            ],
            'value_date' => $this->accrualDate
        ])));
    }
}