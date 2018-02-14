<?php

namespace App\Providers;

use App\Entities\CorporateClient;
use App\Entities\GroupClient;
use App\Entities\IndividualClient;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $connection = config('database.default');

        // Enable foreign key checks for sqlite database
        // Also makes sure data being saved, matches the data type of the column
        if (config("database.connections.{$connection}.driver") === "sqlite") {
            $db = app()->make('db');
            $db->connection()->getPdo()->exec('pragma foreign_keys=1');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Relation::morphMap([
            'MorphIndividual' => IndividualClient::class,
            'MorphCorporate' => CorporateClient::class,
            'MorphGroup' => GroupClient::class
        ]);
    }
}
