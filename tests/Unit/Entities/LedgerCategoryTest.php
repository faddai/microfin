<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 29/02/2017
 * Time: 4:50 PM
 */

use App\Entities\Accounting\LedgerCategory;
use Tests\TestCase;

class LedgerCategoryTest extends TestCase
{
    public function test_get_category_type()
    {
        $asset = LedgerCategory::getCategoryType('Non-Current Assets');
        $asset1 = LedgerCategory::getCategoryType('Customer Control-Assets');
        $asset2 = LedgerCategory::getCategoryType('Other Current Assets');
        $asset3 = LedgerCategory::getCategoryType('Other-Current-Assets-2016');
        $expense = LedgerCategory::getCategoryType('Expenses');
        $income = LedgerCategory::getCategoryType('Income');
        $liab = LedgerCategory::getCategoryType('Short Term Liabilities');
        $liab1 = LedgerCategory::getCategoryType('Non Current Liabilities');

        self::assertEquals('expense', $expense);

        collect([$asset, $asset1, $asset2, $asset3])->each(function ($asset) {
            self::assertEquals('asset', $asset);
        });

        self::assertEquals('income', $income);

        collect([$liab, $liab1])->each(function ($liab) {
            self::assertEquals('liab', $liab);
        });
    }
}
