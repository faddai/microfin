<?php

namespace App\Jobs\Exports;

use App\Entities\Loan;
use App\Entities\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetDataForLoanScheduleExport
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
     * GetDataForLoanScheduleExport constructor.
     * @param Request $request
     * @param Loan $loan
     */
    public function __construct(Request $request, Loan $loan)
    {
        $this->request = $request;
        $this->loan = $loan;
    }

    /**
     * @return Collection
     */
    public function handle()
    {
        $loan = $this->loan->load('schedule');

        $schedule = $loan->schedule->map(function (LoanRepayment $repayment, $i) {

            return [
                '#' => $i + 1,
                'Due Date' => $repayment->due_date ? $repayment->due_date->format(config('microfin.dateFormat')) : 'n/a',
                'Principal' => $repayment->getPrincipal(),
                'Paid principal' => $repayment->getPaidPrincipal(),
                'Interest' => $repayment->getInterest(),
                'Paid interest' => $repayment->getPaidInterest(),
                'Fees' => $repayment->getFees(),
                'Paid fees' => $repayment->getPaidFees(),
                'Outstanding' => $repayment->getOutstandingRepaymentAmount(),
            ];
        });

        $schedule->meta = collect([
            'Customer Name' => $loan->client->getFullName(),
            'Customer Number' => '\''. $loan->client->account_number, // make a number appear as string so it doesn't get truncated
            'Loan Number' => '\''. $loan->number,
            'Maturity Date' => $loan->maturity_date ? '"'. $loan->maturity_date->format(config('microfin.dateFormat')) .'"' : 'n/a',
            'Balance' => '"'. number_format($loan->getBalance(false) * -1, 2) .'"',
        ]);

        return $schedule;
    }
}
