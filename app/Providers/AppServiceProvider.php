<?php

namespace App\Providers;

use App\Models\MasterDistributor;
use App\Observers\MasterDistributorObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        // URL::forceScheme('https');

        // Register MasterDistributor observer to auto-create users
        MasterDistributor::observe(MasterDistributorObserver::class);
    }
}
