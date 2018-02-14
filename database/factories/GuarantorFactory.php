<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 1:15 AM
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\App\Entities\Guarantor::class, function (\Faker\Generator $faker) {

    return [
        'name' =>$faker->name,
        'work_phone' => $faker->phoneNumber,
        'personal_phone' => $faker->phoneNumber,
        'employer' => $faker->company,
        'job_title' => $faker->jobTitle,
        'years_known' => random_int(1, 40),
        'email' => $faker->safeEmail,
        'residential_address' => $faker->address,
    ];

});