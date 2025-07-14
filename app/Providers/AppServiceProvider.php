<?php

namespace App\Providers;

use App\Models\BalanceHistory;
use App\Models\Business;
use App\Models\FloatManagement;
use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Models\BusinessDocument;

// Import models and observers
use App\Models\User;
use App\Observers\ModelActivityObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('business', Auth::check() ? Auth::user()->business : null);
        });

         // Register observers
         User::observe(ModelActivityObserver::class);
         Business::observe(ModelActivityObserver::class);
         Transaction::observe(ModelActivityObserver::class);
         PaymentLink::observe(ModelActivityObserver::class);
         BalanceHistory::observe(ModelActivityObserver::class);
         FloatManagement::observe(ModelActivityObserver::class);
         BusinessDocument::observe(ModelActivityObserver::class);
         
    }
}
