<?php

namespace App\Events;

use App\Entities\Loan;


class LoanApprovedEvent
{
    /**
     * @var Loan
     */
    public $loan;

    /**
     * Create a new event instance.
     *
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }
}
