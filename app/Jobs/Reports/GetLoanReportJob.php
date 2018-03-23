<?php

namespace App\Jobs\Reports;

use App\Exceptions\ReportJobClassNotDefinedException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetLoanReportJob
{

    use DispatchesJobs;

    /**
     * @var Request
     */
    private $request;
    /**
     * @var string
     */
    private $report;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param string $report
     */
    public function __construct(Request $request, string $report)
    {
        $this->request = $request;
        $this->report = str_replace('report', '', $report); // if there's a report in report title, get rid of it
    }

    /**
     * Execute the job.
     *
     * @return Collection
     * @throws \Exception
     * @throws ReportJobClassNotDefinedException
     */
    public function handle()
    {
        // look up for the job that needs to work on this report
        $job = 'App\\Jobs\\Reports\\'. Str::studly(sprintf('generate_%s_report_job', $this->report));

        if (! class_exists($job)) {
            throw new ReportJobClassNotDefinedException($job);
        }

        // a download request
        if ($this->isReportDownloadRequest()) {
            $object = new $job($this->request);
            $method = 'downloadAs'. ucfirst(strtolower($this->request->get('format')));

            if (! method_exists($object, $method)) {
                throw new \Exception(sprintf('Method "%s" has not been defined', $method));
            }

            return $object->{$method}();
        }

        return $this->dispatch(new $job($this->request));
    }

    private function isReportDownloadRequest()
    {
        return $this->request->has('format') && $this->request->get('format');
    }
}
