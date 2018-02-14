<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 18:30
 */

use App\Entities\Branch;
use App\Entities\Client;
use App\Entities\CorporateClient;
use App\Entities\Country;
use App\Entities\GroupClient;
use App\Entities\IndividualClient;
use App\Entities\User;
use Carbon\Carbon;

/**
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Client::class, function (Faker\Generator $faker) {

    $relationshipManager = factory(User::class)->create();
    $country = factory(Country::class)->create();

    return [
        'email' => $faker->unique()->safeEmail,
        'nationality' => $country->alpha_2_code,
        'branch_id' => factory(Branch::class)->create()->id,
        'relationship_manager' => $relationshipManager->id,
        'account_balance' => $faker->numberBetween(2000, 500000),
    ];

});

$factory->define(IndividualClient::class, function (Faker\Generator $faker) {
    return [
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'gender' => 'male',
        'dob' => Carbon::parse('28 years ago'),
    ];
});

$factory->define(CorporateClient::class, function (Faker\Generator $faker) {

    $companyOwnershipType = trans('company_ownership_types');

    return [
        'date_of_incorporation' => Carbon::parse('4 years ago'),
        'business_registration_number' => $faker->randomNumber(9),
        'company_ownership_type' => $companyOwnershipType[array_rand($companyOwnershipType)]
    ];
});

$factory->define(GroupClient::class, function (Faker\Generator $faker) {
    return [
        'date_of_incorporation' => Carbon::parse('3 years ago')
    ];
});


// individual client
$factory->defineAs(Client::class, 'individual', function (Faker\Generator $faker) use ($factory) {
    $client = $factory->raw(Client::class);
    $individual = factory(IndividualClient::class)->create();

    return array_merge($client, [
        'clientable_id' => $individual->getKey(),
        'clientable_type' => $individual->getMorphClass()
    ]);
});

// corporate client
$factory->defineAs(Client::class, 'corporate', function() use ($factory) {
    $client = $factory->raw(Client::class);
    $corporate = factory(CorporateClient::class)->create();

    return array_merge($client, [
        'clientable_id' => $corporate->getKey(),
        'clientable_type' => $corporate->getMorphClass()
    ]);
});

// corporate client
$factory->defineAs(Client::class, 'group', function() use ($factory) {
    $group = factory(GroupClient::class)->create();
    $client = $factory->raw(Client::class);

    return array_merge($client, [
        'clientable_id' => $group->getKey(),
        'clientable_type' => $group->getMorphClass()
    ]);
});