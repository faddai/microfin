<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Loan;
use App\Traits\DecoratesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class GenerateAgeGroupReportJob implements ReportsInterface
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
        $this->report = Loan::disbursed()
            ->get()
            ->map(function (Loan $loan) {
                $loan['age_group'] = in_array($loan->age_group, ['N/A', ''], true) ? 'Others' : $loan->age_group;

                return $loan;
            })
            ->groupBy('age_group')
            ->flatMap(function (Collection $loans, $ageGroup) {

                return collect([$ageGroup => $loans->sum('amount')]);
            })
            ->sortBy(function ($amount, $ageGroup) {
                // sort by the starting age, where applicable, so that keys will
                // appear in ascending order
                return explode('-', $ageGroup)[0];
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
        return 'Age Group Report';
    }

    /**
     * Returns the description of this report
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getTitle(). ' as at '. Carbon::today()->format(config('microfin.dateFormat'));
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
            'Age Group',
            'Loan Amount'
        ];
    }

    /**
     * @return Collection
     */
    public function downloadAsCsv()
    {
        $report = $this->handle();

        $report->shift();

        $_report = $report->map(function ($amount, $k) {
            return [
                'Age Group' => $k,
                'Loan Amount' => number_format($amount, 2)
            ];
        });

        $_report->push(sprintf(',"%s"', number_format($report->sum(), 2)));

        $_report->meta = collect([
            $this->getTitle() => '',
            sprintf('"%s"', $this->getDescription()) => '',
        ]);

        return $_report;
    }

}
