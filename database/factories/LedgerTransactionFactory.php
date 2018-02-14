<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 22/02/2017
 * Time: 06:21
 */

use App\Entities\Accounting\LedgerTransaction;
use App\Entities\Branch;
use App\Entities\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(LedgerTransaction::class, function (Generator $faker) {

    return [
        'uuid' => $faker->uuid,
        'branch_id' => factory(Branch::class)->create()->id,
        'user_id' => factory(User::class)->create()->id,
    ];
});