<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/12/2016
 * Time: 23:36
 */

use App\Entities\Accounting\LedgerCategory;
use App\Entities\Client;
use App\Entities\ClientTransaction;
use Carbon\Carbon;
use Faker\Generator;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(ClientTransaction::class, function (Generator $faker) {

    $client = factory(Client::class, 'individual')->create();

    $bankLedgers = LedgerCategory::getBankOrCashLedgers();

    return [
        'client_id' => $client->id,
        'ledger_id' => $bankLedgers->get(array_rand($bankLedgers->toArray()))->id,
        'narration' => $faker->text(15),
        'value_date' => Carbon::now(),
    ];
});

$amount = faker()->numberBetween(3000, 10000);

$factory->state(ClientTransaction::class, 'deposit', function () use ($amount) {
    return [
        'cr' => $amount
    ];
});

$factory->state(ClientTransaction::class, 'withdrawal', function () use ($amount) {
    return [
        'dr' => $amount
    ];
});