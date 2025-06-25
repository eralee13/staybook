<?php
// app/Services/FXService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FXService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.fxkg.url');   // https://data.fx.kg/api/v1
        $this->token   = config('services.fxkg.token');
    }

    /**
     * Получить официальные курсы НБ КР (central).
     *
     * @return array{usd: float, rub: float, kgs: float}
     */
    public function getCentralRates(): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/central")
            ->throw();

        $json = $response->json();
        Log::debug('FX.kg /central raw response', ['body' => $json]);

        // Ожидаем формат: ['usd'=>'82.00','rub'=>'1.10',…]
        return [
            'usd' => isset($json['usd']) && is_numeric($json['usd'])
                ? (float) $json['usd']
                : 0.0,
            'rub' => isset($json['rub']) && is_numeric($json['rub'])
                ? (float) $json['rub']
                : 0.0,
            'kgs' => 1.0,
        ];
    }

    /**
     * Получить официальные курсы НБ, но пересчитанные так,
     * чтобы базой была любая из USD/KGS/RUB.
     *
     * @param  string  $baseCurrency  'USD', 'KGS' или 'RUB'
     * @return array{usd: float, rub: float, kgs: float}
     */
    public function getRatesBaseCentral(string $baseCurrency): array
    {
        $baseCurrency = strtoupper($baseCurrency);
        $r = $this->getCentralRates();
        $usdToKgs = $r['usd'];
        $rubToKgs = $r['rub'];

        return match($baseCurrency) {
            'USD' => [
                'usd' => 1.0,
                'kgs' => round($usdToKgs, 4),
                'rub' => $rubToKgs > 0
                    ? round($usdToKgs / $rubToKgs, 4)
                    : 0.0,
            ],
            'RUB' => [
                'rub' => 1.0,
                'kgs' => round($rubToKgs, 4),
                'usd' => $usdToKgs > 0
                    ? round($rubToKgs / $usdToKgs, 4)
                    : 0.0,
            ],
            default => [
                'kgs' => 1.0,
                'usd' => round($usdToKgs, 4),
                'rub' => round($rubToKgs, 4),
            ],
        };
    }
}
