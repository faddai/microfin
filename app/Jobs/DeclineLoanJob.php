<?php

namespace App\Jobs;

use App\Entities\Loan;
use App\Events\LoanDeclinedEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DeclineLoanJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Loan
     */
    private $loan;

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
    }

    /**
     * Execute the job.
     *
     * @return Loan
     */
    public function handle()
    {
        return DB::transaction(function () {
            $loan = $this->decline();

            event(new LoanDeclinedEvent($loan));

            return $loan;
        });
    }

    private function decline()
    {
        $this->loan->forceFill([
            'status' => Loan::DECLINED,
            'declined_by' => $this->request->user()->id,
            'declined_at' => Carbon::now()
        ])->save();

        return $this->loan;
    }
}
