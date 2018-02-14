<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\LedgerCategory;
use App\Jobs\Accounting\AddLedgerCategoryJob;
use Illuminate\Http\Request;

class LedgerCategoriesController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required|in:'. collect(LedgerCategory::TYPES)->implode(',')
        ]);

        try {
            dispatch(new AddLedgerCategoryJob($request));
        } catch (\Exception $e) {
            logger('Error occurred', ['error' => $e]);

            flash()->error('Ledger category could not be added. Please try again.');

            return back()->withInput();
        }

        flash()->success('Ledger category has been created.');

        return back();
    }
}
