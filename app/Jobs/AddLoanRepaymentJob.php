<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

namespace App\Jobs;

use App\Entities\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddLoanRepaymentJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var LoanRepayment
     */
    private $repayment;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanRepayment $repayment
     */
    public function __construct(Request $request, LoanRepayment $repayment = null)
    {
        $this->request = $request;
        $this->repayment = $repayment ?? new LoanRepayment;
    }

    /**
     * Execute the job.
     *
     * @return LoanRepayment
     */
    public function handle()
    {
        return DB::transaction(function () {
            return $this->saveOrUpdateLoanRepayment();
        });
    }

    private function saveOrUpdateLoanRepayment()
    {
        // these fields are set when an actual payment is made
        if ($this->repayment->exists) {
            $this->repayment->user_id = $this->request->user()->id ?? null;
            $this->repayment->has_been_paid = true;
            $this->repayment->repayment_timestamp = Carbon::now();
        }

        foreach ($this->repayment->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->repayment->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->repayment->save();

        return $this->repayment;
    }
}
