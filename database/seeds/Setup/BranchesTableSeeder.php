<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 10:22
 */

namespace Setup;

use App\Entities\Branch;
use Illuminate\Database\Seeder;

class BranchesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branches = [
            [
                'name' => 'Accra Central',
                'code' => '001',
                'location' => 'Accra Central'
            ],
            [
                'name' => 'Ring Road',
                'code' => '002',
                'location' => 'No. 1 Street, Ring Road'
            ],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate($branch);
        }

        cache()->forget('branches');
    }
}