<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 1:01 AM
 */

use App\Entities\RepaymentPlan;

/** @var \Illuminate\Database\Eloquent\Factory $factory **/
$factory->define(RepaymentPlan::class, function () {

    $plans = [
        ['number_of_days' => 5, 'label' => RepaymentPlan::WEEKLY, 'number_of_repayments_per_month' => 4],
        ['number_of_days' => 10, 'label' => RepaymentPlan::FORTNIGHTLY, 'number_of_repayments_per_month' => 2],
        ['number_of_days' => 20, 'label' => RepaymentPlan::MONTHLY, 'number_of_repayments_per_month' => 1],
    ];

    return $plans[array_rand($plans)];
});