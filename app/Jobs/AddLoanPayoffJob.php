<?php

namespace App\Jobs;

use App\Entities\Loan;
use App\Entities\LoanPayoff;
use Illuminate\Http\Request;

class AddLoanPayoffJob
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
     * @var LoanPayoff
     */
    private $payoff;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Loan $loan
     * @param LoanPayoff $payoff
     */
    public function __construct(Request $request, Loan $loan, LoanPayoff $payoff = null)
    {
        $this->request = $request;
        $this->loan = $loan;
        $this->payoff = $payoff ?? new LoanPayoff(['created_by' => $this->request->user()->id]);
    }

    /**
     * Execute the job.
     *
     * @return LoanPayoff
     */
    public function handle()
    {
        if (! $this->payoff->exists) {
            $this->payoff->status = Loan::PENDING;
        }

        foreach ($this->payoff->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {

                $data = $this->request->get($fillable);

                if (in_array($fillable, ['principal', 'interest', 'fees', 'penalty'], true)) {
                    $data = str_replace(',', '', $this->request->get($fillable));
                }

                $this->payoff->{$fillable} = $data;
            }
        }

        $this->payoff->amount = $this->getTotalAmountForPayOff();

        $this->loan->payoff()->save($this->payoff);

        return $this->payoff;
    }

    private function getTotalAmountForPayOff()
    {
        return collect($this->request->only(['principal', 'interest', 'fees']))
            ->map(function ($amount) { return str_replace(',', '', $amount); })
            ->sum();
    }
}
