<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 26/04/2017
 * Time: 21:53
 */

namespace App\Jobs\Reports;


use App\Contracts\ReportsInterface;
use App\Entities\Loan;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateLoanBookReportJob implements ReportsInterface
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
     * GenerateLoanBookReportJob constructor.
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
        Loan::book($this->request)
            ->sortBy(function (Loan $loan) {
                return $loan->client->name;
            })
            ->each(function (Loan $loan) {

                $this->report->push(collect([
                    'number' => $loan->number,
                    'id' => $loan->id,
                    'disbursed' => $loan->disbursed_at->format(config('microfin.dateFormat')),
                    'maturity' => $loan->maturity_date->format(config('microfin.dateFormat')),
                    'amount' => $loan->getPrincipalAmount(),
                    'product' => $loan->product->name,
                    'type' => $loan->type->label,
                    'balance' => $loan->getBalance(),
                    'client' => collect([
                        'id' => $loan->client->id,
                        'name' => $loan->client->name
                    ]),
                ]));

                $this->report->totals->put('disbursed', $this->report->totals->get('disbursed') + $loan->getPrincipalAmount(false));
                $this->report->totals->put('balance', $this->report->totals->get('balance') + $loan->getBalance(false));
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
        return sprintf('Loan Book (%d)', $this->report->count());
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Loan Book as at '. $this->request->get('date')->format(config('microfin.dateFormat'));
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
            'Disbursed',
            'Maturity',
            'Disbursed Amount',
            'Balance',
        ];
    }

    /**
     * @return Collection
     */
    public function downloadAsCsv()
    {
        $report = $this->handle();

        $report->shift();

        $_report = $report->map(function (Collection $loan) {
            return [
                'Customer Name' => data_get($loan, 'client.name'),
                'Loan Number' => sprintf('\'%s', $loan->get('number')),
                'Product' => $loan->get('product'),
                'Type' => $loan->get('type'),
                'Disbursed' => $loan->get('disbursed'),
                'Maturity' => $loan->get('maturity'),
                'Disbursed Amount' => $loan->get('amount'),
                'Balance' => $loan->get('balance'),
            ];
        });

        $totals = [];

        collect(['disbursed', 'balance'])
            ->each(function ($key) use ($report, &$totals) {
                $totals[] = '"'. number_format($report->totals->get($key), 2) .'"';
            });

        $_report->push(vsprintf(',,,,,,%s,%s', $totals));

        $_report->meta = collect([
            'Loan Book' => $report->count(),
            'Date' => sprintf('"%s"', $this->request->get('date')->format(config('microfin.dateFormat'))),
        ]);

        return $_report;
    }
}