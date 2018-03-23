<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 08/04/2017
 * Time: 05:21
 */

namespace App\Jobs;


use App\Entities\Loan;
use App\Entities\LoanStatement;
use App\Entities\LoanStatementEntry;
use Illuminate\Http\Request;

class AddLoanStatementEntryJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var LoanStatement
     */
    private $statement;
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
        $this->statement = LoanStatement::firstOrCreate(['loan_id' => $this->loan->id]);
        $this->entry = new LoanStatementEntry(['loan_statement_id' => $this->statement->id]);
    }

    /**
     * Execute the job.
     *
     * @return LoanStatementEntry
     */
    public function handle()
    {
        foreach ($this->entry->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $this->entry->{$fillable} = $this->request->get($fillable);
            }
        }

        $this->entry->save();

        $this->entry->balance = $this->getBalance();

        $this->entry->save();

        logger('Add entry in loan account statement', $this->entry->toArray());

        return $this->entry;
    }

    private function getBalance()
    {

        if ($this->loan->statement->entries->count()) {

            $this->loan = $this->loan->fresh('statement');

            $entries = $this->loan->statement->entries;

            return $entries->sum('cr') - $entries->sum('dr');
        }

        // loan has just been disbursed
        return $this->request->get('dr') * -1;
    }
}
