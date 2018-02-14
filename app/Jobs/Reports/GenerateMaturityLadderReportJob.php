<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Loan;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateMaturityLadderReportJob implements ReportsInterface
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
        Loan::maturityLadder($this->request)
            ->get()
            ->sortBy('name')
            ->each(function (Loan $loan) {
                $this->report->push(collect([
                    'loan' => collect([
                        'id' => $loan->id,
                        'number' => $loan->number,
                        'amount' => $loan->getPrincipalAmount(),
                        'maturing_amount' => $loan->getTotalLoanAmount(),
                        'product' => $loan->product->getDisplayName(),
                        'maturity' => $loan->maturity_date->format(config('microfin.dateFormat')),
                    ]),

                    'client' => collect([
                        'id' => $loan->id,
                        'name' => $loan->client->getFullName(),
                    ]),
                ]));

                $totalLoanAmounts = $this->report->totals->get('amount') + $loan->getPrincipalAmount(false);
                $totalMaturingAmounts = $this->report->totals->get('maturing_amount') + $loan->getTotalLoanAmount(false);

                $this->report->totals->put('amount', $totalLoanAmounts);
                $this->report->totals->put('maturing_amount', $totalMaturingAmounts);
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
        return 'Maturity Ladder';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf('%s from %s to %s',
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
        return [
            'Name',
            'Loan Number',
            'Product',
            'Loan Amount',
            'Maturing Amount',
            'Maturity Date',
        ];
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
                'Product' => $loan->get('product'),
                'Loan Amount' => $loan->get('amount'),
                'Maturing Amount' => $loan->get('maturing_amount'),
                'Maturity Date' => $loan->get('maturity'),
            ];
        });

        $totals = [];

        collect(['amount', 'maturing_amount'])->each(function ($key) use ($report, &$totals) {
            $totals[] = '"'. number_format($report->totals->get($key), 2) .'"';
        });

        $_report->push(vsprintf(',,,%s,%s,', $totals));

        $_report->meta = collect([
            'Maturity Ladder' => '',
            'From Date' => sprintf('"%s"', $this->request->get('startDate')->format(config('microfin.dateFormat'))),
            'To Date' => sprintf('"%s"', $this->request->get('endDate')->format(config('microfin.dateFormat'))),
        ]);

        return $_report;
    }

}
