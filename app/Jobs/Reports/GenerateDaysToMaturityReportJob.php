<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Loan;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateDaysToMaturityReportJob implements ReportsInterface
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
        Loan::daysToMaturity($this->request)
            ->each(function (Loan $loan) {

                $diffInDays = Carbon::today()->diffInWeekdays($loan->maturity_date, false);

                $this->report->push(collect([
                    'loan' => collect([
                        'id' => $loan->id,
                        'number' => $loan->number,
                        'disbursed_date' => $loan->disbursed_at ?
                            $loan->disbursed_at->format(config('microfin.dateFormat')) : 'n/a',
                        'amount' => $loan->getPrincipalAmount(),
                        'product' => $loan->product->name,
                        'type' => $loan->type->label,
                        'maturity' => $loan->maturity_date->format(config('microfin.dateFormat')),
                    ]),

                    'client' => collect([
                        'name' => $loan->client->getFullName(),
                        'id' => $loan->client->id
                    ]),

                    'days' => $diffInDays
                ]));

                $this->report->totals->put('amount', $this->report->totals->get('amount') + $loan->amount);
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
        return 'Days to Maturity';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getTitle(). ' as at '. $this->request->get('date')->format(config('microfin.dateFormat'));
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
            'Days to Maturity',
            'Maturity date',
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
                'Type' => $loan->get('type'),
                'Disbursed Date' => $loan->get('disbursed_date'),
                'Loan Amount' => $loan->get('amount'),
                'Days to Maturity' => $collection->get('days'),
                'Maturity date' => $loan->get('maturity'),
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
