<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 16/04/2017
 * Time: 01:36
 */

namespace App\Jobs;

use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Entities\LoanStatement;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PostAccruedReceivablesToLoanAccountStatementJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, DispatchesJobs;

    /**
     * @var Loan
     */
    private $loan;

    /**
     * Create a new job instance.
     *
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            $posted = 0;

            // get due repayments
            // reject repayments with entries already posted to the loan statement
            // proceed with the remaining repayments and post their interest and fees due
            $this->loan->schedule()
                ->whereDate('due_date', '<=', Carbon::today())
                ->get()
                ->filter(function (LoanRepayment $repayment) {
                    return $this->entryHasNotBeenPostedAlready($repayment);
                })
                ->each(function (LoanRepayment $repayment) use (&$posted) {

                    logger('Post accrued receivables for #'. $repayment->loan->number .' to its Loan Statement', [
                        'dueDate' => $repayment->getDueDate()
                    ]);

                    if ($repayment->getInterest(false) > 0) {

                        // add accrued interest
                        $request = new Request([
                            'narration' => 'Interest accrued',
                            'dr' => $repayment->getInterest(false),
                            'value_date' => $repayment->due_date,
                            'created_at' => $repayment->due_date
                        ]);

                        $this->dispatch(new AddLoanStatementEntryJob($request, $repayment->loan));

                        $posted++;

                    }

                    // add accrued/due fees to loan statement
                    $repayment->loan->fees
                        ->reject(function (Fee $fee) {
                            return $fee->pivot->is_paid_upfront;
                        })
                        ->each(function (Fee $fee) use ($repayment, &$posted) {

                            if ($fee->pivot->amount > 0) {

                                $this->dispatch(new AddLoanStatementEntryJob(new Request([
                                    'narration' => $fee->name,
                                    'dr' => $fee->pivot->amount / $repayment->loan->tenure->number_of_months,
                                    'value_date' => $repayment->due_date,
                                    'created_at' => $repayment->due_date
                                ]), $repayment->loan));

                                $posted++;
                            }

                        });

                    logger(sprintf(
                        'Posted %d %s on %s for Loan #%s',
                        $posted, str_plural('entry', $posted), $repayment->getDueDate(), $repayment->loan->number
                    ));

                });
        });
    }

    private function entryHasNotBeenPostedAlready(LoanRepayment $repayment)
    {
        return LoanStatement::where('loan_id', $repayment->loan->id)
            ->whereHas('entries', function ($query) use ($repayment) {
                return $query->where('value_date', $repayment->due_date);
            })
            ->first() === null;
    }

}
