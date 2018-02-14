<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 1:15 AM
 */

use App\Entities\Client;
use App\Entities\Loan;
use App\Entities\LoanProduct;
use App\Entities\LoanType;
use App\Entities\RepaymentPlan;
use App\Entities\Role;
use App\Entities\Tenure;
use App\Entities\User;
use App\Entities\Zone;
use Carbon\Carbon;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(Loan::class, function (\Faker\Generator $faker) {

    $client = factory(Client::class, 'individual')->create();
    $tenure = Tenure::firstOrCreate(['number_of_months' => 12]);
    $loanType = LoanType::firstOrCreate(['label' => 'Personal']);
    $repaymentPlan = RepaymentPlan::whereLabel(RepaymentPlan::MONTHLY)->first();
    $zone = Zone::firstOrCreate(['name' => 'Zone 1']);
    $officer = factory(User::class)->create();
    $role = Role::firstOrCreate(['name' => str_slug(Role::CREDIT_OFFICER)]);
    $officer->roles()->sync([$role->id]);

    $loanAmount = $faker->randomNumber(6);

    return [
        'amount' => $loanAmount,
        'client_id' => $client->id,
        'tenure_id' => $tenure->id,
        'loan_type_id' => $loanType->id,
        'repayment_plan_id' => $repaymentPlan->id,
        'purpose' => $faker->text,
        'rate' => $faker->numberBetween(1, 9),
        'zone_id' => $zone->id,
        'credit_officer' => $officer->id,
        'grace_period' => 0,
        'monthly_income' => $faker->randomNumber(5),
        'loan_size' => get_loan_size($loanAmount),
        'age_group' => get_age_group($client->clientable->dob), // applicable to individual clients
        'start_date' => Carbon::today(),
        'interest_calculation_strategy' => Loan::STRAIGHT_LINE_STRATEGY,
        'status' => Loan::PENDING,
        'user_id' => factory(User::class)->create()->id,
    ];

});

// customer loans
$factory->defineAs(Loan::class, 'customer', function () use ($factory) {

    return array_merge($factory->raw(Loan::class),
        ['loan_product_id' => LoanProduct::whereCode(LoanProduct::CUSTOMER)->first()->id]
    );
});

// staff loans
$factory->defineAs(Loan::class, 'staff', function () use ($factory) {

    return array_merge(
        $factory->raw(Loan::class),
        ['loan_product_id' => LoanProduct::whereCode(LoanProduct::STAFF)->first()->id]
    );
});

// GRZ loans
$factory->defineAs(Loan::class, 'grz', function () use ($factory) {

    return array_merge(
        $factory->raw(Loan::class),
        ['loan_product_id' => LoanProduct::whereCode(LoanProduct::GRZ)->first()->id]
    );
});

$factory->state(Loan::class, 'approved', function () {
    return [
        'approved_at' => Carbon::yesterday()->addWeekday(),
        'approver_id' => factory(User::class)->create()->id,
        'status' => Loan::APPROVED,
    ];
});

$factory->state(Loan::class, 'disbursed', function () {
    return [
        'disbursed_at' => Carbon::yesterday()->addWeekday(),
        'disburser_id' => factory(User::class)->create()->id,
        'status' => Loan::DISBURSED,
    ];
});