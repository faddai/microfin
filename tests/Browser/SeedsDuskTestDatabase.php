<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 03/04/2018
 * Time: 11:51 PM
 */

namespace Tests\Browser;

use DatabaseSeeder;

trait SeedsDuskTestDatabase
{
    /**
     * Creates an empty database for testing
     */
    protected function createDatabase()
    {
        // Unable to use database_path because the app wouldn't
        // have been bootstrapped by the time this gets called
        touch(__DIR__.'/../../database/testing.sqlite');
    }

    protected function seedDatabase()
    {
        $this->seed(DatabaseSeeder::class);

        $this->beforeApplicationDestroyed([$this, 'removeDatabase']);
    }

    protected function removeDatabase()
    {
        $db = $this->app->make('db')->connection();

        unlink($db->getDatabaseName());
    }

}
