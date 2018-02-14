<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/01/2017
 * Time: 12:36 AM
 */

namespace App\Events;

use App\Entities\Loan;


class LoanDeclinedEvent
{
    /**
     * @var Loan|mixed
     */
    public $loan;

    /**
     * LoanDeclinedEvent constructor.
     * @param mixed $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }
}