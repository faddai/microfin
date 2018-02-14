<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 17/04/2017
 * Time: 19:57
 */

namespace App\Http\Controllers;

use App\Jobs\Reports\GenerateIncomeStatementReportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Csv\Writer;

class IncomeStatementController extends Controller
{
    public function index(Request $request)
    {
        $incomeStatement = $this->dispatch(new GenerateIncomeStatementReportJob($request));

        return view('dashboard.accounting.income_statement.index', compact('incomeStatement'));
    }

    public function download(Request $request)
    {
        $incomeStatement = $this->dispatch(new GenerateIncomeStatementReportJob($request));

        $filename = vsprintf('income-statement-%s_%s.csv', [$incomeStatement->startDate, $incomeStatement->endDate]);

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // meta information
        $csv->insertOne(config('app.company'));
        $csv->insertOne(sprintf('"%s"', config('app.address')));
        $csv->insertOne(',');

        $csv->insertOne(sprintf('From Date,"%s"', $incomeStatement->startDate));
        $csv->insertOne(sprintf('To Date,"%s"', $incomeStatement->endDate));
        $csv->insertOne(',');

        $csv->insertOne(',Balance,Budgeted');

        $incomeStatement->reverse()->each(function ($group, $ledgerCategoryType) use ($csv) {

            $csv->insertOne(sprintf('%s,,', $ledgerCategoryType));

            $group->get('ledgers')->each(function (Collection $ledger, $ledgerName) use ($csv) {
                $csv->insertOne(vsprintf('%s,"%s","%s"', [
                    $ledgerName,
                    number_format($ledger->get('balance'), 2),
                    number_format($ledger->get('budgeted'), 2),
                ]));
            });

            $csv->insertOne(vsprintf('Total %s,"%s","%s"', [
                $ledgerCategoryType,
                number_format($group->get('total'), 2),
                number_format($group->get('total_budgeted'), 2)
            ]));

        });

        $csv->insertOne(',');

        $csv->insertOne(vsprintf('Net Profit/Loss,"%s","%s"', [
            number_format($incomeStatement->net_profit->get('balance'), 2),
            number_format($incomeStatement->net_profit->get('budgeted'), 2)
        ]));

        $csv->output($filename);

        die();
    }
}
