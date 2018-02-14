<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 26/02/2017
 * Time: 17:05
 */

namespace Setup;

use App\Entities\Accounting\Ledger;
use App\Entities\LoanProduct;
use Illuminate\Database\Seeder;

class LoanProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                'name' => 'Customer',
                'code' => LoanProduct::CUSTOMER,
                'principal_ledger_id' => Ledger::whereCode(5001)->first()->id,
                'interest_ledger_id' => Ledger::whereCode(5004)->first()->id,
                'interest_income_ledger_id' => Ledger::whereCode(7002)->first()->id
            ],
            [
                'name' => 'Government (GRZ)',
                'code' => LoanProduct::GRZ,
                'principal_ledger_id' => Ledger::whereCode(5006)->first()->id,
                'interest_ledger_id' => Ledger::whereCode(5007)->first()->id,
                'interest_income_ledger_id' => Ledger::whereCode(7010)->first()->id
            ],
            [
                'name' => 'Staff',
                'code' => LoanProduct::STAFF,
                'principal_ledger_id' => Ledger::whereCode(5002)->first()->id,
                'interest_ledger_id' => Ledger::whereCode(5003)->first()->id,
                'interest_income_ledger_id' => Ledger::whereCode(7003)->first()->id
            ],
        ];

        collect($products)->map(function ($product) {
            $product['name'] .= ' Loan';
            return $product;
        })->each(function ($product) {
            LoanProduct::firstOrCreate($product);
        });
    }
}
