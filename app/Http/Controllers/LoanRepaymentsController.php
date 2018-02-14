<?php

namespace App\Http\Controllers;

use App\Entities\LoanRepayment;
use App\Entities\User;
use App\Http\Requests\GetDueRepaymentsFormRequest;


class LoanRepaymentsController extends Controller
{
    public function due(GetDueRepaymentsFormRequest $request)
    {
        $repayments = LoanRepayment::withoutGlobalScope('paid')->paginate(20);
        //dispatch(new GetRepaymentsJob($request));

        $creditOfficers = User::creditOfficers();

        return view('dashboard.loans.due', compact('repayments', 'creditOfficers'));
    }
}
