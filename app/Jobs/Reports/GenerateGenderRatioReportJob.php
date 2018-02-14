<?php

namespace App\Jobs\Reports;

use App\Contracts\ReportsInterface;
use App\Entities\Client;
use App\Traits\DecoratesReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GenerateGenderRatioReportJob implements ReportsInterface
{
    use DecoratesReport;

    /**
     * @var Collection
     */
    private $report;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->report = collect();
    }

    /**
     * Add logic to retrieve data for this report
     *
     * @return Collection
     */
    public function handle(): Collection
    {
        $this->report = Client::with('clientable', 'loans')
            ->get()
            ->groupBy('clientable.gender')
            ->flatMap(function (Collection $clients, $genderGroup) {

                $genderGroup = $genderGroup === '' ? 'Others' : $genderGroup;

                return collect([
                    ucfirst($genderGroup) => $clients->sum(function (Client $client) {
                        return $client->loans->sum('amount');
                    })
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
        return 'Gender Ratio';
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
            'Gender',
            'Total Loan Amount'
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
                'Gender' => $k,
                'Total Loan Amount' => number_format($amount, 2)
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
