<?php

namespace Setup;


use App\Entities\Zone;
use Illuminate\Database\Seeder;

class ZonesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect(['Accra Main'])
            ->each(function ($name) {
                Zone::firstOrCreate(['name' => $name]);
            });
    }
}
