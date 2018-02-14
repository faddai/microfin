<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\LedgerCategory;
use App\Entities\Client;
use App\Http\Requests\DepositFormRequest;
use App\Jobs\AddClientDepositJob;


class ClientDepositsController extends Controller
{
    public function create()
    {
        $clients = Client::with(['clientable'])->get();
        $ledgers = LedgerCategory::getBankOrCashLedgers();

        return view('dashboard.deposits.create', compact('clients', 'ledgers'));
    }

    public function store(DepositFormRequest $request)
    {
        try {
            $this->dispatch(new AddClientDepositJob($request, Client::findOrFail($request->get('client_id'))));
        } catch (\Exception $exception) {
            logger('Deposit could not be recorded', compact('exception'));

            flash()->error('There was an error adding the deposit. Please try again. Error: '. $exception->getMessage());

            return back()->withInput();
        }

        flash()->success('The Client\'s account has been credited with the deposit amount.');

        return redirect()->route('client.transactions.index');
    }
}
