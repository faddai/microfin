<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 05/02/2017
 * Time: 1:46 PM
 */

namespace App\Entities\InterestCalculations;


use Illuminate\Support\Collection;

interface LoanInterestCalculationStrategyInterface
{
    public function schedule(): Collection;
    public function getRepaymentAmount();
    public function getPrincipalOnRepayment($repaymentAmount, $interest);
}