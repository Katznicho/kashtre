<?php

namespace App\Providers;


use App\Models\Business;
use App\Models\Transaction;


// Import models and observers
use App\Models\User;
use App\Observers\ModelActivityObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\Items\SimpleItems;
use App\Livewire\Items\CompositeItems;
use App\Livewire\Admin\Admins;



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
        // View::composer('*', function ($view) {
        //     $view->with('business', Auth::check() ? Auth::user()->business : null);
        // });

        View::composer('*', function ($view) {
            $user = Auth::user();
        
            $view->with('business', $user?->business);
            $view->with('permissions', (array) ($user?->permissions ?? []));
        });

         // Register observers
         User::observe(ModelActivityObserver::class);
         Business::observe(ModelActivityObserver::class);
         Transaction::observe(ModelActivityObserver::class);
         
         // Register Livewire components
         Livewire::component('items.simple-items', SimpleItems::class);
         Livewire::component('items.composite-items', CompositeItems::class);
         Livewire::component('admin.admins', Admins::class);
         

    }
}
