<?php

namespace App\Providers;

use App\Services\CurrencyConversion;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(['auth.layouts.master', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['auth.layouts.booking', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['layouts.master', 'rooms'], 'App\ViewComposers\RoomsComposer');
        View::composer(['layouts.master', 'contacts'], 'App\ViewComposers\ContactsComposer');
        View::composer(['layouts.master', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['layouts.booking', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['layouts.booking', 'rooms'], 'App\ViewComposers\RoomsComposer');
        View::composer(['layouts.booking', 'contacts'], 'App\ViewComposers\ContactsComposer');

    }
}
