<?php

namespace App\Http\Controllers;

use App\Entities\Loan;
use App\Jobs\ApproveLoanJob;
use App\Jobs\GetApprovedLoansJob;
use App\Jobs\LoanSearchJob;
use Illuminate\Http\Request;


class ApproveLoanController extends Controller
{
    public function approve(Request $request, Loan $loan)
    {
        try {
            dispatch(new ApproveLoanJob($request, $loan));
        } catch (\Exception $e) {
            logger('Error occurred approving loan', ['error' => $e->getMessage()]);

            flash()->error('The loan could not be approved. Please try again');

            return back();
        }

        flash()->success('The loan is approved');

        return redirect()->route('loans.index');
    }

    public function approved(Request $request)
    {
        $request->merge(['status' => Loan::APPROVED]);

        $loans = dispatch(new LoanSearchJob($request));

        return view('dashboard.loans.approved', compact('loans'));
    }
}
