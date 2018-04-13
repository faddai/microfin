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
    // Unable to use database_path because the app wouldn't
    // have been bootstrapped by the time this gets called
    private $testDatabaseFile = __DIR__.'/../../database/testing.sqlite';

    /**
     * Creates an empty database for testing
     */
    protected function createDatabase()
    {
        touch($this->testDatabaseFile);
    }

    protected function seedDatabase()
    {
        $this->seed(DatabaseSeeder::class);

        $this->beforeApplicationDestroyed([$this, 'removeDatabase']);
    }

    protected function removeDatabase()
    {
        unlink($this->testDatabaseFile);
    }

}
