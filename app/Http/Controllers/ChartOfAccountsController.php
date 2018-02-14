<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\LedgerCategory;


class ChartOfAccountsController extends Controller
{
    public function index()
    {
        $ledgerCategories = LedgerCategory::with('ledgers')->get();
        $ledgerCategoryTypes = LedgerCategory::TYPES;

        return view('dashboard.accounting.chart', compact('ledgerCategories', 'ledgerCategoryTypes'));
    }
}
