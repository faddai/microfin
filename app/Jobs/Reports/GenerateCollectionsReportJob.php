<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 27/04/2017
 * Time: 08:52
 */

namespace App\Jobs\Reports;


use App\Contracts\ReportsInterface;
use App\Entities\Loan;
use App\Entities\LoanRepayment;
use App\Exceptions\NoDataAvailableForExportException;
use App\Traits\DecoratesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateCollectionsReportJob implements ReportsInterface
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
        Loan::collection($this->request)
            ->each(function (Loan $loan) {

                $repaymentAmountDue = $loan->schedule->sum(function (LoanRepayment $repayment) {
                    return $repayment->isDue() ? $repayment->getOutstandingRepaymentAmount(false) : 0;
                });

                $repaymentCollected = $loan->repaymentCollections->sum('amount');

                $percentageCollected = $repaymentAmountDue > 0 ? ($repaymentCollected / $repaymentAmountDue) * 100 : 0;

                $this->report->push(collect([
                    'repayment_amount_due' => number_format($repaymentAmountDue, 2),
                    'loan_amount_collected' => number_format($repaymentCollected, 2),
                    'percentage_collected' => number_format($percentageCollected, 2),
                    'credit_officer' => $loan->creditOfficer->getFullName(),

                    'loan' => collect([
                        'id' => $loan->id,
                        'number' => $loan->number,
                    ]),

                    'client' => collect([
                        'id' => $loan->client->id,
                        'name' => $loan->client->getFullName(),
                    ]),
                ]));

                $this->report->totals->put(
                    'repayment_amount_due',
                    $this->report->totals->get('repayment_amount_due') + $repaymentAmountDue
                );

                $this->report->totals->put(
                    'loan_amount_collected',
                    $this->report->totals->get('loan_amount_collected') + $repaymentCollected
                );

                $this->report->totals->put(
                    'percentage_collected',
                    $this->report->totals->get('percentage_collected') + $percentageCollected
                );

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
        return 'Collections Report';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     * @throws \InvalidArgumentException
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
        return [
            'Name',
            'Account Number',
            'Repayments Due',
            'Loan amount collected',
            '% Collected',
            'Credit Officer',
        ];
    }

    /**
     * @return Collection
     * @throws \App\Exceptions\NoDataAvailableForExportException
     */
    public function downloadAsCsv()
    {
        $report = $this->handle();

        $report->shift();

        if ($report->count()) {

            $_report = $report->map(function (Collection $collection) {

                $loan = $collection->get('loan');

                return [
                    'Customer Name' => data_get($collection, 'client.name'),
                    'Loan Number' => sprintf('\'%s', $loan->get('number')),
                    'Repayments Due' => $collection->get('repayment_amount_due'),
                    'Loan amount collected' => $collection->get('loan_amount_collected'),
                    '% Collected' => $collection->get('percentage_collected'),
                    'Credit Officer' => $collection->get('credit_officer'),
                ];
            });

            $totals = [];

            collect(['repayment_amount_due', 'loan_amount_collected', 'percentage_collected'])
                ->each(function ($key) use ($report, &$totals) {
                    $totals[] = '"'. number_format($report->totals->get($key), 2) .'"';
                });

            $_report->push(vsprintf(',,%s,%s,%s,', $totals));

            $_report->meta = collect([
                $this->getTitle() => '',
                'From Date' => sprintf('"%s"', $this->request->get('startDate')->format(config('microfin.dateFormat'))),
                'To Date' => sprintf('"%s"', $this->request->get('endDate')->format(config('microfin.dateFormat'))),
            ]);

            return $_report;
        }

        throw new NoDataAvailableForExportException;
    }

}