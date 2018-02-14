<?php

namespace App\Jobs;

use App\Entities\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Http\Request;


class GetEffectiveLoanDeductionsJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return LoanRepayment
     */
    public function handle()
    {
        $startDate = $this->request->get('startDate', Carbon::yesterday());
        $endDate = $this->request->get('endDate', Carbon::today());

        return LoanRepayment::with(['loan', 'loan.client'])
            ->whereBetween('repayment_timestamp', [$startDate, $endDate])
            ->get();
    }
}