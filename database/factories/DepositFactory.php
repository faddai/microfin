<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 14/12/2016
 * Time: 23:36
 */
use App\Entities\Accounting\LedgerCategory;
use App\Entities\Client;
use App\Entities\Deposit;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Deposit::class, function (\Faker\Generator $faker) {

    $client = factory(Client::class, 'individual')->create();
    $bankLedgers = LedgerCategory::getBankOrCashLedgers();

    return [
        'amount' => $faker->numberBetween(3000, 10000),
        'client_id' => $client->id,
        'ledger_id' => $bankLedgers->get(array_rand($bankLedgers->toArray()))->id,
        'narration' => $faker->text(15),
    ];
});