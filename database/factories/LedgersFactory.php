<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/01/2017
 * Time: 5:13 AM
 */
use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerCategory;
use Setup\LedgersAndLedgerCategoriesTableSeeder;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Ledger::class, function (Faker\Generator $faker) {

    $categories = array_keys(LedgersAndLedgerCategoriesTableSeeder::getChartOfAccounts());

    return [
        'name' => $faker->name,
        'code' => $faker->randomNumber(5),
        'category_id' => LedgerCategory::firstOrCreate(['name' => $categories[array_rand($categories)]])->id,
    ];

});