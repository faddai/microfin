<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Collateral;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateCollateralReportJob implements ReportsInterface
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
    }

    /**
     * Add logic to retrieve data for this report
     *
     * @return Collection
     */
    public function handle(): Collection
    {
        Collateral::with('loan.client.clientable', 'loan.product', 'loan.type')
            ->get()
            ->each(function (Collateral $collateral) {

                $this->report->push(collect([
                    'loan' => collect([
                        'id' => $collateral->loan->id,
                        'number' => $collateral->loan->number,
                        'disbursed_date' => $collateral->loan->disbursed_at ?
                            $collateral->loan->disbursed_at->format(config('microfin.dateFormat')) : 'n/a',
                        'amount' => $collateral->loan->getPrincipalAmount(),
                        'product' => $collateral->loan->product->name,
                        'type' => $collateral->loan->type->label,
                    ]),

                    'client' => collect([
                        'name' => $collateral->loan->client->getFullName(),
                        'id' => $collateral->loan->client->id
                    ]),

                    'collateral_type' => $collateral->label,
                    'collateral_value' => number_format($collateral->market_value, 2),
                    'percentage_coverage' => round(($collateral->market_value / $collateral->loan->getPrincipalAmount(false)) * 100, 2),
                ]));
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
        return 'Collateral Report';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getTitle();
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
            'Collateral type',
            'Collaterial Value',
            '% Coverage',
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
                'Collateral type' => $collection->get('collateral_type'),
                'Collaterial Value' => $collection->get('collateral_value'),
                '% Coverage' => $collection->get('percentage_coverage'),
            ];
        });

        $_report->meta = collect([
            $this->getTitle() => '',
            'Date' => sprintf('"%s"', Carbon::today()->format(config('microfin.dateFormat'))),
        ]);

        return $_report;
    }

}
