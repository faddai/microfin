<?php

namespace App\Jobs\Reports;

use Illuminate\Support\Collection;

class GetLoanReportListJob
{
    /**
     * Execute the job.
     *
     * @return Collection
     */
    public function handle()
    {
        return collect(config('microfin.reports.loans'))
            ->reject(function (array $report) {
                return array_key_exists('hide', $report) && app()->environment() !== 'local';
            })
            ->map(function (array $report) {
                $report['url'] = route('reports.loans.show', ['report' => str_slug($report['title'])]);
                return collect($report);
            });
    }
}
