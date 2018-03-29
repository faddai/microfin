<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/12/2017
 * Time: 3:59 PM
 */

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerEntry;
use App\Entities\Accounting\LedgerTransaction;
use App\Entities\Client;
use App\Entities\ClientTransaction;
use App\Entities\Fee;
use App\Entities\Loan;
use App\Entities\RepaymentPlan;
use App\Entities\Tenure;
use App\Jobs\AddLoanJob;
use Carbon\Carbon;
use Tests\TestCase;

class LoanDisbursalListenerTest extends TestCase
{
    public function test_that_a_client_account_is_credited_when_loan_amount_is_disbursed()
    {
        $this->setAuthenticatedUserForRequest();

        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'disbursed_at' => Carbon::parse('Feb 20, 2017'),
                    'amount' => 10000,
                    'rate' => 9,
                    'tenure_id' => Tenure::firstOrCreate(['number_of_months' => 24])->id,
                    'repayment_plan_id' => RepaymentPlan::firstOrCreate(['label' => RepaymentPlan::MONTHLY])->id,
                    'client_id' => factory(Client::class, 'individual')->create(['account_balance' => 0])->id,
                    'interest_calculation_strategy' => Loan::REDUCING_BALANCE_STRATEGY,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(0, $loan->client->getAccountBalance(false));

        $this->approveAndDisburseLoan($loan);

        self::assertEquals(10000, $loan->client->getAccountBalance(false));
        self::assertEquals(Carbon::parse('Feb 20, 2017'), $loan->disbursed_at);
    }

    /**
     * When a loan (with an upfront fee) is disbursed
     *
     * 1. Fee is deducted and recorded in its income ledger
     * 2. Current Account ledger is credited with loan principal minus fee charged
     * 3. Principal ledger for the loan product is debited with loan amount
     * 4. Client account balance is incremented with the loan amount minus fee charged (nominal)
     */
    public function test_that_appropriate_ledger_transactions_are_recorded_after_disbursing_a_loan_with_upfront_fees()
    {
        $this->setAuthenticatedUserForRequest();

        $fees = collect([
            Fee::whereName(Fee::ADMINISTRATION)->first()->fill(['rate' => 2.5]),
            Fee::whereName(Fee::PROCESSING)->first()->fill(['rate' => 1.5]),
            // override amortized fee to be paid upfront
            Fee::whereName(Fee::ARRANGEMENT)->first()->fill(['rate' => 1, 'is_paid_upfront' => 1])
        ])->toArray();

        // create a customer loan
        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'amount' => 10000,
                    'fees' => $fees,
                    'client_id' => factory(Client::class)->create(['account_balance' => 0])->id,
                ]) // deductable fees = 0.015 * 10000 = 150
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(500, $loan->getTotalFees());
        self::assertEquals(350, $loan->getUpfrontFees());

        $loan = $this->approveAndDisburseLoan($loan);

        $transaction = LedgerTransaction::with('entries')->first();

        self::assertEquals($transaction->entries->sum('dr'), $transaction->entries->sum('cr'));
        self::assertEquals(9650, $loan->client->getAccountBalance(false));
        self::assertEquals(
            9650,
            LedgerEntry::where('ledger_id', Ledger::whereCode(Ledger::CURRENT_ACCOUNT_CODE)->first()->id)->first()->cr
        );
        self::assertEquals(250, LedgerEntry::where('ledger_id', $loan->fees->first()->incomeLedger->id)->first()->cr);
        self::assertEquals(100, LedgerEntry::where('ledger_id', $loan->fees->last()->incomeLedger->id)->first()->cr);
        self::assertEquals(10000, LedgerEntry::where('ledger_id', $loan->product->principalLedger->id)->first()->dr);
        self::assertEquals(0, $loan->fees->first()->incomeLedger->dr);

        self::assertCount(1, ClientTransaction::all());
        self::assertNotNull($transaction->loan_id);
    }

    /**
     * When a loan (without an upfront fee) is disbursed
     *
     * 1. Current Account ledger is credited with loan principal minus fee charged
     * 2. Principal ledger for the loan product is debited with loan amount
     * 3. Client account balance is incremented with the loan amount minus fee charged (nominal)
     */
    public function test_that_appropriate_ledger_transactions_are_recorded_after_disbursing_a_loan_without_upfront_fees()
    {
        $this->setAuthenticatedUserForRequest();

        $fees = collect([
            Fee::whereName(Fee::ARRANGEMENT)->first()->fill(['rate' => 2.5]),
            Fee::whereName(Fee::PROCESSING)->first()->fill(['rate' => 1.5])
        ])->toArray();

        // create a customer loan
        $this->request->merge(
            factory(Loan::class, 'customer')
                ->make([
                    'amount' => 10000,
                    'fees' => $fees,
                    'client_id' => factory(Client::class)->create(['account_balance' => 0])->id,
                ])
                ->toArray()
        );

        $loan = $this->dispatch(new AddLoanJob($this->request));

        self::assertEquals(400, $loan->getTotalFees());
        self::assertEquals(0, $loan->getUpfrontFees());

        $this->approveAndDisburseLoan($loan);

        $transaction = LedgerTransaction::with('entries')->first();

        self::assertCount(2, $transaction->entries);
        self::assertEquals(10000, $transaction->entries->first()->dr);
        self::assertEquals(0, $transaction->entries->first()->cr);
        self::assertEquals(10000, $transaction->entries->last()->cr);
        self::assertEquals(0, $transaction->entries->last()->dr);
        self::assertEquals(10000, $loan->client->getAccountBalance(false));
        self::assertNotNull($transaction->loan_id);
    }
}