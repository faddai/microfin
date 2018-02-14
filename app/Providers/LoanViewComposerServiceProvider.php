<?php

namespace App\Providers;

use App\ViewComposers\AuthPagesViewComposer;
use Illuminate\Support\ServiceProvider;

class LoanViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('dashboard.*', AuthPagesViewComposer::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
