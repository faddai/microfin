<?php

namespace App\Jobs;

use App\Entities\Loan;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class RestructureLoanJob
{

    use DispatchesJobs;

    /**
     * @var Request
     */
    private $request;

    /**
     * The loan being restructured
     *
     * @var Loan
     */
    private $originalLoan;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Loan $loan
     */
    public function __construct(Request $request, Loan $loan)
    {
        $this->request = $request;
        $this->originalLoan = $loan;
    }

    /**
     * Execute the job.
     *
     * @return Loan
     */
    public function handle()
    {
        // create loan
        $loanRestructure = $this->dispatch(new AddLoanJob($this->request));

        // update existing loan to flag it as restructured
        $this->originalLoan->forceFill([
            'restructured_by' => $this->request->user()->id,
            'restructured_at' => Carbon::now(),
            'status' => Loan::RESTRUCTURED,
        ])->save();

        $loanRestructure->parent_loan_id = $this->originalLoan->id;

        $loanRestructure->save();

        return $loanRestructure;
    }
}
