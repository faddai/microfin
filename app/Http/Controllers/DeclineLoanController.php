<?php

namespace App\Http\Controllers;

use App\Entities\Loan;
use App\Jobs\DeclineLoanJob;
use Illuminate\Http\Request;


class DeclineLoanController extends Controller
{
    public function decline(Request $request, Loan $loan)
    {
        try {
            dispatch(new DeclineLoanJob($request, $loan));
        } catch (\Exception $e) {
            logger()->error('An error occurred', [$e]);

            flash()->error('An error occurred, loan could not be declined.');

            return back();
        }

        flash()->success('Loan has been declined.');

        return redirect()->route('loans.index');
    }
}
