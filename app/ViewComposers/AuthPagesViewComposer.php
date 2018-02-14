<?php

/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 28/10/2016
 * Time: 23:21
 */

namespace App\ViewComposers;

use App\Entities\Branch;
use App\Entities\Country;
use App\Entities\Fee;
use App\Entities\LoanProduct;
use Illuminate\View\View;


class AuthPagesViewComposer
{

    /**
     * Cache common data needed in the view
     *
     * @param View $view
     * @return View
     */
    public function compose(View $view)
    {
        $authUser = auth()->user();

        $branches = cache()->rememberForever('branches', function () {
            return Branch::all();
        });

        $identificationTypes = cache()->rememberForever('identification_types', function () {
            return trans('identification_types');
        });

        $countries = cache()->rememberForever('countries', function () {
            return Country::all();
        });

        cache()->rememberForever('fees', function () {
            return Fee::all();
        });

        $products = cache()->rememberForever('products', function () {
            return LoanProduct::with('principalLedger', 'interestReceivableLedger')->get();
        });

        $currency = config('app.currency');

        return $view->with(compact('authUser', 'branches', 'identificationTypes', 'countries', 'currency', 'products'));
    }

}