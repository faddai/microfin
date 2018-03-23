<?php

namespace App\Jobs;

use App\Entities\Guarantor;
use App\Entities\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        if (! $this->request->filled('name')) {
            throw new BadRequestHttpException('You must provide at least a name for the guarantor');
        }

        $guarantor = new Guarantor(['loan_id' => $this->loan->id]);

        if ($this->request->filled('guarantor_id')) {
            $guarantor = Guarantor::findOrFail($this->request->get('guarantor_id'));
        }

        foreach ($guarantor->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $guarantor->{$fillable} = $this->request->get($fillable);
            }
        }

        $guarantor->save();

        return $guarantor;
    }
}
