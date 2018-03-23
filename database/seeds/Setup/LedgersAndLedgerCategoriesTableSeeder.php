<?php

namespace Setup;

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class LedgersAndLedgerCategoriesTableSeeder extends Seeder
{
    /**
     * Create 10 dummy shareholders
     *
     * @return array
     */
    private static function generateFakeLedgers($minCode, $maxCode) {

        return collect(range($minCode, $maxCode))->map(function ($code) {
            return [
                'code' => $code,
                'name' => faker()->name
            ];
        })->toArray();
    }

    /**
     * @return  array
     */
    public static function getChartOfAccounts() {
        return [
            'Share Capital' => static::generateFakeLedgers(1001, 1010),

            'Non Current Liabilities' => static::generateFakeLedgers(2001, 2006),

            'Short Term Liabilities' => static::generateFakeLedgers(3001, 3006),

            'Non-Current Assets' => [
                ['code' => 4001, 'name' => 'Motor Vehicles - Net Value'],
                ['code' => 4002, 'name' => 'Motor Vehicles - @ Cost'],
                ['code' => 4003, 'name' => 'Motor Vehicles - Accum Depre'],
                ['code' => 4004, 'name' => 'Computer Equipment - Net Value'],
                ['code' => 4005, 'name' => 'Computer Equipment - @ Cost'],
                ['code' => 4006, 'name' => 'Computer Equipment - Accum Depre'],
                ['code' => 4007, 'name' => 'Intangible Asset - ACME Software'],
                ['code' => 4008, 'name' => 'Furniture & Fittings - Net value'],
                ['code' => 4009, 'name' => 'Furniture & Fittings - @ Cost'],
                ['code' => 4010, 'name' => 'Furniture & Fittings - Accum Depre'],
                ['code' => 4011, 'name' => 'Equipment-Net Value'],
                ['code' => 4012, 'name' => 'Equipment-Cost'],
                ['code' => 4013, 'name' => 'Equipment-Accumulated Depreciation'],
            ],

            'Customer Control-Assets' => [
                ['code' => 5001, 'name' => 'Principal - Customers'],
                ['code' => 5002, 'name' => 'Principal - Staff'],
                ['code' => 5003, 'name' => 'Interest - Staff'],
                ['code' => 5004, 'name' => 'Interest - Customer'],
                ['code' => 5005, 'name' => 'Interest Receivables-Refinanced'],
                ['code' => 5006, 'name' => 'Principal - GRZ'],
                ['code' => 5007, 'name' => 'Interest Income-GRZ'],
                ['code' => 5008, 'name' => 'GRZ - Customer Loans'],
                ['code' => 5009, 'name' => 'Prepayments / Deferred Expenses'],
            ],

            'Other Current Assets' => [
                ['code' => 6001, 'name' => 'Other Receivables'],
                ['code' => 6002, 'name' => 'Other receivable-Loan Processing fees'],
                ['code' => 6003, 'name' => 'Prepayments-Other'],
                ['code' => 6004, 'name' => 'Staff Advances'],
                ['code' => 6005, 'name' => 'Inde-Credit'],
                ['code' => 6006, 'name' => 'Loan to JV'],
                ['code' => 6007, 'name' => 'Other Receivables - Debt Recovery Costs'],
                ['code' => 6008, 'name' => 'Other Receivable - Relocation Costs'],
                ['code' => 6009, 'name' => 'Other Receivable - Debt recovery costs GRZ'],
                ['code' => 6010, 'name' => 'ACME Bank Plc - 2301100000', 'is_bank_or_cash' => 1],
                ['code' => 6011, 'name' => 'Petty Cash'],
                ['code' => 6012, 'name' => 'Cavmount Bank - 90897787', 'is_bank_or_cash' => 1],
                ['code' => 6013, 'name' => 'ACME-Operations USD', 'is_bank_or_cash' => 1],
                ['code' => 6014, 'name' => 'Millenium Challenge-Account Receivable'],
                ['code' => 6015, 'name' => 'Investment in Subsidiary'],
                ['code' => 6016, 'name' => 'Investment in JV-Inde Credit'],
                ['code' => 6017, 'name' => 'Joint Venture - ACME'],
                ['code' => 6018, 'name' => 'Deferred Tax Asset'],
                ['code' => 6019, 'name' => 'Provisions & Accruals - Expenses'],
                ['code' => 6020, 'name' => 'Provision for doubtful debts'],
                ['code' => 6021, 'name' => 'Provisions&Accruals-Leave & Gratuity'],
                ['code' => 6022, 'name' => 'Credit Life Insurance-Provision'],
                ['code' => 6023, 'name' => 'Opening Balance / Suspense Account'],
                ['code' => 6024, 'name' => 'Net Profit'],
            ],

            'Income' => [
                ['code' => 7001, 'name' => 'Interest Received'],
                ['code' => 7002, 'name' => 'Interest received - Customer'],
                ['code' => 7003, 'name' => 'Interest received - Staff'],
                ['code' => 7004, 'name' => 'Bank'],
                ['code' => 7005, 'name' => 'Administration fees'],
                ['code' => 7006, 'name' => 'Interest Income - Refinanced'],
                ['code' => 7007, 'name' => 'Arrangement Fees'],
                ['code' => 7008, 'name' => 'Processing Fees'],
                ['code' => 7009, 'name' => 'Disbursement Fees'],
                ['code' => 7010, 'name' => 'Interest Income-GRZ'],
                ['code' => 7011, 'name' => 'Other Income-CRB'],
                ['code' => 7012, 'name' => 'Other Income-Interest from placement'],
                ['code' => 7013, 'name' => 'Credit Life Insurance Commission'],
                ['code' => 7014, 'name' => 'Other Income-Miscellaneous'],
                ['code' => 7015, 'name' => 'Management Fees Income'],
                ['code' => 7016, 'name' => 'Administration Fees-GRZ'],
                ['code' => 7017, 'name' => 'Bad Debts Recovered'],
                ['code' => 7018, 'name' => 'Approval Fees Income'],
                ['code' => 7019, 'name' => 'Pft/Loss on Sale of fixed Assets'],
            ],

            'Expenses' => [
                ['code' => 8001, 'name' => 'Advertising & Promotions'],
                ['code' => 8002, 'name' => 'Accounting / Audit Fees'],
                ['code' => 8003, 'name' => 'Bad Debts'],
                ['code' => 8004, 'name' => 'Debt Recovery Costs'],
                ['code' => 8005, 'name' => 'Bank Charges'],
                ['code' => 8006, 'name' => 'Loan Processing Fees'],
                ['code' => 8007, 'name' => 'Cleaning'],
                ['code' => 8008, 'name' => 'Computer Expenses'],
                ['code' => 8009, 'name' => 'Consulting Fees'],
                ['code' => 8010, 'name' => 'Courier & Postage'],
                ['code' => 8011, 'name' => 'Depreciation'],
                ['code' => 8012, 'name' => 'Directors Fees / Members Remuneration'],
                ['code' => 8013, 'name' => 'Board Expenses'],
                ['code' => 8014, 'name' => 'Donations'],
                ['code' => 8015, 'name' => 'Electricity & Water'],
                ['code' => 8016, 'name' => 'Entertainment Expenses'],
                ['code' => 8017, 'name' => 'Sundry Expenses'],
                ['code' => 8018, 'name' => 'Insurance & Licences'],
                ['code' => 8019, 'name' => 'Credit Reference Bureau-Expense'],
                ['code' => 8020, 'name' => 'Generator Expenses'],
                ['code' => 8021, 'name' => 'Workers Compensation'],
                ['code' => 8022, 'name' => 'Interest Paid'],
                ['code' => 8023, 'name' => 'Bank'],
                ['code' => 8024, 'name' => 'Legal Fees'],
                ['code' => 8025, 'name' => 'Motor Vehicle Expenses'],
                ['code' => 8026, 'name' => 'Motor Vehicle - Petrol & Oil'],
                ['code' => 8027, 'name' => 'Motor Vehicle - Repairs & Maintenance'],
                ['code' => 8028, 'name' => 'Motor Vehicle - Insurance & Licence'],
                ['code' => 8029, 'name' => 'Printing & Stationery'],
                ['code' => 8030, 'name' => 'Rent Paid'],
                ['code' => 8031, 'name' => 'Relocation Costs'],
                ['code' => 8032, 'name' => 'Repairs & Maintenance'],
                ['code' => 8033, 'name' => 'Salaries & Wages'],
                ['code' => 8034, 'name' => 'PAYE'],
                ['code' => 8035, 'name' => 'NAPSA'],
                ['code' => 8036, 'name' => 'Staff Training'],
                ['code' => 8037, 'name' => 'Commissions'],
                ['code' => 8038, 'name' => 'Staff Welfare'],
                ['code' => 8039, 'name' => 'Subscriptions'],
                ['code' => 8040, 'name' => 'Management Fees'],
                ['code' => 8041, 'name' => 'Telephone  Fax & Internet'],
                ['code' => 8042, 'name' => 'Travel & Accommodation'],
                ['code' => 8043, 'name' => 'Travel - Local'],
                ['code' => 8044, 'name' => 'Travel - Overseas'],
                ['code' => 8045, 'name' => 'Millenium Challenge Account-Expenses'],
                ['code' => 8046, 'name' => 'Dividends Declared / Paid'],
                ['code' => 8047, 'name' => 'Retainer Expenses-Kama'],
                ['code' => 8048, 'name' => 'Transport'],
                ['code' => 8049, 'name' => 'With holding tax'],
                ['code' => 8050, 'name' => 'Title deeds verification fees'],
                ['code' => 8051, 'name' => 'Property Transfer Tax'],
                ['code' => 8052, 'name' => 'Taxation Paid'],
                ['code' => 8053, 'name' => 'Security Charges'],
            ]
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tables are getting deleted because we're working with fake
        // data and instead of creating more records, we're doing away with old data
        // Ledger codes stay the same though
        foreach (['ledgers', 'ledger_categories'] as $table) {
            DB::table($table)->delete();
        }

        // create account categories and associate their respective accounts
        collect($this->getChartOfAccounts())->each(function ($accounts, $category) {
            $accountCategory = LedgerCategory::firstOrCreate([
                'name' => $category,
                'type' => LedgerCategory::getCategoryType($category)
            ]);

            collect($accounts)->each(function ($account) use ($accountCategory) {
                return Ledger::firstOrCreate(array_merge($account, [
                    'category_id' => $accountCategory->id,
                    'is_left' => $accountCategory->hasDebitBalance(),
                    'is_right' => $accountCategory->hasCreditBalance()
                ]));
            });
        });
    }
}
