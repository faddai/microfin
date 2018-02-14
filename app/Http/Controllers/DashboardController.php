<?php

namespace App\Http\Controllers;

use App\Entities\Client;
use App\Entities\Loan;

class DashboardController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clientsCount = Client::count();
        $loansCount = Loan::count();
        $loansValue = Loan::sum('amount');

        return view('dashboard.index', compact('clientsCount', 'loansCount', 'loansValue'));
    }
}
