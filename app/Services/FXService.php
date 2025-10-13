<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FXService
{
    protected string $baseUrl;
    protected string $token;
    protected string $base;

    public function __construct()
    {
        $this->baseUrl = config('services.fxkg.url');   // https://data.fx.kg/api/v1
        $this->token   = config('services.fxkg.token');
        $this->base = 'KGS';
    }

    /**
     * Получить официальные курсы НБ КР (central), с кэшированием.
     *
     * @return array{usd: float, rub: float, uzs: float|null, kgs: float, kzt: float}
     */
    public function getCentralRates(): array
    {
        $cacheKey = 'fx_central_rates';
        $cacheValue = Cache::get($cacheKey);
        if ( empty($cacheValue['usd']) ){ Cache::forget($cacheKey); }

        return Cache::remember($cacheKey, now()->addHours(12), function () {
            try {
                $response = Http::withToken($this->token)
                    ->get("{$this->baseUrl}/central");

                if (!$response->successful()) {
                    Log::warning('FX.kg ответ неуспешен', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return $this->getFallbackRates();
                }

                $json = $response->json();
                
                Log::debug('FX.kg /central full json', $json);
                
                return [
                    'usd' => $this->parseRate($json, 'usd'),
                    'rub' => $this->parseRate($json, 'rub'),
                    'uzs' => $this->parseRate($json, 'uzs'),
                    'kzt' => $this->parseRate($json, 'kzt'),
                    'cny' => $this->parseRate($json, 'cny'),
                    'kgs' => 1.0,
                ];
            } catch (\Throwable $e) {
                Log::error('Ошибка получения курсов FX.kg', [
                    'error' => $e->getMessage(),
                ]);
                
                return $this->getFallbackRates();
            }
        });
    }

    private function parseRate(array $json, string $key): ?float
    {
        return isset($json[$key]) && is_numeric($json[$key]) ? (float) $json[$key] : null;
    }


    private function getFallbackRates(): array
    {
        return [
            'usd' => 89.5,
            'rub' => 0.95,
            'uzs' => 0.0072,
            'kzt' => 0.19,
            'cny' => 12.3,
            'kgs' => 1.0,
        ];
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
        // dd( Cache::get('fx_central_rates') );
        $usd = $rates['usd'] ?? 0;
        $rub = $rates['rub'] ?? 0;
        $uzs = $rates['uzs'] ?? null;
        $kzt = $rates['kzt'] ?? null;
        $cny = $rates['cny'] ?? null;
        // dump($rates);
        return match (strtoupper($baseCurrency)) {
            'USD' => [
                'USD' => 1.0,
                'KGS' => round($usd, 4),
                'RUB' => $rub > 0 ? round($usd / $rub, 4) : 0.0,
                'UZS' => $uzs > 0 ? round($usd / $uzs, 4) : 0.0,
                'KZT' => $kzt > 0 ? round($usd / $kzt, 4) : 0.0,
            ],
            'RUB' => [
                'USD' => $usd > 0 ? round($usd, 4) : 0.0,
                'KGS' => round($rub, 4),
                'RUB' => 1.0,
                'UZS' => $uzs > 0 ? round($uzs, 4) : 0.0,
                'KZT' => $kzt > 0 ? round($kzt, 4) : 0.0,
            ],
            'UZS' => [
                'USD' => $usd > 0 && $uzs > 0 ? round($uzs / $usd, 4) : 0.0,
                'KGS' => $uzs > 0 ? round($uzs, 4) : 0.0,
                'RUB' => $rub > 0 && $uzs > 0 ? round($uzs / $rub, 4) : 0.0,
                'KZT' => $kzt > 0 && $uzs > 0 ? round($uzs / $kzt, 4) : 0.0,
                'UZS' => 1.0,
            ],
            'KZT' => [
                'USD' => $usd > 0 && $kzt > 0 ? round($kzt / $usd, 4) : 0.0,
                'KGS' => $kzt > 0 ? round($kzt, 4) : 0.0,
                'RUB' => $rub > 0 && $kzt > 0 ? round($kzt / $rub, 4) : 0.0,
                'UZS' => $uzs > 0 && $kzt > 0 ? round($kzt / $uzs, 4) : 0.0,
                'KZT' => 1.0,
            ],
            'CNY' => [
                'USD' => $usd > 0 ? round($usd, 4) : 0.0,
                'KGS' => 1.0,
                'RUB' => $rub > 0 && $cny > 0 ? round($rub, 4) : 0.0,
                'UZS' => $uzs > 0 && $cny > 0 ? round($uzs, 4) : 0.0,
                'CNY' => $cny > 0 ? round($cny, 4) : 0.0,
            ],
            default => [
                'USD' => $usd > 0 ? round(1 / $usd, 4) : 0.0,
                'RUB' => $rub > 0 ? round(1 / $rub, 4) : 0.0,
                'UZS' => $uzs > 0 ? round(1 / $uzs, 4) : 0.0,
                'KZT' => $kzt > 0 ? round(1 / $kzt, 4) : 0.0,
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
        $rates = $this->getRatesBaseCentral($from);
        // dump($rates);
        $rateFrom = $rates[$from] ?? null;
        $rateTo = $rates[$to] ?? null;
        // dump($rateFrom, $rateTo);

        if ($from == 'CNY'){
            if (!$rateFrom || !$rateTo || $rateFrom <= 0 || $rateTo <= 0) {
                return $amount;
            }

            // FROM → BASE
            $amountInBase = $from === $this->base ? $amount : $amount * $rateFrom;

            // BASE → TO
            $converted = $to === $this->base ? $amountInBase : $amountInBase / $rateTo;

            return round($converted, 0);

        }elseif($from == 'RUB'){

            if (!$rateFrom || !$rateTo || $rateFrom <= 0) {
                return $amount;
            }

            // Из рублей в другую валюту
            $converted = $amount / $rateTo;
            
            if($to == 'KGS'){
                $converted = $amount * $rateTo;
            }

            return round($converted, 2);

        }else{

            if (!$rateFrom || !$rateTo || $rateFrom <= 0) {
                return $amount;
            }

            $amountInUsd = $from !== 'USD' ? ($amount / $rateFrom) : $amount;
            return round($to !== 'USD' ? $amountInUsd * $rateTo : $amountInUsd, 2);
        }
        
    }
}