<?php

namespace App\Console\Commands;

use App\Entities\Loan;
use App\Jobs\PostAccruedReceivablesToLoanAccountStatementJob;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PostAccruedReceivablesToLoanAccountStatementCommand extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microfin:post-receivables-to-loan-statement {--include-matured-loans}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Posts interest & fees accrued for loans with due repayments to their Loan statements';

    /**
     * When a loan's repayment is due, record fees (breakdown) and interest accrued/receivable as Debit
     * to the loan account
     *
     * @return void
     */
    public function handle()
    {
        $includesMaturedLoans = $this->option('include-matured-loans');
        $loans = Loan::running();

        if (! $includesMaturedLoans) {
            $loans = $loans->reject(function (Loan $loan) {
                return $loan->isMatured();
            });
        }

        $loans->each(function (Loan $loan) {
            $this->dispatch(new PostAccruedReceivablesToLoanAccountStatementJob($loan));
        });
    }
}
