<?php

namespace Setup;

use Illuminate\Database\Seeder;
use App\Entities\BusinessSector;

class BusinessSectorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sectors = [
            'Agriculture,forestry and fishing',
            'Mining and quarrying',
            'Manufacturing',
            'Electricity Gas and Water',
            'Construction',
            'Wholesale and Retail trade',
            'Finance & insurance',
            'Hotels,bars and restaurant',
            'Transport Storage and communication',
            'Real Estate and business services',
            'Community Services',
            'Public Service',
            'Education',
        ];

        sort($sectors);

        foreach ($sectors as $sector) {
            BusinessSector::firstOrCreate(['name' => $sector]);
        }
    }
}
