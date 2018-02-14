<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 30/10/2016
 * Time: 21:56
 */

namespace Setup;

use App\Entities\Accounting\Ledger;
use App\Entities\Fee;
use Illuminate\Database\Seeder;

class FeesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            [
                'name' => Fee::ADMINISTRATION,
                'rate' => 5.0,
                'income_ledger_id' => Ledger::whereCode(7005)->first()->id,
                'is_paid_upfront' => 1
            ],
            [
                'name' => Fee::ARRANGEMENT,
                'rate' => 7.0,
                'income_ledger_id' => Ledger::whereCode(7007)->first()->id
            ],
            [
                'name' => Fee::DISBURSEMENT,
                'rate' => 5.0,
                'income_ledger_id' => Ledger::whereCode(7009)->first()->id
            ],
            [
                'name' => Fee::PROCESSING,
                'rate' => 5.0,
                'income_ledger_id' => Ledger::whereCode(7008)->first()->id
            ],
            [
                'name' => 'Insurance fee',
                'rate' => 0.03, // (0.03 * loan amount) * 0.01
                'income_ledger_id' => Ledger::whereCode(7013)->first()->id
            ],
        ])->each(function (array $fee) {
            $fee['receivable_ledger_id'] = Ledger::whereCode(6002)->first()->id;
            Fee::firstOrCreate($fee);
        });
    }
}