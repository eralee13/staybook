<?php

namespace App\ViewComposers;

use App\Services\FXService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class CurrencyComposer
{
    public function __construct(private FXService $fx) {}

    public function compose(View $view): void
    {
        $base = strtoupper((string) Session::get('currency', 'USD'));

        // ✅ Один кеш на "центральные курсы", без зависимости от base
        $central = Cache::remember('fx.central_rates_v1', 3600, function () {
            return $this->fx->getCentralRates(); // если у тебя есть этот метод
        });

        // Если нужно — можешь привести ключи к UPPER здесь
        $rates = is_array($central) ? array_change_key_case($central, CASE_UPPER) : $central;

        $view->with('fxBase', $base)->with('fxRates', $rates);
    }
}