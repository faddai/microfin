<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Loan;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateBusinessSectorReportJob implements ReportsInterface
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

        $this->normalizeAndSetDate();
    }

    /**
     * Add logic to retrieve data for this report
     *
     * @return Collection
     */
    public function handle(): Collection
    {
        Loan::businessSector($this->request)
            ->get()
            ->sortBy('name')
            ->each(function (Loan $loan) {
                $this->report->push(collect([
                    'loan' => collect([
                        'id' => $loan->id,
                        'number' => $loan->number,
                        'amount' => $loan->getPrincipalAmount(),
                        'disbursed_at' => $loan->disbursed_at->format(config('microfin.dateFormat')),
                        'product' => $loan->product->getDisplayName(),
                        'maturity' => $loan->maturity_date->format(config('microfin.dateFormat')),
                        'sector' => $loan->sector->name,
                        'type' => $loan->type->label,
                    ]),

                    'client' => collect([
                        'id' => $loan->id,
                        'name' => $loan->client->getFullName(),
                    ]),
                ]));

                $totalLoanAmounts = $this->report->totals->get('amount') + $loan->getPrincipalAmount(false);

                $this->report->totals->put('amount', $totalLoanAmounts);
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
        return 'Business Sector Report';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getTitle(). ' for Disbursed Loans as at '. $this->request->get('date')->format(config('microfin.dateFormat'));
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
            'Type',
            'Disbursed Date',
            'Loan Amount',
            'Sector',
            'Maturity',
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
                'Name' => data_get($collection, 'client.name'),
                'Loan Number' => sprintf("'%s", $loan->get('number')),
                'Product' => $loan->get('product'),
                'Type' => $loan->get('type'),
                'Disbursed Date' => $loan->get('disbursed_at'),
                'Loan Amount' => $loan->get('amount'),
                'Sector' => $loan->get('sector'),
                'Maturity' => $loan->get('maturity'),
            ];
        });

        $_report->push(sprintf(',,,,,"%s",,', number_format($report->totals->get('amount'), 2)));

        $_report->meta = collect([
            $this->getTitle() => '',
            sprintf('"%s"', $this->getDescription()) => '',
        ]);

        return $_report;
    }

}
