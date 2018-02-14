<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/03/2017
 * Time: 01:52
 */

use App\Entities\Accounting\Ledger;
use App\Entities\Fee;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Fee::class, function (Generator $faker) {
    return [
        'rate' => $faker->randomDigit,
        'name' => $faker->name,
    ];
});

// administration fee
$factory->defineAs(Fee::class, 'administration', function () use ($factory) {
    return array_merge(
        $factory->raw(Fee::class),
        Fee::whereName(Fee::ADMINISTRATION)->first()->toArray()
    );
});

// arrangement fee
$factory->defineAs(Fee::class, 'arrangement', function () use ($factory) {
    return array_merge(
        $factory->raw(Fee::class),
        Fee::whereName(Fee::ARRANGEMENT)->first()->toArray()
    );
});

// disbursement fee
$factory->defineAs(Fee::class, 'disbursement', function () use ($factory) {
    return array_merge(
        $factory->raw(Fee::class),
        Fee::whereName(Fee::DISBURSEMENT)->first()->toArray()
    );
});

// processing fee
$factory->defineAs(Fee::class, 'processing', function () use ($factory) {
    return array_merge(
        $factory->raw(Fee::class),
        Fee::whereName(Fee::PROCESSING)->first()->toArray()
    );
});

