<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('logout', 'Auth\LoginController@logout');

Route::get('password/reset/{token}',['as' => 'password.reset', 'uses' => 'Auth\ResetPasswordController@showResetForm']);

// make registration page inaccessible
Route::get('/register', function () {
    abort(404);
});

Route::group(['as' => '', 'middleware' => ['auth']], function () {

    Route::get('/', 'DashboardController@index');
    Route::get('/home', function () { // get around the annoying /home path
        return redirect('/');
    });

    // clients
    Route::group(['prefix' => 'clients', 'as' => 'clients.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'ClientsController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'ClientsController@create']);
        Route::post('store', ['as' => 'store', 'uses' => 'ClientsController@store']);
        Route::get('{client}', ['as' => 'show', 'uses' => 'ClientsController@show']);
        Route::put('{client}', ['as' => 'update', 'uses' => 'ClientsController@update']);
        Route::get('{client}/edit', ['as' => 'edit', 'uses' => 'ClientsController@edit']);
        Route::get('{client}/transactions/download', [
            'as' => 'transactions.download', 'uses' => 'ClientTransactionsController@downloadClientTransactions'
        ]);
    });

    // settings
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'SettingsController@index']);

        // fees
        Route::post('fees/store', ['as' => 'fees.store', 'uses' => 'FeesController@store']);
        Route::put('fees/{fee}', ['as' => 'fees.update', 'uses' => 'FeesController@update']);
        Route::delete('fees/{fee}', ['as' => 'fees.delete', 'uses' => 'FeesController@destroy']);

        // zones
        Route::delete('zones/{zone}', ['as' => 'zones.delete', 'uses' => 'ZonesController@destroy']);
        Route::post('zones/store', ['as' => 'zones.store', 'uses' => 'ZonesController@store']);
        Route::put('zones/{zone}', ['as' => 'zones.update', 'uses' => 'ZonesController@update']);

        // loan products
        Route::delete('products/{product}', ['as' => 'products.delete', 'uses' => 'LoanProductsController@destroy']);
        Route::post('products/store', ['as' => 'products.store', 'uses' => 'LoanProductsController@store']);
        Route::put('products/{product}', ['as' => 'products.update', 'uses' => 'LoanProductsController@update']);
    });

    // loans
    Route::group(['prefix' => 'loans', 'as' => 'loans.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'LoansController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'LoansController@create']);
        Route::get('{loan}/restructure', ['as' => 'restructure', 'uses' => 'LoansController@create']);
        Route::post('store', ['as' => 'store', 'uses' => 'LoansController@store']);
        Route::get('approved', ['as' => 'approved', 'uses' => 'ApproveLoanController@approved']);
        Route::get('disbursed', ['as' => 'disbursed', 'uses' => 'DisburseLoanController@index']);
        Route::get('restructured', ['as' => 'restructured', 'uses' => 'LoanRestructureController@index']);
        Route::get('search', ['as' => 'search', 'uses' => 'LoansController@search']);

        // loan payoff
        Route::group(['prefix' => 'payoff', 'as' => 'payoff.'], function () {
            Route::get('', ['as' => 'index', 'uses' => 'LoanPayoffController@index']);
            Route::post('{payoff}/decline', ['as' => 'decline', 'uses' => 'LoanPayoffController@decline']);
            Route::post('{payoff}/approve', ['as' => 'approve', 'uses' => 'LoanPayoffController@approve']);
        });

        Route::get('{loan}', ['as' => 'show', 'uses' => 'LoansController@show']);
        Route::get('{loan}/schedule/download', ['as' => 'schedule.download', 'uses' => 'LoansController@downloadSchedule']);
        Route::get('{loan}/statement/download', ['as' => 'statement.download', 'uses' => 'LoansController@downloadStatement']);
        Route::put('{loan}/update', ['as' => 'update', 'uses' => 'LoansController@update']);
        Route::get('{loan}/edit', ['as' => 'edit', 'uses' => 'LoansController@edit']);
        Route::post('{loan}/approve', ['as' => 'approve', 'uses' => 'ApproveLoanController@approve']);
        Route::post('{loan}/decline', ['as' => 'decline', 'uses' => 'DeclineLoanController@decline']);
        Route::post('{loan}/disburse', ['as' => 'disburse', 'uses' => 'DisburseLoanController@disburse']);
        Route::get('{loan}/regenerate-schedule', ['as' => 'regenerate.schedule', 'uses' => 'LoansController@regenerateRepaymentSchedule']);
        Route::post('{loan}/payoff/store', ['as' => 'payoff.store', 'uses' => 'LoanPayoffController@store']);
    });

    // users
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::get('{user}/edit', ['as' => 'edit', 'uses' => 'UsersController@edit']);
        Route::put('{user}', ['as' => 'update', 'uses' => 'UsersController@update']);
        Route::post('store', ['as' => 'store', 'uses' => 'UsersController@store']);
        Route::delete('{user}/delete', ['as' => 'delete', 'uses' => 'UsersController@destroy']);
        Route::get('{user}/suspend', ['as' => 'suspend', 'uses' => 'UsersController@suspend']);
    });

    // branches
    Route::group(['prefix' => 'branches', 'as' => 'branches.'], function () {
        Route::post('store', ['as' => 'store', 'uses' => 'BranchesController@store']);
    });

    Route::group(['prefix' => 'client-transactions', 'as' => 'client.transactions.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'ClientTransactionsController@index']);
        Route::post('deposits/store', ['as' => 'deposits.store', 'uses' => 'ClientDepositsController@store']);
        Route::post('withdrawals/store', ['as' => 'withdrawal.store', 'uses' => 'ClientWithdrawalsController@store']);
    });

    // repayments
    Route::group(['prefix' => 'repayments', 'as' => 'repayments.'], function () {
        Route::get('due', ['as' => 'due', 'uses' => 'LoanRepaymentsController@due']);
    });

    // chart of accounts
    Route::group(['prefix' => 'accounting', 'as' => 'accounting.'], function () {
        Route::get('chart', ['as' => 'chart', 'uses' => 'ChartOfAccountsController@index']);

        // ledgers and categories
        Route::group(['prefix' => 'ledgers', 'as' => 'ledgers.'], function () {
            Route::get('', ['as' => 'index', 'uses' => 'LedgersController@index']);
            Route::post('categories/store', ['as' => 'category.store', 'uses' => 'LedgerCategoriesController@store']);
            Route::post('store', ['as' => 'store', 'uses' => 'LedgersController@store']);
            Route::put('{category}/update', ['as' => 'category.update', 'uses' => 'LedgerCategoriesController@update']);
            Route::put('{ledger}', ['as' => 'update', 'uses' => 'LedgersController@update']);
            Route::get('{ledger}', ['as' => 'show', 'uses' => 'LedgersController@show']);
        });

        // transactions
        Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
            Route::group(['prefix' => 'unapproved', 'as' => 'unapproved.'], function () {
                Route::get('', ['as' => 'index', 'uses' => 'UnApprovedTransactionsController@index']);
                Route::post('store', ['as' => 'store', 'uses' => 'UnApprovedTransactionsController@store']);
                Route::get('{transaction}', ['as' => 'show', 'uses' => 'UnApprovedTransactionsController@show']);
                Route::delete('reject', ['as' => 'destroy', 'uses' => 'UnApprovedTransactionsController@destroy']);
            });

            Route::get('', ['as' => 'index', 'uses' => 'LedgerTransactionsController@index']);
            Route::get('create', ['as' => 'create', 'uses' => 'LedgerTransactionsController@create']);
            Route::post('store', ['as' => 'store', 'uses' => 'LedgerTransactionsController@store']);
            Route::get('{transaction}', ['as' => 'show', 'uses' => 'LedgerTransactionsController@show']);
        });

        // trial balances
        Route::group(['prefix' => 'trial-balances', 'as' => 'trial_balance.'], function () {
            Route::get('', ['as' => 'index', 'uses' => 'TrialBalanceController@index']);
            Route::get('download', ['as' => 'download', 'uses' => 'TrialBalanceController@download']);
        });

        // balance sheet
        Route::get('balance-sheet', ['as' => 'balance_sheet', 'uses' => 'BalanceSheetController@index']);
        Route::get('balance-sheet/download', ['as' => 'balance_sheet.download', 'uses' => 'BalanceSheetController@download']);

        // income statement
        Route::get('income-statement', ['as' => 'income_statement', 'uses' => 'IncomeStatementController@index']);
        Route::get('income-statement/download', ['as' => 'income_statement.download', 'uses' => 'IncomeStatementController@download']);
    });

    // reports
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::group(['prefix' => 'loans', 'as' => 'loans.'], function () {
            Route::get('', ['as' => 'index', 'uses' => 'LoanReportsController@index']);
            Route::get('{report}', ['as' => 'show', 'uses' => 'LoanReportsController@show']);
            Route::get('{report}/download', ['as' => 'download', 'uses' => 'LoanReportsController@download']);
        });
    });
});