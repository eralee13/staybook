<?php

namespace App\Providers;

use App\ViewComposers\CitiesComposer;
use App\ViewComposers\ContactsComposer;
use App\ViewComposers\CurrencyComposer;
use App\ViewComposers\HotelsComposer;
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
        View::composer([
            'pages.search.search',
            'pages.index',
            // добавь только те страницы, где реально нужен список городов
        ], CitiesComposer::class);
        View::composer([
            'layouts.master',
            'layouts.main',
            'auth.layouts.master'
        ], ContactsComposer::class);
        View::composer([
            'auth.layouts.master',
            'auth.layouts.booking',
            'auth.layouts.master'
        ], HotelsComposer::class);
        View::composer([
            'pages.search.*',
            'pages.hotel.*',
            'pages.order.*',
            'layouts.master',
            'layouts.main',
        ], CurrencyComposer::class);

    }
}
