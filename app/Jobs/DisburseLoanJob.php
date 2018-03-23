<?php

namespace App\Jobs;

use App\Entities\Loan;
use App\Events\LoanDisbursedEvent;
use App\Exceptions\LoanDisbursalException;
use App\Notifications\LoanDisbursedClientNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DisburseLoanJob
{

    use DispatchesJobs;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Loan
     */
    private $loan;

    /**
     * @var boolean
     */
    private $loanIsBackdated;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Loan $loan
     */
    public function __construct(Request $request, Loan $loan)
    {
        $this->request = $request;
        $this->loan = $loan;
        $this->loanIsBackdated = $this->getDateLoanIsDisbursed()->lt(Carbon::today());
    }

    /**
     * Execute the job.
     * @return Loan
     * @throws LoanDisbursalException
     */
    public function handle()
    {
        return DB::transaction(function () {
            $loan = $this->disburse();

            $this->regenerateRepaymentScheduleForLoan($loan);

            event(new LoanDisbursedEvent($loan));

            if ($this->request->filled('notify_client_of_disbursement')) {
                $loan->client->notify(new LoanDisbursedClientNotification($loan));
            }

            return $loan;
        });
    }

    /**
     * @return Loan
     * @throws LoanDisbursalException
     */
    private function disburse(): Loan
    {
        if (! $this->loan->isApproved()) {
            throw new LoanDisbursalException('You cannot disburse a loan that hasn\'t been approved');
        }

        // disbursing a loan isn't allowed to be mass assigned
        $this->loan->forceFill([
            'disburser_id' => $this->request->user()->id,
            'disbursed_at' => $this->getDateLoanIsDisbursed(),
            'status' => Loan::DISBURSED,
            'disbursal_remarks' => $this->request->get('disbursal_remarks')
        ])->save();

        return $this->loan;
    }

    /**
     * @param Loan $loan
     * @return mixed
     */
    private function regenerateRepaymentScheduleForLoan(Loan $loan)
    {
        return $this->dispatch(new GenerateLoanRepaymentScheduleJob($loan, true, $this->loanIsBackdated));
    }

    /**
     * @return Carbon
     */
    private function getDateLoanIsDisbursed(): Carbon
    {
        $date = Carbon::parse($this->request->get('disbursed_at'));

        // if disbursal is being done on a weekend, add a weekday
        return $date->isWeekend() ? $date->addWeekday() : $date;
    }
}