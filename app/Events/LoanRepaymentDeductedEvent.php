<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/04/2017
 * Time: 16:38
 */

namespace App\Events;


use App\Entities\LoanRepayment;

class LoanRepaymentDeductedEvent
{
    /**
     * @var LoanRepayment
     */
    public $previousRepaymentDeduction;

    /**
     * @var LoanRepayment
     */
    public $currentRepaymentDeduction;

    /**
     * LoanRepaymentDeductedEvent constructor.
     * @param LoanRepayment $previousRepaymentDeduction
     * @param LoanRepayment $currentRepaymentDeduction
     */
    public function __construct(LoanRepayment $previousRepaymentDeduction, LoanRepayment $currentRepaymentDeduction)
    {
        $this->previousRepaymentDeduction = $previousRepaymentDeduction;
        $this->currentRepaymentDeduction = $currentRepaymentDeduction;
    }
}