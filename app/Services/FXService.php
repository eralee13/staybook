<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
     * Получить официальные курсы НБ КР (central), с кэшированием.
     *
     * @return array{usd: float, rub: float, uzs: float|null, kgs: float}
     */
    public function getCentralRates(): array
    {
        return Cache::remember('fx_central_rates', now()->addMinutes(60), function () {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/central")
                ->throw();

            $json = $response->json();
            Log::debug('FX.kg /central raw response', ['body' => $json]);

            return [
                'usd' => isset($json['usd']) && is_numeric($json['usd']) ? (float) $json['usd'] : 0.0,
                'rub' => isset($json['rub']) && is_numeric($json['rub']) ? (float) $json['rub'] : 0.0,
                'uzs' => isset($json['uzs']) && is_numeric($json['uzs']) ? (float) $json['uzs'] : null,
                'kgs' => 1.0,
            ];
        });
    }

    /**
     * Получить курсы с базой в USD (по умолчанию), пересчитанные в выбранную валюту.
     *
     * @param  string  $baseCurrency
     * @return array<string, float>
     */
    public function getRatesBaseCentral(string $baseCurrency = 'USD'): array
    {
        $rates = $this->getCentralRates();

        $usd = $rates['usd'] ?? 0;
        $rub = $rates['rub'] ?? 0;
        $uzs = $rates['uzs'] ?? null;

        return match (strtoupper($baseCurrency)) {
            'USD' => [
                'USD' => 1.0,
                'KGS' => round($usd, 4),
                'RUB' => $rub > 0 ? round($usd / $rub, 4) : 0.0,
                'UZS' => $uzs > 0 ? round($usd / $uzs, 4) : 0.0,
            ],
            'RUB' => [
                'USD' => $usd > 0 ? round($rub / $usd, 4) : 0.0,
                'KGS' => round($rub, 4),
                'RUB' => 1.0,
                'UZS' => $uzs > 0 ? round($rub / $uzs, 4) : 0.0,
            ],
            'UZS' => [
                'USD' => $usd > 0 && $uzs > 0 ? round($uzs / $usd, 4) : 0.0,
                'KGS' => $uzs > 0 ? round($uzs, 4) : 0.0,
                'RUB' => $rub > 0 && $uzs > 0 ? round($uzs / $rub, 4) : 0.0,
                'UZS' => 1.0,
            ],
            default => [
                'USD' => $usd > 0 ? round(1 / $usd, 4) : 0.0,
                'RUB' => $rub > 0 ? round(1 / $rub, 4) : 0.0,
                'UZS' => $uzs > 0 ? round(1 / $uzs, 4) : 0.0,
                'KGS' => 1.0,
            ],
        };
    }

    /**
     * Конвертация суммы из одной валюты в другую через USD как базу.
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     * @return float
     */
    public function convert(float $amount, string $from, string $to): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        $rates = $this->getRatesBaseCentral('USD');

        $rateFrom = $rates[$from] ?? null;
        $rateTo = $rates[$to] ?? null;

        if (!$rateFrom || !$rateTo || $rateFrom <= 0) {
            return $amount;
        }

        $amountInUsd = $from !== 'USD' ? $amount / $rateFrom : $amount;
        return round($to !== 'USD' ? $amountInUsd * $rateTo : $amountInUsd, 2);
    }
}