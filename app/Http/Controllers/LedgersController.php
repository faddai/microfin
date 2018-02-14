<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerCategory;
use App\Jobs\Accounting\AddLedgerJob;
use Illuminate\Http\Request;


class LedgersController extends Controller
{
    protected $rules = [
        'category_id' => 'bail|required|numeric|exists:ledger_categories,id',
        'name' => 'required'
    ];

    public function index()
    {
        $categories = LedgerCategory::with('ledgers')->get();

        return view('dashboard.accounting.ledgers.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        try {
            dispatch(new AddLedgerJob($request));
        } catch (\Exception $e) {
            logger('Error occurred', ['error' => $e]);

            flash()->error('Ledger could not be added. Please try again.');

            return back()->withInput();
        }

        flash()->success('Ledger has been created.');

        return back();
    }

    public function update(Request $request, Ledger $ledger)
    {
        $this->validate($request, $this->rules);

        try {
            dispatch(new AddLedgerJob($request, $ledger));
        } catch (\Exception $e) {
            logger('Error occurred', ['error' => $e]);

            flash()->error('Ledger could not be updated. Please try again.');

            return back()->withInput();
        }

        flash()->success('Ledger has been updated.');

        return back();
    }

    public function show(Ledger $ledger)
    {
        $ledger->load('entries', 'entries.transaction');

        return view('dashboard.accounting.ledgers.show', compact('ledger'));
    }
}
