<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 08/11/2016
 * Time: 08:20
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Entities\Branch::class, function (Faker\Generator $faker) {

    $branchCodes = ['001', '002', '003'];

    return [
        'name' => $faker->name,
        'code' => $branchCodes[array_rand($branchCodes)],
        'location' => $faker->city,
        'status' => random_int(0, 1)
    ];

});
