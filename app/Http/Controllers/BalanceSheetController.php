<?php

namespace App\Http\Controllers;

use App\Jobs\Reports\GenerateBalanceSheetReportJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Csv\Writer;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $balanceSheet = $this->dispatch(new GenerateBalanceSheetReportJob($request));

        return view('dashboard.accounting.balance_sheet.index', compact('balanceSheet'));
    }

    public function download(Request $request)
    {
        $today = Carbon::today();
        $filename = sprintf('balance-sheet-%s.csv', $today->format('d-m-Y'));

        $balanceSheet = $this->dispatch(new GenerateBalanceSheetReportJob($request));

        $csv = Writer::createFromFileObject(new \SplTempFileObject());

        // meta information
        $csv->insertOne(config('app.company'));
        $csv->insertOne(sprintf('"%s"', config('app.address')));
        $csv->insertOne(',');
        $csv->insertOne(sprintf('Date,"%s"', $today->format(config('microfin.dateFormat'))));
        $csv->insertOne(',');

        $csv->insertOne(',Balance,Budgeted');

        $balanceSheet->each(function (Collection $group, $ledgerCategoryType) use ($csv) {

            $group->each(function (Collection $collection, $category) use ($csv) {
                $csv->insertOne(sprintf('%s,,', $category));

                $collection->get('ledgers')->each(function (Collection $ledger, $ledgerName) use ($csv) {
                    $csv->insertOne(vsprintf('%s,"%s","%s"', [
                        $ledgerName,
                        number_format($ledger->get('balance'), 2),
                        number_format($ledger->get('budgeted'), 2),
                    ]));
                });

                $csv->insertOne(vsprintf('Subtotal,"%s","%s"', [
                    number_format($collection->get('subtotal'), 2),
                    number_format($collection->get('subtotal_budgeted'), 2)
                ]));
            });

            $csv->insertOne(vsprintf('Total %s,"%s","%s"', [
                ucfirst($ledgerCategoryType),
                number_format($group->total, 2),
                number_format($group->budgeted_total, 2),
            ]));

        });

        // leave a blank line
        $csv->insertOne(',');

        $csv->insertOne(vsprintf('Total Liabilities & Capital,"%s",%s',[
            number_format($balanceSheet->totalLiabilitiesAndCapital, 2),
            0.00
        ]));

        $csv->output($filename);

        die();
    }
}
