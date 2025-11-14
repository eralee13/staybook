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
                    'gel' => $this->parseRate($json, 'gel'),   // ✅ ДОБАВЛЕНО
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

    private function getFallbackRates(): array
    {
        return [
            'usd' => 89.5,
            'rub' => 0.95,
            'uzs' => 0.0072,
            'kzt' => 0.19,
            'cny' => 12.3,
            'gel' => 32.4,   // ✅ ФОЛБЭК ДЛЯ ГРУЗИНСКОГО ЛАРИ (KGS за 1 GEL)
            'kgs' => 1.0,
        ];
    }


    private function parseRate(array $json, string $key): ?float
    {
        return isset($json[$key]) && is_numeric($json[$key]) ? (float) $json[$key] : null;
    }

    private function normalizeKgsPerUnit(array $rates): array
    {
        // ключи → UPPER, значения → float
        $r = [];
        foreach ($rates as $k => $v) {
            $K = strtoupper(trim((string)$k));
            $r[$K] = is_numeric($v) ? (float)$v : 0.0;
        }

        // гарантируем базу
        if (!isset($r['KGS']) || $r['KGS'] <= 0) $r['KGS'] = 1.0;

        // если USD подозрительно < 1 — значит пришло "USD per 1 KGS", инвертируем все кроме KGS
        if (isset($r['USD']) && $r['USD'] > 0 && $r['USD'] < 1) {
            foreach ($r as $ccy => $val) {
                if ($ccy === 'KGS') { $r[$ccy] = 1.0; continue; }
                $r[$ccy] = $val > 0 ? (1 / $val) : 0.0;
            }
        }

        // KGS строго 1.0
        $r['KGS'] = 1.0;
        return $r;
    }




    /**
     * Получить курсы с базой в USD (по умолчанию), пересчитанные в выбранную валюту.
     *
     * @param  string  $baseCurrency
     * @return array<string, float>
     */
    public function getRatesBaseCentral(string $baseCurrency = 'USD'): array
    {
        $kgsPer = $this->normalizeKgsPerUnit($this->getCentralRates());
        $base   = strtoupper(trim($baseCurrency));

        if (!isset($kgsPer[$base]) || $kgsPer[$base] <= 0) {
            $base = 'USD';
        }

        $out = [];
        foreach ($kgsPer as $ccy => $kgsPerOne) {
            if ($ccy === 'KGS') {
                // KGS per 1 BASE
                $out['KGS'] = $kgsPer[$base];
                continue;
            }
            // TARGET per 1 BASE = (KGS/TARGET) / (KGS/BASE)
            $out[$ccy] = $kgsPerOne > 0 ? round($kgsPer[$ccy] / $kgsPer[$base], 6) : 0.0;
        }

        // сам base → 1.0
        $out[$base] = 1.0;

        // гарантируем наличие популярных кодов
        foreach (['USD','RUB','UZS','KZT','CNY','GEL'] as $code) {
            if (!array_key_exists($code, $out)) $out[$code] = 0.0;
        }

        return $out;
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
        $from = strtoupper(trim($from));
        $to   = strtoupper(trim($to));

        // 1) Берём KGS per 1 CCY
        $kgsPer = $this->normalizeKgsPerUnit($this->getCentralRates());

        $kgsPerFrom = $from === 'KGS' ? 1.0 : ($kgsPer[$from] ?? 0.0);
        $kgsPerTo   = $to   === 'KGS' ? 1.0 : ($kgsPer[$to]   ?? 0.0);

        if ($kgsPerFrom <= 0 || $kgsPerTo <= 0) {
            return $amount; // если чего-то нет — вернём как есть
        }

        // 2) FROM → KGS → TO
        $inKgs = $amount * $kgsPerFrom;     // amount[FROM] * (KGS/FROM)
        $out   = $inKgs / $kgsPerTo;        // / (KGS/TO) => amount[TO]

        // банковское округление до 2 знаков достаточно для UI
        return round($out, 2);
    }

}