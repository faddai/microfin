<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 12:52 AM
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Entities\LoanType::class, function (Faker\Generator $faker) {

    return [
        'label' => $faker->name
    ];

});
