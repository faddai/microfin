<?php

namespace App\Http\Controllers;

use App\Entities\Accounting\Ledger;
use App\Entities\Fee;
use App\Entities\LoanProduct;
use App\Entities\Role;
use App\Entities\User;
use App\Entities\Zone;

class SettingsController extends Controller
{
    public function index()
    {
        $zones = cache()->rememberForever('zones', function () {
            return Zone::all();
        });

        $fees = cache()->rememberForever('fees', function () {
            return Fee::with('incomeLedger', 'receivableLedger')->get();
        });

        $users = cache()->rememberForever('users', function () {
            return User::with(['branch', 'roles'])->get();
        });

        $products = cache()->rememberForever('products', function () {
            return LoanProduct::with('principalLedger', 'interestReceivableLedger')->get();
        });

        $roles = cache()->rememberForever('roles', function () {
            return Role::all(['id', 'display_name']);
        });

        $ledgers = cache()->rememberForever('ledgers', function () {
            return Ledger::with('category')->get();
        });

        return view('dashboard.settings.index', compact('zones', 'fees', 'users', 'roles', 'products', 'ledgers'));
    }
}
