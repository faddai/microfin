<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 18/04/2017
 * Time: 19:59
 */

namespace App\Jobs\Reports;

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerCategory;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateBalanceSheetReportJob
{

    use DispatchesJobs;

    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle()
    {
        $date = Carbon::createFromFormat('d/m/Y', $this->request->get('date', Carbon::today()->format('d/m/Y')));

        /**
        get all ledgers and group them under their respective categories
        get the ledger closing balance at the date range

        Sample output:

        collect([
         * 'capital' => [
         *      'category_subtotal' => 9000,
         *      'categories' => [
         *          'Share Capital' => [
                        'subtotal' => 2000,
                        'budgeted_subtotal' => 0,
                        'ledgers' => [
                            'Bank interest' => ['balance' => 1290, 'budgeted' => 0],
                            'Yello card' => ['balance' => 10, 'budgeted' => 0],
                            'Mabella Enterprise' => ['balance' => 500, 'budgeted' => 0],
                            'Fransoft Inc.' => ['balance' => 200, 'budgeted' => 0],
                        ]
                    ],
         *     ]
         * ],
         *
         * 'asset' => [
         *

            'Non-Current Assets' => [
                'subtotal' => 500,
                'budgeted_subtotal' => 1000,
                'ledgers' => [
                    'Motor Vehicles - @ Cost' => ['balance' => 20, 'budgeted' => 100],
                    'Computer Equipment - Accum Depre' => ['balance' => 180, 'budgeted' => 300],
                    'Furniture & Fittings - Accum Depre' => ['balance' => 300, 'budgeted' => 600]
                ]
            ],

          'Customer-Control Assets' => [
               'subtotal' => 4000,
               'budgeted_subtotal' => 11000,
               'ledgers' => [
                   'Interest Receivables-Refinanced' => ['balance' => 2000, 'budgeted' => 3000],
                   'Interest Income-GRZ' => ['balance' => 1000, 'budgeted' => 4000],
                   'Prepayments / Deferred Expenses' => ['balance' => 1000, 'budgeted' => 4000],
               ]
          ]
        ])

         */

        $balanceSheet = LedgerCategory::with('ledgers.entries')
            ->whereIn('type', ['asset', 'capital', 'liab'])
            ->whereHas('ledgers.entries', function ($query) use ($date) {
                /**
                 * Caveat: not all entries have transactions backing them, case in point,
                 * Opening Balance entries. Ideally, value_date of the transaction would've
                 * been used to filter the entries here. In view of that, use the date the
                 * entry was created.
                 */
                return $query->whereDate('created_at', '<=', $date);
            })
            ->get()
            ->groupBy('type')
            ->reverse() // show ledgers in order of assets, liab, capital
            ->map(function (Collection $collection) {
                return $collection->flatMap(function (LedgerCategory $category) { // get ledgers under this cat with their closing balances
                    return [
                        $category->name => collect([
                            'ledgers' => $category->ledgers->flatMap(function (Ledger $ledger) {
                                return [
                                    $ledger->name => collect([
                                        'balance' => $ledger->getClosingBalance(false),
                                        'budgeted' => 0
                                    ])
                                ];
                            })
                        ])
                    ];
                });
            })
            ->map(function (Collection $collection, $ledgerCategoryType) use ($date) {

                // Append the Net Profit as at now to Share Capital
                if ($ledgerCategoryType === LedgerCategory::CAPITAL) {

                    $this->request->merge(['startDate' => Carbon::today()->startOfYear(), 'endDate' => $date]);

                    $collection->first()->get('ledgers')['Net Profit/Loss'] = $this->dispatch(
                        new GenerateIncomeStatementReportJob($this->request)
                    )->net_profit;
                }

                return $collection;
            })
            ->map(function (Collection $collection) {

                $collection->each(function (Collection $collection) {
                   $collection['subtotal'] = $collection->get('ledgers')->sum('balance');
                   $collection['subtotal_budgeted'] = $collection->get('ledgers')->sum('budgeted');
                });

                $collection->total = $collection->sum('subtotal');
                $collection->budgeted_total = $collection->sum('subtotal_budgeted');

                return $collection;

            });

        $balanceSheet->totalLiabilitiesAndCapital = array_sum([
            $balanceSheet->get('liab')->total ?? 0,
            $balanceSheet->get('capital')->total ?? 0
        ]);

        $balanceSheet->date = $date;

        return $balanceSheet;
    }
}
