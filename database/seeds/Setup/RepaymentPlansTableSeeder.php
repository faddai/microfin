<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 02/11/2016
 * Time: 00:50
 */

namespace Setup;


use App\Entities\RepaymentPlan;
use Illuminate\Database\Seeder;

class RepaymentPlansTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            ['number_of_days' => 5, 'label' => RepaymentPlan::WEEKLY, 'number_of_repayments_per_month' => 4],
            ['number_of_days' => 10, 'label' => RepaymentPlan::FORTNIGHTLY, 'number_of_repayments_per_month' => 2],
            ['number_of_days' => 22, 'label' => RepaymentPlan::MONTHLY, 'number_of_repayments_per_month' => 1],
        ];

        foreach ($plans as $plan) {
            RepaymentPlan::firstOrCreate($plan);
        }
    }
}