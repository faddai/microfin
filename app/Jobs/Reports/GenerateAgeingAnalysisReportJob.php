<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 25/04/2017
 * Time: 19:23
 */

namespace App\Jobs\Reports;


use App\Contracts\ReportsInterface;
use App\Entities\LoanRepayment;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateAgeingAnalysisReportJob implements ReportsInterface
{
    use DecoratesReport;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Collection
     */
    protected $report;

    /**
     * GenerateAgeingAnalysisReportJob constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->report = collect();
        $this->report->totals = collect();

        $this->normalizeAndSetDate();
    }

    public function handle(): Collection
    {
        LoanRepayment::ageing($this->request)
            ->sortBy(function (Collection $collection) {
                return $collection->first()->loan->client->name;
            })
            ->map(function (Collection $repayments) {

                $loan = $repayments->first()->loan;

                $totalLoanBalance = 0;

                $this->report->push(collect([
                    'ageing' => $repayments->groupBy(function (LoanRepayment $repayment) {

                        return $this->getAgeGroup($repayment);

                    })->map(function (Collection $repayments) use (&$totalLoanBalance) {

                        // Sum up the balances from each of the age groups
                        $ageGroupTotals = $repayments->sum(function (LoanRepayment $repayment) {

                            return $repayment->getOutstandingRepaymentAmount(false);

                        });

                        // Total balance remaining for this loan
                        $totalLoanBalance += $ageGroupTotals;

                        $this->report->totals->put('Total', $this->report->totals->get('Total', 0) + $totalLoanBalance);

                        return $ageGroupTotals;

                    })->each(function (float $amount, $groupName) {

                        // sum totals for all the age groups
                        $this->report->totals->put($groupName, $this->report->totals->get($groupName, 0) + $amount);

                    })->map(function (float $amount) {

                        // format numbers to make them human readable
                        return number_format($amount, 2);

                    }),

                    'client' => collect([
                        'id' => $loan->client->id,
                        'name' => $loan->client->getFullName(),
                    ]),

                    'loan' => collect([
                        'id' => $loan->id,
                        'number' => $loan->number
                    ]),

                    'Total' => number_format($totalLoanBalance, 2)
                ]));
            });

        $this->setReportTitleAndDescription();

        $this->prependHeaderToReport();

        return $this->report;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Ageing Analysis';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Ageing Analysis of Balances as at '. $this->request->get('date')->format(config('microfin.dateFormat'));
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return [
            'Customer Name',
            'Loan #',
            'Current',
            '1 - 29 Days',
            '30 - 59 Days',
            '60 - 89 Days',
            '90 - 119 Days',
            'Over 119 Days',
            'Total',
        ];
    }

    /**
     * @param LoanRepayment $repayment
     * @return string
     */
    private function getAgeGroup(LoanRepayment $repayment)
    {
        $group = 'Current';

        if ($repayment->isDue()) {

            $age = $repayment->due_date->diffInWeekdays(Carbon::today(), false);

            if ($age > 0 && $age < 30) {
                $group = '1-29';
            } elseif ($age > 29 && $age < 60) {
                $group = '30-59';
            } elseif ($age > 59 && $age < 90) {
                $group = '60-89';
            } elseif ($age > 89 && $age < 120) {
                $group = '90-119';
            } elseif ($age > 119) {
                $group = 'Over 119';
            }
        }

        return $group;

    }

    /**
     * @return Collection
     */
    public function downloadAsCsv()
    {
        $report = $this->handle();

        $report->shift();

        $_report = $report->map(function (Collection $collection) {

            $ageing = $collection->get('ageing');
            $default = '';

            return [
                'Customer Name' => data_get($collection, 'client.name'),
                'Loan Number' => sprintf('\'%s', data_get($collection, 'loan.number')),
                'Current' => $ageing->get('Current', $default),
                '1 - 29 Days' => $ageing->get('1-29', $default),
                '30 - 59 Days' => $ageing->get('30-59', $default),
                '60 - 89 Days' => $ageing->get('60-89', $default),
                '90 - 119 Days' => $ageing->get('90-119', $default),
                'Over 119 Days' => $ageing->get('Over 119', $default),
                'Total' => $collection->get('Total'),
            ];
        });

        $totals = [];

        collect(['Current', '1-29', '30-59', '60-89', '90-119', 'Over 119','Total'])
            ->each(function ($key) use ($report, &$totals) {
                $totals[] = '"'. number_format($report->totals->get($key), 2) .'"';
            });

        $_report->push(vsprintf(',,%s,%s,%s,%s,%s,%s,%s', $totals));

        $_report->meta = collect([
            'Ageing Analysis Report' => '',
            'Date' => sprintf('"%s"', $this->request->get('date')->format(config('microfin.dateFormat'))),
        ]);

        return $_report;
    }

}