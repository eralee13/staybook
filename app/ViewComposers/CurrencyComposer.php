<?php

namespace App\ViewComposers;

use App\Services\FXService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class CurrencyComposer
{
    protected FXService $fx;

    public function __construct(FXService $fx)
    {
        $this->fx = $fx;
    }

    public function compose(View $view): void
    {
        $base = Session::get('currency', 'USD');
        $rates = Cache::remember("fx.rates_{$base}", 3600, fn() =>
        $this->fx->getRatesBaseCentral($base)
        );



        $view->with('fxBase', $base)
            ->with('fxRates', $rates);
    }
}