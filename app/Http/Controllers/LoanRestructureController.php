<?php

namespace App\Http\Controllers;

use App\Entities\Loan;
use App\Jobs\LoanSearchJob;
use App\LoanRestructure;
use Illuminate\Http\Request;

class LoanRestructureController extends Controller
{
    public function index(Request $request)
    {
        $request->merge(['status' => Loan::RESTRUCTURED]);

        $loans = $this->dispatch(new LoanSearchJob($request));

        return view('dashboard.loans.restructured', compact('loans'));
    }
}
