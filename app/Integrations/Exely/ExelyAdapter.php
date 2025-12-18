<?php

namespace App\Integrations\Exely;

use App\Integrations\Contracts\ChannelAdapterInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class ExelyAdapter implements ChannelAdapterInterface
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $config = config('integrations.exely');

        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey  = $config['api_key'] ?? null;
        $this->timeout = $config['timeout'] ?? 10;
    }

    /**
     * Базовый HTTP-клиент с нужными заголовками.
     */
    protected function client()
    {
        return Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept'        => 'application/json',
            ]);
    }

    /**
     * Поиск отелей.
     */
    public function searchHotels(array $criteria = []): array
    {
        $cacheKey = $this->buildCacheKey('hotels_search', $criteria);
        $ttl      = now()->addMinutes(config('integrations.exely.cache_ttl.hotels'));

        return Cache::remember($cacheKey, $ttl, function () use ($criteria) {
            try {
                // TODO: подставить реальные эндпоинты и параметры из доки Exely
                $response = $this->client()
                    ->get("{$this->baseUrl}/hotels/search", $criteria)
                    ->throw();

                $rawData = $response->json();

                return $this->normalizeHotels($rawData);
            } catch (RequestException $e) {
                // тут можно логировать детальнее
                report($e);

                return [];
            }
        });
    }

    /**
     * Получение одного отеля по ID.
     */
    public function getHotel(string $externalId): ?array
    {
        $cacheKey = $this->buildCacheKey('hotel', ['id' => $externalId]);
        $ttl      = now()->addMinutes(config('integrations.exely.cache_ttl.hotels'));

        return Cache::remember($cacheKey, $ttl, function () use ($externalId) {
            try {
                $response = $this->client()
                    ->get("{$this->baseUrl}/hotels/{$externalId}")
                    ->throw();

                $data = $response->json();

                return $this->normalizeHotel($data);
            } catch (RequestException $e) {
                report($e);

                return null;
            }
        });
    }

    /**
     * Доступность.
     */
    public function getAvailability(string $externalId, Carbon $from, Carbon $to): array
    {
        $params = [
            'hotel_id' => $externalId,
            'date_from' => $from->toDateString(),
            'date_to'   => $to->toDateString(),
        ];

        $cacheKey = $this->buildCacheKey('availability', $params);
        $ttl      = now()->addMinutes(config('integrations.exely.cache_ttl.availability'));

        return Cache::remember($cacheKey, $ttl, function () use ($params) {
            try {
                $response = $this->client()
                    ->get("{$this->baseUrl}/availability", $params)
                    ->throw();

                $rawData = $response->json();

                return $this->normalizeAvailability($rawData);
            } catch (RequestException $e) {
                report($e);

                return [];
            }
        });
    }

    /**
     * Тарифы / цены.
     */
    public function getRates(string $externalId, Carbon $from, Carbon $to): array
    {
        $params = [
            'hotel_id' => $externalId,
            'date_from' => $from->toDateString(),
            'date_to'   => $to->toDateString(),
        ];

        $cacheKey = $this->buildCacheKey('rates', $params);
        $ttl      = now()->addMinutes(config('integrations.exely.cache_ttl.rates'));

        return Cache::remember($cacheKey, $ttl, function () use ($params) {
            try {
                $response = $this->client()
                    ->get("{$this->baseUrl}/rates", $params)
                    ->throw();

                $rawData = $response->json();

                return $this->normalizeRates($rawData);
            } catch (RequestException $e) {
                report($e);

                return [];
            }
        });
    }

    /**
     * Импорт бронирований.
     */
    public function pullReservations(Carbon $from, Carbon $to): array
    {
        $params = [
            'from' => $from->toIso8601String(),
            'to'   => $to->toIso8601String(),
        ];

        $cacheKey = $this->buildCacheKey('reservations', $params);
        $ttl      = now()->addMinutes(config('integrations.exely.cache_ttl.reservations'));

        return Cache::remember($cacheKey, $ttl, function () use ($params) {
            try {
                $response = $this->client()
                    ->get("{$this->baseUrl}/reservations", $params)
                    ->throw();

                $rawData = $response->json();

                return $this->normalizeReservations($rawData);
            } catch (RequestException $e) {
                report($e);

                return [];
            }
        });
    }

    /**
     * Нормализация списка отелей в единый формат Staybook.
     */
    protected function normalizeHotels(array $data): array
    {
        // Предположим, Exely вернёт массив вида ['hotels' => [ ... ]]
        $items = $data['hotels'] ?? $data;

        return collect($items)->map(fn(array $item) => $this->normalizeHotel($item))->all();
    }

    /**
     * Нормализация одного отеля.
     */
    protected function normalizeHotel(array $item): array
    {
        return [
            'provider'      => 'exely',
            'external_id'   => $item['hotel_id'] ?? $item['id'] ?? null,
            'title'         => $item['hotelName'] ?? $item['name'] ?? null,
            'description'   => $item['description'] ?? null,
            'address'       => $item['location'] ?? $item['address'] ?? null,
            'city'          => $item['city'] ?? null,
            'country'       => $item['country'] ?? null,
            'latitude'      => $item['lat'] ?? null,
            'longitude'     => $item['lng'] ?? null,
            'rating'        => $item['rating'] ?? null,
            'images'        => $item['images'] ?? [],
            'amenities'     => $item['amenities'] ?? [],
            // любые другие поля, которые ты используешь в Staybook
        ];
    }

    protected function normalizeAvailability(array $data): array
    {
        // TODO: подстроить под реальный ответ Exely
        // Пример ожидаемого единого формата:
        // [
        //   [
        //     'date' => '2025-01-01',
        //     'room_type' => 'standard',
        //     'available' => 3,
        //   ],
        // ]
        return $data;
    }

    protected function normalizeRates(array $data): array
    {
        // TODO: подстроить под реальный ответ Exely
        // Единый формат:
        // [
        //   [
        //     'date'      => '2025-01-01',
        //     'room_type' => 'standard',
        //     'rate_code' => 'BAR',
        //     'price'     => 100.00,
        //     'currency'  => 'EUR',
        //   ],
        // ]
        return $data;
    }

    protected function normalizeReservations(array $data): array
    {
        // TODO: подстроить под реальный ответ Exely
        // Единый формат:
        // [
        //   [
        //     'external_id' => 'abc123',
        //     'hotel_id'    => 'h1',
        //     'check_in'    => '2025-01-01',
        //     'check_out'   => '2025-01-05',
        //     'guest_name'  => 'John Doe',
        //     'status'      => 'confirmed',
        //     'total_price' => 500.00,
        //     ...
        //   ],
        // ]
        return $data;
    }

    /**
     * Формирование ключа кеша с учетом параметров.
     */
    protected function buildCacheKey(string $prefix, array $params = []): string
    {
        if (empty($params)) {
            return "exely:{$prefix}";
        }

        ksort($params);

        return 'exely:' . $prefix . ':' . md5(json_encode($params));
    }
}