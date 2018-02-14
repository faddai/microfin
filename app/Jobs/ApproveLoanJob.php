<?php

namespace App\Jobs;

use App\Entities\Loan;
use App\Events\LoanApprovedEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ApproveLoanJob
{
    /**
     * @var Loan
     */
    private $loan;

    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Loan $loan
     */
    public function __construct(Request $request, Loan $loan)
    {
        $this->loan = $loan;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return Loan
     */
    public function handle()
    {
        return DB::transaction(function () {

            $this->updateLoan();

            event(new LoanApprovedEvent($this->loan));

            return $this->loan;
        });
    }

    private function updateLoan()
    {
        // approving loans isn't allowed to be mass assigned
        $this->loan->forceFill([
            'approver_id' => $this->request->user()->id,
            'approved_at' => $this->request->get('approved_at', Carbon::now()),
            'status' => Loan::APPROVED,
        ])->save();

        return $this->loan;
    }
}
