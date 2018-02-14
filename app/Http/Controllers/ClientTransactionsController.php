<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\LedgerCategory;
use App\Entities\Client;
use App\Exceptions\NoDataAvailableForExportException;
use App\Jobs\Exports\GetDataForClientTransactionsExport;
use App\Jobs\GetClientTransactionsJob;
use Illuminate\Http\Request;


class ClientTransactionsController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::with('clientable')->get();
        $ledgers = LedgerCategory::getBankOrCashLedgers();
        $transactions = $this->dispatch(new GetClientTransactionsJob($request));

        return view('dashboard.client_transactions.index', compact('clients', 'ledgers', 'transactions'));
    }

    public function downloadClientTransactions(Request $request, Client $client)
    {
        $options['filename'] = 'client-transactions-'. $client->account_number;
        $options['view'] = 'pdf.client_transactions';
        $options['dataKey'] = 'transactions';

        $transactions = $this->dispatch(new GetDataForClientTransactionsExport($request, $client));

        if (! $transactions->count()) {
            throw new NoDataAvailableForExportException;
        }

        return $this->export($request, $transactions, $options);
    }
}