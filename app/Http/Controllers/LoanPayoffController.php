<?php

namespace App\Http\Controllers;

use App\Entities\Loan;
use App\Entities\LoanPayoff;
use App\Jobs\AddLoanPayoffJob;
use App\Jobs\ApproveLoanPayoffJob;
use App\Jobs\GetLoansAwaitingPayoffApproval;
use Illuminate\Http\Request;

class LoanPayoffController extends Controller
{
    public function index(Request $request)
    {
        $payoffs = $this->dispatch(new GetLoansAwaitingPayoffApproval($request));

        return view('dashboard.loans.payoff.index', compact('payoffs'));
    }

    /**
     * @param Request $request
     * @param Loan $loan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Loan $loan)
    {
        try {
            $this->dispatch(new AddLoanPayoffJob($request, $loan));

            flash()->success('Loan payoff request sent, pending approval');
        } catch (\Exception $exception) {
            logger()->debug('Error occurred: ', compact('exception'));

            flash()->error('An error occurred whiles paying off loan. Error: '. $exception->getMessage());
        }

        return back();
    }

    public function approve(Request $request, LoanPayoff $payoff)
    {
        try {
            $this->dispatch(new ApproveLoanPayoffJob($request, $payoff));
        } catch (\Exception $exception) {
            logger()->debug('Error occurred: ', compact('exception'));

            flash()->error('An error occurred whiles paying off loan. Error: '. $exception->getMessage());

            return back();
        }

        return redirect()->route('loans.show', ['loan' => $payoff->loan]);
    }

    public function decline(Request $request, LoanPayoff $payoff)
    {
        $this->validate($request, ['decline_reason' => 'required|min:4']);

        flash()->error('There was an error deleting the Payoff request. Please try again later.');

        $request->has('decline_reason') && $payoff->update(['decline_reason' => $request->get('decline_reason')]);

        $payoff->delete() && flash()->success('The Payoff request has been deleted');

        return back();
    }
}
