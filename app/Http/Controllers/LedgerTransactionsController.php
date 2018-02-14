<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerTransaction;
use App\Entities\Accounting\UnapprovedLedgerTransaction;
use App\Exceptions\LedgerEntryException;
use App\Jobs\AddLedgerTransactionJob;
use Illuminate\Http\Request;

class LedgerTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = LedgerTransaction::where('branch_id', auth()->user()->branch->id)
            ->with('entries', 'user')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('dashboard.accounting.transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $transaction = new LedgerTransaction;
        $ledgers = Ledger::with('category')->get();

        return view('dashboard.accounting.transactions.create', compact('transaction', 'ledgers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Grab what is needed from an unapproved transaction and get rid of it
            $unapprovedTransaction = UnapprovedLedgerTransaction::findOrFail($request->get('unapproved_transaction_id'));

            $entries = $unapprovedTransaction->entries->toArray();

            $request->replace(compact('entries'));

            $transaction = $this->dispatchNow(new AddLedgerTransactionJob($request));

            $unapprovedTransaction->delete();
        } catch (LedgerEntryException $exception) {
            logger()->error('Error occurred, could not save ledger transaction', compact('exception'));

            flash()->error($exception->getMessage());

            return back()->withInput();
        }

        flash()->success('The transaction has been posted to the General Ledger');

        return redirect()->route('accounting.transactions.show', compact('transaction'));
    }

    /**
     * Display the specified resource.
     *
     * @param LedgerTransaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(LedgerTransaction $transaction)
    {
        $transaction->load('user', 'entries.ledger');

        return view('dashboard.accounting.transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
