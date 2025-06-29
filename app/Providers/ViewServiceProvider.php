<?php

namespace App\Providers;

use App\ViewComposers\CurrencyComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register amenities.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap amenities.
     */
    public function boot(): void
    {
        View::composer(['auth.layouts.master', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['auth.layouts.booking', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['layouts.master', 'rooms'], 'App\ViewComposers\RoomsComposer');
        View::composer(['layouts.master', 'cities'], 'App\ViewComposers\CitiesComposer');
        View::composer(['layouts.master', 'contacts'], 'App\ViewComposers\ContactsComposer');
        View::composer(['layouts.filter_mini', 'contacts'], 'App\ViewComposers\ContactsComposer');
        View::composer(['layouts.master', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['layouts.booking', 'hotels'], 'App\ViewComposers\HotelsComposer');
        View::composer(['layouts.booking', 'rooms'], 'App\ViewComposers\RoomsComposer');
        View::composer(['layouts.booking', 'contacts'], 'App\ViewComposers\ContactsComposer');

        View::composer(
            '*',
            CurrencyComposer::class
        );

    }
}
