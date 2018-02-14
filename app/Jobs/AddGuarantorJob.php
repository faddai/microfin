<?php

namespace App\Jobs;

use App\Entities\Guarantor;
use App\Entities\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;

class AddGuarantorJob
{
    use Queueable;

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
     * @return Guarantor
     */
    public function handle()
    {
        $guarantor = new Guarantor(['loan_id' => $this->loan->id]);

        if ($this->request->has('guarantor_id')) {
            $guarantor = Guarantor::findOrFail($this->request->get('guarantor_id'));
        }

        foreach ($guarantor->getFillable() as $fillable) {
            if ($this->request->has($fillable)) {
                $guarantor->{$fillable} = $this->request->get($fillable);
            }
        }

        $guarantor->save();

        return $guarantor;
    }
}
