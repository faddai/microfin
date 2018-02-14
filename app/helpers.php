<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 06/11/2016
 * Time: 09:57
 */
use App\Entities\Accounting\Ledger;
use App\Entities\Accounting\LedgerEntry;

/**
 * Given a loan amount, return the range the amount falls in
 *
 * @param $amount
 * @return string
 */
if (! function_exists('get_loan_size')) {
    function get_loan_size($amount = null)
    {
        if (null === $amount) {
            throw new \Exception('You must specify a loan Amount');
        }

        return collect(trans('loan_sizes'))->filter(function ($size) use ($amount) {
            if ($amount >= $size[0] && $amount <= $size[1]) {
                return $size;
            }
        })->flatten()->implode('-');
    }
}

/**
 * Given a date of birth, return the age group of this
 *
 * @param $dob
 * @return string
 */
if (! function_exists('get_age_group')) {
    function get_age_group($dob = null)
    {
        if (null === $dob) {
            throw new \Exception('You must specify Date of Birth');
        }

        if (! $dob instanceof \Carbon\Carbon) {
            $dob = \Carbon\Carbon::parse($dob);
        }

        return collect(trans('age_groups'))->filter(function ($group) use ($dob) {
            if ($dob->age >= $group[0] && $dob->age <= $group[1]) {
                return $group;
            }
        })->flatten()->implode('-');

    }
}

if (! function_exists('faker')) {

    /**
     * @return Faker\Generator;
     */
    function faker() : Faker\Generator
    {
        return (new Faker\Factory())->create();
    }
}

if (! function_exists('has_error')) {

    /**
     * Puts an error indicator on form fields failing validation
     *
     * @param string $field
     * @return string
     */
    function has_error($field) : string
    {
        $errors = request()->session()->get('errors') ?: new \Illuminate\Support\MessageBag;

        return $errors->has($field) ? 'has-error' : '';
    }
}

if (! function_exists('num2words')) {

    /**
     * Convert a number to words
     *
     * @param $number
     * @return string
     */
    function num2words($number) : string
    {
        $nf = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);

        $nf->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");

        return $nf->format($number);
    }
}

if (! function_exists('route_with_hash')) {

    /**
     * Append a URL hash to a route
     *
     * @param string $route
     * @param string $hash
     * @param array $params
     * @return string
     */
    function route_with_hash($route, $hash, array $params = []) : string
    {
        return route($route, $params, false). $hash;
    }
}

if (! function_exists('get_running_balance')) {

    /**
     * @param float $balance
     * @param Ledger $ledger
     * @param LedgerEntry $entry
     * @return float
     */
    function get_running_balance(float $balance, Ledger $ledger, LedgerEntry $entry)
    {
        if($ledger->isDebitAccount()) {
            $balance = $entry->isDebit() ? $balance + $entry->dr : $balance - $entry->cr;
        }

        if($ledger->isCreditAccount()) {
            $balance = $entry->isCredit() ? $balance + $entry->cr : $balance - $entry->dr;
        }

        return $balance;
    }
}

if (! function_exists('show_export_links')) {

    /**
     * @param $route
     * @param array $routeParams
     * @return static
     */
    function show_export_links($route, array $routeParams = [])
    {
        return collect(['pdf', 'csv', 'print'])
            ->flatMap(function ($format) use ($route, $routeParams) {

                $routeParams['format'] = $format;

                return [$format => route($route, $routeParams)];
            });
    }
}