<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 02/11/2016
 * Time: 00:58
 */

namespace Setup;


use App\Entities\Tenure;
use Illuminate\Database\Seeder;

class TenuresTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect(range(1, 60))
            ->each(function (int $tenure) {
                Tenure::firstOrCreate([
                    'number_of_months' => $tenure,
                    'label' => $tenure. ' ' .str_plural('month', $tenure)
                ]);
            });
    }
}