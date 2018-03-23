<?php

namespace App\Jobs;

use App\Entities\Collateral;
use App\Entities\Loan;
use Illuminate\Http\Request;


class AddCollateralJob
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
     * @return Loan
     */
    public function handle()
    {
        $collateral = new Collateral(['loan_id' => $this->loan->id]);

        if ($this->request->filled('collateral_id')) {
            $collateral = Collateral::find($this->request->get('collateral_id'));
        }

        foreach ($collateral->getFillable() as $fillable) {
            if ($this->request->filled($fillable)) {
                $collateral->{$fillable} = $this->request->get($fillable);
            }
        }

        $collateral->save();

        return $collateral;

    }
}
