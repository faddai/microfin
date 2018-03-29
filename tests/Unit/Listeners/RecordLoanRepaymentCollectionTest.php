<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/03/2017
 * Time: 3:50 PM
 */

use App\Entities\Client;
use App\Entities\Loan;
use App\Jobs\AutomatedLoanRepaymentJob;
use App\Entities\LoanRepaymentCollection;
use Carbon\Carbon;
use Tests\TestCase;

class RecordLoanRepaymentCollectionTest extends TestCase
{

    public function test_can_record_amount_collected_as_loan_repayment()
    {
        $loan = factory(Loan::class, 'customer')->create([
            'amount' => 1000,
            'rate' => 5
        ]);

        $this->request->merge(['disbursed_at' => Carbon::today()->subWeekdays(24)]);

        $this->approveAndDisburseLoan($loan, $this->request);

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        self::assertCount(1, LoanRepaymentCollection::all());
        self::assertEquals(133.33, LoanRepaymentCollection::first()->amount, '', 0.1);
    }

    public function test_can_record_multiple_amounts_collected_for_the_same_loan_repayment()
    {
        $client = factory(Client::class, 'individual')->create(['account_balance' => 0]);

        $loan = factory(Loan::class, 'customer')->create([
            'amount' => 1000,
            'rate' => 5,
            'client_id' => $client->id,
        ]);

        $this->request->merge(['disbursed_at' => Carbon::today()->subWeekdays(24)]);

        $this->approveAndDisburseLoan($loan, $this->request);

        $client->update(['account_balance' => 100]);

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        self::assertCount(1, LoanRepaymentCollection::all());
        self::assertEquals(100, LoanRepaymentCollection::first()->amount);

        $client->increment('account_balance', 100);

        $this->dispatch(new AutomatedLoanRepaymentJob($loan->schedule->first()->due_date));

        self::assertCount(2, LoanRepaymentCollection::all());
        self::assertEquals(33.33, LoanRepaymentCollection::find(2)->amount, '', 0.1);
        self::assertEquals(133.33, $loan->fresh()->schedule->first()->getTotalAmountPaid());
    }
}
