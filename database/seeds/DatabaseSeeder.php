<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(Setup\BranchesTableSeeder::class);
        $this->call(Setup\CountriesTableSeeder::class);
        $this->call(Setup\LoanTypesTableSeeder::class);
        $this->call(Setup\RepaymentPlansTableSeeder::class);
        $this->call(Setup\TenuresTableSeeder::class);
        $this->call(Setup\BusinessSectorsTableSeeder::class);
        $this->call(Setup\ZonesTableSeeder::class);
        $this->call(Setup\RolesAndPermissionsTablesSeeder::class);
        $this->call(Setup\LedgersAndLedgerCategoriesTableSeeder::class);
        $this->call(Setup\LoanProductsTableSeeder::class);
        $this->call(Setup\FeesTableSeeder::class);
    }
}
