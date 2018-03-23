<?php

namespace App\Http\Controllers;

use App\Entities\BusinessSector;
use App\Entities\LoanType;
use App\Entities\User;
use App\Jobs\Reports\GenerateLoanBookReportJob;
use App\Jobs\Reports\GetLoanReportJob;
use App\Jobs\Reports\GetLoanReportListJob;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoanReportsController extends Controller
{
    public function index()
    {
        $reports = $this->dispatch(new GetLoanReportListJob());

        return view('dashboard.reports.loans.index', compact('reports'));
    }

    public function show(Request $request, $report)
    {
        $creditOfficers = User::creditOfficers();

        $loanTypes = LoanType::all();

        $sectors = BusinessSector::all();

        $report = $this->dispatch(new GetLoanReportJob($request, $report));

        return view('dashboard.reports.loans.show', compact('report', 'creditOfficers', 'loanTypes', 'sectors'));
    }

    public function download(Request $request, $report)
    {
        $options = [
            'view' => 'pdf.'. str_slug($report, '_'),
            'dataKey' => 'report',
            'filename' => sprintf('%s-%s', $report, Carbon::today()->format('Y-m-d'))
        ];

        $report = $this->dispatch(new GetLoanReportJob($request, $report));

        return $this->export($request, $report, $options);
    }
}
