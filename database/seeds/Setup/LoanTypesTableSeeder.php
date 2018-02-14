<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 02/11/2016
 * Time: 00:38
 */

namespace Setup;

use Illuminate\Database\Seeder;
use App\Entities\LoanType;

class LoanTypesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'Personal Loans',
            'Working Capital',
            'Order Finance',
            'Invoice Discounting',
            'Advance Factoring',
            'Equity Release',
            'Personal Loans (GRZ)',
            'Bridge Finance',
        ];

        sort($types); // sort in alphabetical order

        foreach ($types as $type) {
            LoanType::firstOrCreate(['label' => $type]);
        }
    }
}