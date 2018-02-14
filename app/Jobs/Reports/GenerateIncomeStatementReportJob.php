<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 17/04/2017
 * Time: 17:00
 */

namespace App\Jobs\Reports;


use App\Entities\Accounting\LedgerEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateIncomeStatementReportJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * GetIncomeStatementReport constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle()
    {

        $startDate = Carbon::parse($this->request->get('startDate'));
        $endDate = Carbon::parse($this->request->get('endDate'));

        /**
        get all ledgers and group them under their respective categories
        get the ledger closing balance at the date range

        Sample output:

            collect([
                'income' => [
                    'total' => 2000,
                    'ledgers' => [
                        'Bank interest' => 1290,
                        'Yello card' => 10,
                        'Mabella Enterprise' => 500,
                        'Fransoft Inc.' => 200
                    ]
                ],
                'expense' => [
                    'total' => 500,
                    'ledgers' => [
                        'Bank charges' => 20,
                        'Transportation' => 180,
                        'Yin Yang Lui' => 300
                    ]
                ]
            ]);
        */

        $collection = LedgerEntry::with('ledger.category')
            ->whereHas('ledger.category', function ($query) {
                return $query->whereIn('id', [7, 8]);
            })
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                return $query->whereBetween(DB::raw('date(value_date)'), [$startDate, $endDate]);
            })
            ->get()
            ->groupBy(function (LedgerEntry $entry) {
                return $entry->ledger->category->name;
            })
            ->map(function (Collection $ledgerEntriesGroup) {

                return collect([
                    'total' => $ledgerEntriesGroup->sum(function (LedgerEntry $entry) {
                        return $entry->dr + $entry->cr;
                    }),

                    'total_budgeted' => 0,

                    'ledgers' => $ledgerEntriesGroup->groupBy(function (LedgerEntry $entry) {
                        return $entry->ledger->name;
                    })->flatMap(function (Collection $entries, string $ledgerName) {
                        return [
                            $ledgerName => collect([
                                'balance' => $entries->sum(function (LedgerEntry $entry) {
                                    return $entry->dr + $entry->cr;
                                }),
                                'budgeted' => 0
                            ])
                        ];
                    }),
                ]);
            });

        $totalIncome = $collection->has('Income') ? $collection->get('Income')->get('total') : 0;
        $totalExpense = $collection->has('Expenses') ? $collection->get('Expenses')->get('total') : 0;

        $collection->net_profit = collect([
            'balance' => $totalIncome - $totalExpense,
            'budgeted' => 0
        ]);

        $collection->startDate = $startDate->format(config('microfin.dateFormat'));
        $collection->endDate = $endDate->format(config('microfin.dateFormat'));

        return $collection;
    }
}