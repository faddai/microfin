<?php

namespace App\Http\Controllers;

use App\Jobs\GetEffectiveLoanDeductionsJob;
use Carbon\Carbon;
use Illuminate\Http\Request;


class EffectiveLoanDeductionsController extends Controller
{
    public function index(Request $request)
    {
        $deductions = [];
        $startDate = Carbon::parse(request('startDate', Carbon::today()))->format('M d, Y');
        $endDate = Carbon::parse(request('endDate', Carbon::today()))->format('M d, Y');

        try {
            $deductions = dispatch(new GetEffectiveLoanDeductionsJob($request));
        } catch (\Exception $e) {
            logger()->error('Error whiles retrieving loan deductions', [$e]);
        }

        return view('dashboard.loans.deductions', compact('deductions', 'startDate', 'endDate'));
    }
}