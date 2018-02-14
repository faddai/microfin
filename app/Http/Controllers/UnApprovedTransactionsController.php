<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\UnapprovedLedgerTransaction;
use App\Exceptions\LedgerEntryException;
use App\Http\Requests\AddLedgerTransactionFormRequest;
use App\Jobs\AddUnapprovedLedgerTransactionJob;


class UnApprovedTransactionsController extends Controller
{
    public function index()
    {
        $transactions = UnapprovedLedgerTransaction::with('user')
            ->whereBranchId(auth()->user()->branch->id)
            ->paginate(20);

        return view('dashboard.accounting.transactions.unapproved', compact('transactions'));
    }

    public function store(AddLedgerTransactionFormRequest $request)
    {
        try {
            $this->dispatch(new AddUnapprovedLedgerTransactionJob($request));
        } catch (LedgerEntryException $exception) {
            logger()->error('Error occurred, could not save ledger transaction', compact('exception'));

            flash()->error($exception->getMessage());

            return back()->withInput();
        }

        flash()->success('The transaction has been saved, pending approval');

        return redirect()->route('accounting.transactions.unapproved.index');
    }

    public function destroy()
    {
        try {
            $transaction = UnapprovedLedgerTransaction::findOrFail(request('unapproved_transaction_id'));

            $transaction->delete() && flash()->success('The transaction has been cancelled and deleted');
        } catch (\Exception $exception) {
            flash()->error('The transaction could not be cancelled. Error: '. $exception->getMessage());
        }

        return back();
    }
}
