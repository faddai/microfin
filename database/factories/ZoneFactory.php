<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 20/11/2016
 * Time: 1:08 AM
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(\App\Entities\Zone::class, function (\Faker\Generator $faker) {

    return ['name' => $faker->name. ' Zone'];

});