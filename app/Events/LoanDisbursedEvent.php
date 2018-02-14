<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 17/01/2017
 * Time: 4:24 PM
 */

namespace App\Events;

use App\Entities\Loan;


class LoanDisbursedEvent
{
    /**
     * @var Loan
     */
    public $loan;

    /**
     * @param mixed $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }
}