<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\LoanRepayment;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GeneratePortfolioAtRiskReportJob implements ReportsInterface
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
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->report = collect();
        $this->report->totals = collect([
            'loan_amount' => 0,
            'principal_due' => 0,
            'interest_due' => 0,
            'amount_due' => 0,
        ]);

        $this->normalizeAndSetDate();
    }

    /**
     * Execute the job.
     *
     * @return Collection
     */
    public function handle(): Collection
    {
        LoanRepayment::par($this->request)
            ->sortBy(function (Collection $repayments) {
                return $repayments->first()->loan->client->name;
            })
            ->map(function (Collection $repayments) {

                $loan = $repayments->first()->loan;

                $principalDue = $repayments->sum('principal') - $repayments->sum('paid_principal');
                $interestDue = $repayments->sum('interest') - $repayments->sum('paid_interest');
                $totalAmountDue = $principalDue + $interestDue;

                $this->report->push(collect([
                    'principal_due' => number_format($principalDue, 2),
                    'interest_due' => number_format($interestDue, 2),
                    'p+i_due' => number_format($totalAmountDue, 2),
                    'client' => collect([
                        'id' => $loan->client->id,
                        'name' => $loan->client->getFullName(),
                        'number' => $loan->client->account_number
                    ]),
                    'loan' => collect([
                        'id' => $loan->id,
                        'amount' => $loan->getPrincipalAmount(),
                        'number' => $loan->number,
                        'credit_officer' => $loan->creditOfficer->getFullName() ?? 'n/a',
                    ])
                ]));

                $this->report->totals->put('loan_amount', $this->report->totals->get('loan_amount') + $loan->getPrincipalAmount(false));
                $this->report->totals->put('principal_due', $this->report->totals->get('principal_due') + $principalDue);
                $this->report->totals->put('interest_due', $this->report->totals->get('interest_due') + $interestDue);
                $this->report->totals->put('amount_due', $this->report->totals->get('amount_due') + $totalAmountDue);
            });

        if ($this->report->count()) {
            $this->report->par = ($this->report->totals->get('principal_due') / $this->report->totals->get('loan_amount')) * 100;
        }

        $this->setReportTitleAndDescription();

        $this->prependHeaderToReport();

        return $this->report;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return [
            '#',
            'Loan Number',
            'Client',
            'Credit Officer',
            'Loan Amount',
            'Principal past due',
            'Interest past due',
            'Past Due(P+I)',
        ];
    }

    /**
     * @return mixed|null
     */
    private function getNumberOfDaysInRequest()
    {
        return $this->request->has('no_of_days') && $this->request->get('no_of_days') !== '' ?
            $this->request->get('no_of_days') : 120;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return sprintf('Portfolio at Risk >%d (%s)',
            $this->getNumberOfDaysInRequest(), number_format($this->report->par ?? 0, 2) .'%'
        );
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf('%d-Day Portfolio At Risk as at %s',
            $this->getNumberOfDaysInRequest(), $this->request->get('date')->format(config('microfin.dateFormat')));
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
                'Credit Officer' => $loan->get('credit_officer'),
                'Loan Amount' => $loan->get('amount'),
                'Principal past due' => $collection->get('principal_due'),
                'Interest past due' => $collection->get('interest_due'),
                'Past Due(P+I)' => $collection->get('p+i_due'),
            ];
        });

        $totals = [];

        collect(['loan_amount', 'principal_due', 'interest_due', 'amount_due'])
            ->each(function ($key) use ($report, &$totals) {
                $totals[] = '"'. number_format($report->totals->get($key), 2) .'"';
            });

        $_report->push(vsprintf(',,,%s,%s,%s,%s', $totals));

        $_report->meta = collect([
            sprintf('%d-Day Portfolio At Risk', $this->getNumberOfDaysInRequest()) => '',
            'Date' => '"'. $this->request->get('date')->format(config('microfin.dateFormat')) .'"',
            sprintf('PAR >%d', $this->getNumberOfDaysInRequest()) => number_format($report->par ?? 0, 2) .'%',
        ]);

        return $_report;
    }
}
