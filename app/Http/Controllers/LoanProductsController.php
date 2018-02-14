<?php

namespace App\Http\Controllers;

use App\Entities\LoanProduct;
use App\Http\Requests\AddLoanProductFormRequest;
use App\Jobs\AddLoanProductJob;


class LoanProductsController extends Controller
{
    public function store(AddLoanProductFormRequest $request)
    {
        try {
            $this->dispatch(new AddLoanProductJob($request));
        } catch (\Exception $exception) {
            logger()->error('Loan product could not be added.', compact('exception'));

            flash()->error('Loan product could not be added. Please try again. Error: '. $exception->getMessage());

            return back()->withInput();
        }

        flash()->success('Loan product has been successfully added');

        return redirect(route_with_hash('settings.index', '#products'));
    }

    public function update(AddLoanProductFormRequest $request, LoanProduct $product)
    {
        try {
            $this->dispatch(new AddLoanProductJob($request, $product));
        } catch (\Exception $exception) {
            logger()->error('Loan product could not be updated.', compact('exception'));

            flash()->error('Loan product could not be updated. Please try again. Error: '. $exception->getMessage());

            return back()->withInput();
        }

        flash()->success('Loan product has been successfully updated');

        return redirect(route_with_hash('settings.index', '#products'));
    }

    public function destroy(LoanProduct $product)
    {
        try {
            $product->delete();

            cache()->forget('products');
        } catch (\Exception $exception) {
            logger()->error('Loan product could not be deleted.', compact('exception'));

            flash()->error('Loan product could not be deleted. Please try again. Error: ' . $exception->getMessage());
        }

        flash()->success('Loan product has been successfully deleted');

        return redirect(route_with_hash('settings.index', '#products'));
    }
}
