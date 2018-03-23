<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 08/04/2017
 * Time: 11:54
 */

namespace App\Listeners;


use App\Events\LoanDisbursedEvent;
use App\Jobs\AddLoanStatementEntryJob;
use App\Jobs\PostAccruedReceivablesToLoanAccountStatementJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostDisbursementToLoanAccountStatement
{

    use DispatchesJobs;

    public function handle(LoanDisbursedEvent $event)
    {
        DB::transaction(function () use ($event) {
            $loan = $event->loan;

            $this->dispatch(new AddLoanStatementEntryJob(new Request([
                'dr' => $loan->amount,
                'narration' => 'Loan disbursed',
                'value_date' => $loan->disbursed_at,
                'created_at' => $loan->disbursed_at,
            ]), $loan));

            // Post interest accrued to the loan statement for backdated loans
            $loan->isBackdated() && $this->dispatch(new PostAccruedReceivablesToLoanAccountStatementJob($loan));
        });
    }
}