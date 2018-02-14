<?php

namespace App\Http\Controllers;

use App\Entities\Loan;
use App\Jobs\DisburseLoanJob;
use App\Jobs\GetDisbursedLoansJob;
use App\Jobs\LoanSearchJob;
use Illuminate\Http\Request;


class DisburseLoanController extends Controller
{
    public function disburse(Request $request, Loan $loan)
    {
        try {
            dispatch(new DisburseLoanJob($request, $loan));
        } catch (\Exception $exception) {
            logger('Error occurred while disbursing loan: ', compact('exception'));

            flash()->error('Loan could not be disbursed. Please try again. Error: '. $exception->getMessage());

            return back();
        }

        flash()->success('Loan is successfully disbursed.');

        return redirect()->route('loans.show', compact('loan'));
    }

    public function index(Request $request)
    {
        $request->merge(['status' => 'disbursed']);

        $loans = $this->dispatch(new LoanSearchJob($request));

        return view('dashboard.loans.disbursed', compact('loans'));
    }
}
