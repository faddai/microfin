<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 26/02/2017
 * Time: 16:42
 */
use App\Entities\LoanProduct;
use Faker\Generator;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(LoanProduct::class, function (Generator $faker) {
    return [
        'description' => $faker->text(50),
        'min_loan_amount' => $faker->numberBetween(200, 600),
        'max_loan_amount' => $faker->numberBetween(3000, 200000),
    ];
});

// Loan product for general Customers
$factory->defineAs(LoanProduct::class, 'customer', function () use ($factory) {
    $product = $factory->raw(LoanProduct::class);
    $customerProduct = LoanProduct::whereCode(LoanProduct::CUSTOMER)->first()->toArray();

    return array_merge($customerProduct, $product);
});

// Loan product for Staff
$factory->defineAs(LoanProduct::class, 'staff', function () use ($factory) {
    $product = $factory->raw(LoanProduct::class);
    $staffProduct = LoanProduct::whereCode(LoanProduct::STAFF)->first()->toArray();

    return array_merge($staffProduct, $product);
});

// Loan product for GRZ
$factory->defineAs(LoanProduct::class, 'grz', function () use ($factory) {
    $product = $factory->raw(LoanProduct::class);
    $grzProduct = LoanProduct::whereCode(LoanProduct::GRZ)->first()->toArray();

    return array_merge($grzProduct, $product);
});