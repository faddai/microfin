<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\LoanRepayment;
use App\Traits\DecoratesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateMonthlyCollectionProjectionsReportJob implements ReportsInterface
{
    use DecoratesReport;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Collection
     */
    private $report;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->report = collect();
        $this->report->totals = collect();

        $this->setStartAndEndDates();
    }

    /**
     * Add logic to retrieve data for this report
     *
     * @return Collection
     */
    public function handle(): Collection
    {
        // get repayments that'd be due from the startDate to the endDate
        LoanRepayment::monthlyCollectionProjections($this->request)
            ->sortBy(function (Collection $repayments) {
                return $repayments->first()->loan->client->name;
            })
            ->each(function (Collection $repayments) {

                $loan = $repayments->first()->loan;

                $totalAmountDueForLoan = $repayments->sum(function (LoanRepayment $repayment) {
                    return $repayment->getOutstandingRepaymentAmount(false);
                });

                $this->report->push(collect([
                    'loan' => collect([
                        'id' => $loan->id,
                        'number' => $loan->number,
                        'matures' => $loan->maturity_date->format(config('microfin.dateFormat')),
                    ]),

                    'client' => collect([
                        'id' => $loan->client->id,
                        'name' => $loan->client->getFullName(),
                    ]),

                    'total_due_for_collection' => number_format($totalAmountDueForLoan, 2)
                ]));

                $this->report->totals = collect([
                    'total' => $this->report->totals->get('total') + $totalAmountDueForLoan,
                ]);

            });

        $this->setReportTitleAndDescription();

        $this->prependHeaderToReport();

        return $this->report;
    }

    /**
     * Returns the title of this report
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Monthly Collection Projections';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf(
            '%s from %s to %s',
            $this->getTitle(),
            $this->request->get('startDate')->format(config('microfin.dateFormat')),
            $this->request->get('endDate')->format(config('microfin.dateFormat'))
        );
    }

    /**
     * Returns the heading used to display report data in HTML table
     * or exported file formats (CSV, Excel, PDF)
     *
     * @return array
     */
    public function getHeader(): array
    {
        $header = [
            'Customer Name',
            'Loan Number',
            'Matures',
            'Total',
        ];

        return $header;
    }

    /**
     * @return Collection
     */
    public function downloadAsCsv()
    {
        $report = $this->handle();

        $report->shift();

        $_report = $report->map(function (Collection $collection) {

            $loan = $collection->get('loan');

            return [
                'Customer Name' => data_get($collection, 'client.name'),
                'Loan Number' => sprintf('\'%s', $loan->get('number')),
                'Matures' => $loan->get('matures'),
                'Total' => $collection->get('total_due_for_collection'),
            ];
        });

        $totals = [];

        collect(['total'])
            ->each(function ($key) use ($report, &$totals) {
                $totals[] = '"'. number_format($report->totals->get($key), 2) .'"';
            });

        $_report->push(vsprintf(',,,%s', $totals));

        $_report->meta = collect([
            $this->getTitle() => '',
            'From Date' => sprintf('"%s"', $this->request->get('startDate')->format(config('microfin.dateFormat'))),
            'To Date' => sprintf('"%s"', $this->request->get('endDate')->format(config('microfin.dateFormat'))),
        ]);

        return $_report;
    }

}
