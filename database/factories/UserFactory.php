<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/10/2016
 * Time: 23:14
 */

use App\Entities\Branch;
use App\Entities\User;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'branch_id' => factory(Branch::class)->create()->id,
        'is_active' => 1
    ];
});