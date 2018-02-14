<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 12:54 AM
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Entities\Tenure::class, function (Faker\Generator $faker) {

    $number = $faker->numberBetween(1, 60);

    return [
        'label' => sprintf('%d %s', $number, str_plural('month', $number)),
        'number_of_months' => $number,
    ];

});
