<?php

namespace App\Jobs;

use App\Entities\Accounting\Ledger;
use App\Entities\LoanPayoff;
use App\Entities\LoanRepayment;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApproveLoanPayoffJob
{
    use DispatchesJobs;

    /**
     * @var Request
     */
    private $request;
    /**
     * @var LoanPayoff
     */
    private $payoff;

    /**
     * @var float
     */
    private $amounts;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanPayoff $payoff
     */
    public function __construct(Request $request, LoanPayoff $payoff)
    {
        $this->request = $request;
        $this->payoff = $payoff;
        $this->amounts = collect($this->request->only(['principal', 'interest', 'fees', 'penalty']))
            ->map(function ($amount) {
                return (float)str_replace(',', '', $amount);
            })
            ->reject(function ($amount) {
                return $amount <= 0;
            });
    }

    /**
     * Execute the job.
     *
     * @throws \App\Exceptions\ClientTransactionException
     */
    public function handle()
    {
        return DB::transaction(function () {

            // update status to approved
            $this->payoff->approve($this->request);

            $this->depositPayoffAmountIntoClientAccount();

            $this->postLoanPayoffToGeneralLedger();

            // post the payoff to the loan statement
            $this->postLoanPayoffToLoanStatement();

            $this->markOutstandingRepaymentsAsPaid();

            return $this->payoff;
        });
    }

    private function postLoanPayoffToGeneralLedger()
    {
        $loan = $this->payoff->loan;

        $narration = sprintf('Loan payoff - %s', $loan->number);

        $entries = [
            [
                'dr' => $this->amounts->sum(),
                'ledger_id' => Ledger::whereCode(Ledger::CURRENT_ACCOUNT_CODE)->first()->id,
                'narration' => $narration
            ]
        ];

        if ($this->amounts->has('penalty')) {
            $entries[] = [
                'cr' => $this->amounts->get('penalty'),
                'ledger_id' => $loan->product->interestReceivableLedger->id,
                'narration' => $narration . ' - penalty charged'
            ];
        }

        if ($this->amounts->has('interest')) {
            $entries[] = [
                'cr' => $this->amounts->get('interest'),
                'ledger_id' => $loan->product->interestReceivableLedger->id,
                'narration' => $narration . ' - interest'
            ];
        }

        if ($this->amounts->has('principal')) {
            $entries[] = [
                'cr' => $this->amounts->get('principal'),
                'ledger_id' => $loan->product->principalLedger->id,
                'narration' => $narration . ' - principal'
            ];
        }

        if ($this->amounts->has('fees')) {
            $entries[] = [
                'cr' => $this->amounts->get('fees'),
                'ledger_id' => Ledger::whereCode(6002)->first()->id,
                'narration' => $narration . ' - fees'
            ];
        }

        $transaction = new Request([
            'loan_id' => $loan->id,
            'user_id' => $this->request->user()->id,
            'branch_id' => $this->request->user()->branch->id,
            'entries' => $entries
        ]);

        $this->dispatchNow(new AddLedgerTransactionJob($transaction));
    }

    /**
     * @throws \App\Exceptions\ClientTransactionException
     */
    private function depositPayoffAmountIntoClientAccount()
    {
        $this->request->merge([
            'cr' => $this->amounts->sum(),
            'user_id' => $this->request->user()->id,
            'narration' => 'Client deposit for loan payoff - ' . $this->payoff->loan->number,
            'branch_id' => $this->request->user()->branch->id,
            'value_date' => Carbon::now()
        ]);

        // bool flag: treat this as a nominal entry, don't post this transaction to the ledger
        // doesn't trigger repayment deductions
        // manually withdraw the deposited amount from Client account after recording the transaction
        if ($this->dispatch(new AddClientDepositJob($this->request, $this->payoff->loan->client, true))) {

            $this->request->merge([
                'cr' => 0,
                'dr' => $this->request->get('cr'),
                'narration' => str_replace('deposit', 'withdrawal', $this->request->get('narration'))
            ]);

            $this->dispatch(new AddClientWithdrawalJob($this->request, $this->payoff->loan->client, true));
        }
    }

    private function postLoanPayoffToLoanStatement()
    {
        $loan = $this->payoff->loan;

        $this->dispatch(new AddLoanStatementEntryJob(new Request([
            'cr' => $this->amounts->sum(),
            'narration' => sprintf('Loan payoff - %s', $loan->number),
            'value_date' => Carbon::now(),
        ]), $loan));
    }

    private function markOutstandingRepaymentsAsPaid()
    {
        $this->payoff->loan->schedule
            ->reject(function (LoanRepayment $repayment) {
                return $repayment->isFullyPaid();
            })
            ->each(function (LoanRepayment $repayment) {
                $repayment->markAsPaid();
            });
    }
}
