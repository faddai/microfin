<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 11/06/2017
 * Time: 09:13
 */
use App\Entities\LoanPayoff;

/** @var Illuminate\Database\Eloquent\Factory $factory */
$factory->define(LoanPayoff::class, function (\Faker\Generator $faker) {
    return [
        'principal' => $faker->randomNumber(4),
        'interest' => $faker->randomNumber(4),
        'fees' => 0,
    ];
});