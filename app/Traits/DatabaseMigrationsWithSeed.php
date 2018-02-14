<?php

namespace App\Traits;


trait DatabaseMigrationsWithSeed
{
    public function runMigrations() {
        if (env('DB_DATABASE') === ':memory:') {
            $this->artisan('migrate', ['--seed' => true]);
        }
    }
}