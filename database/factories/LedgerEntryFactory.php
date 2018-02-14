<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/02/2017
 * Time: 09:58
 */

use App\Entities\Accounting\LedgerEntry;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(LedgerEntry::class, function (Generator $faker) {

    return [
        'ledger_id' => $faker->numberBetween(1, 10),
        'narration' => $faker->sentence,
    ];
});