<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 07/11/2016
 * Time: 15:17
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\App\Entities\Country::class, function(\Faker\Generator $faker) {
    return [
        'name' => $faker->country,
        'alpha_2_code' => $faker->countryCode,
        'alpha_3_code' => $faker->countryISOAlpha3,
        'nationality' => $faker->country
    ];
});