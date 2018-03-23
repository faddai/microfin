<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 21/02/2017
 * Time: 13:52
 */

namespace App\Traits;


use Illuminate\Foundation\Bus\DispatchesJobs;

trait LoanInterestCalculation
{

    use DispatchesJobs;

    /**
     * @return mixed
     */
    protected function getRepaymentStartDate()
    {
        $start = $this->loan->disbursed_at ? $this->loan->disbursed_at : $this->loan->created_at;
        $start = $start->copy();

        if ($this->loan->grace_period > 0) {
            return $start->addWeekdays($this->loan->grace_period);
        }

        return $start;
    }

    /**
     * @param $repaymentAmount
     * @param $interest
     * @return mixed
     */
    public function getPrincipalOnRepayment($repaymentAmount, $interest)
    {
        return $repaymentAmount - ($this->loan->getFeesComponentOnRepayment() + $interest);
    }
}