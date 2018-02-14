<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 1:15 AM
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Entities\Collateral::class, function (\Faker\Generator $faker) {

    return [
        'label' => $faker->sentence(4),
        'market_value' => random_int(20000, 500000),
        'loan_id' => ''
    ];

});