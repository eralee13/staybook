<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\ActualizeRequest;
use App\Http\Requests\API\V1\SearchRequest;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SearchController extends Controller
{
    /**
     * Поиск отелей
     *
     */
    public function index(SearchRequest $request): JsonResponse
    {
        // базовая фильтрация по локальным атрибутам
        $q = Hotel::query()->where('status', 1);
        if ($request->filled('region')) {
            $q->where('city', $request->get('region'));
        }
        if ($request->filled('rating')) {
            $q->where('rating', '>=', (int)$request->rating);
        }

        $hotels = $q->with(['city', 'images', 'amenities', 'rooms.rates.cancellations'])->get();

        $adult = max(1, (int)$request->input('adult', 1));
        $hasDates = $request->filled('start_d') && $request->filled('end_d');
        $startDate = $hasDates ? Carbon::parse($request->start_d)->toDateString() : null;
        $endDate = $hasDates ? Carbon::parse($request->end_d)->toDateString() : null;

        // childAges
        $childAges = [];
        if ($request->filled('childAges')) {
            $raw = $request->input('childAges');
            $childAges = is_array($raw)
                ? array_values(array_filter($raw, fn($v) => $v !== null && $v !== ''))
                : array_values(array_filter(array_map('trim', explode(',', (string)$raw))));
        }

        $result = [];
        foreach ($hotels as $hotel) {
            $localRoomsUnified = $this->mapLocalRoomsToUnified($hotel->toArray()['rooms'] ?? []);
            $exelyRoomsUnified = [];

            if ($hasDates && $hotel->exely_id) {
                $stays = $this->fetchExelyRoomStays(
                    (string)$hotel->exely_id,
                    $startDate,
                    $endDate,
                    $adult,
                    $childAges
                );
                $exelyRoomsUnified = $this->mapExelyRoomsToUnified($stays);
            }

            $rooms = $this->mergeUnifiedRooms($localRoomsUnified, $exelyRoomsUnified);

            $result[] = [
                'id' => $hotel->id,
                'title' => $hotel->title,
                'city' => $hotel->city,
                'images' => $hotel->images,
                'amenities' => $hotel->amenities,
                'rooms' => $rooms,
            ];
        }

        return response()->json([
            'meta' => [
                'arrivalDate' => $startDate,
                'departureDate' => $endDate,
                'adult' => $adult,
                'childAges' => $childAges,
            ],
            'data' => $result,
        ]);
    }

    private function normalizeAmenities($amenities)
    {
        // может быть строкой "телевизор, Wi-Fi, ..." или коллекцией моделей
        if (is_string($amenities)) {
            return collect(explode(',', $amenities))
                ->map(fn($s) => trim($s))
                ->filter()
                ->values()
                ->all();
        }
        if (is_iterable($amenities)) {
            // если relation -> коллекция моделей с полем title/name
            return collect($amenities)->map(function ($a) {
                return is_array($a) ? ($a['title'] ?? $a['name'] ?? null) : ($a->title ?? $a->name ?? null);
            })->filter()->values()->all();
        }
        return [];
    }

    private function normalizeImages($images)
    {
        // ожидаем relation images: [{url, alt}] или {path}
        return collect($images)->map(function ($img) {
            if (is_array($img)) {
                return [
                    'url' => $img['url'] ?? $img['path'] ?? $img['src'] ?? null,
                    'alt' => $img['alt'] ?? ($img['title'] ?? null),
                ];
            }
            return [
                'url' => $img->url ?? $img->path ?? $img->src ?? null,
                'alt' => $img->alt ?? ($img->title ?? null),
            ];
        })->filter(fn($i) => !empty($i['url']))->values()->all();
    }

    private function mapLocalRoomsToUnified(array $localRooms): array
    {
        return array_map(function ($room) {
            return [
                'code'      => $room['code'] ?? null,
                'title'     => $room['title'] ?? null,
                'occupancy' => $room['occupancy'] ?? null,
                'rates'     => array_map(function ($r) {
                    // пробуем разные поля цены: price (float) либо price_minor (int)
                    $price = $r['price'] ?? (isset($r['price_minor']) ? ((float)$r['price_minor'] / 100.0) : null);
                    return [
                        'title'        => $r['title']        ?? '',
                        'price'        => is_numeric($price) ? (float)$price : null,
                        'currency'     => $r['currency']     ?? null,
                        'meal'         => $r['meal']         ?? null,
                        'availability' => $r['availability'] ?? null,
                        'conditions'   => $r['cancellations'] ?? null,
                    ];
                }, $room['rates'] ?? []),
            ];
        }, $localRooms);
    }

    /** Вернёт базовый URL Exely API (учитывает EXELY_BASE или EXELY_BASE_URL; добавит /api при необходимости) */
    private function exelyApiBase(): string
    {
        $base = (string)(config('services.exely.base') ?? config('services.exely.base_url') ?? '');
        $base = trim($base);
        if ($base === '') {
            $base = 'https://connect.hopenapi.com';
        }
        if (!preg_match('~^https?://~i', $base)) {
            $base = 'https://' . ltrim($base, '/');
        }
        $base = rtrim($base, '/');
        $hasApi = preg_match('~/api/?$~i', $base);

        return $hasApi ? rtrim($base, '/') : $base . '/api';
    }

    /** Тянем room-stays (комнаты+тарифы) из Exely для propertyId (exely_id) и дат */
    private function fetchExelyRoomStays(string $propertyId, string $arrival, string $departure, int $adults = 1, array $childAges = []): array
    {
        $apiBase = $this->exelyApiBase();

        // ---- Сначала GET по одному property ----
        $q = [
            'arrivalDate=' . urlencode($arrival),
            'departureDate=' . urlencode($departure),
            'adults=' . urlencode($adults),
            'includeExtraStays=false',
            'includeExtraServices=false',
        ];
        foreach ($childAges as $age) {
            $q[] = 'childAges=' . urlencode($age);
        }
        $url = $apiBase . '/search/v1/properties/' . $propertyId . '/room-stays?' . implode('&', $q);

        try {
            $resp = Http::timeout(60)
                ->acceptJson()
                ->withHeaders(['x-api-key' => config('services.exely.key')])
                ->get($url);

            Log::debug('Exely GET room-stays', ['url' => $url, 'status' => $resp->status(), 'body' => $resp->body()]);

            if ($resp->successful()) {
                $json = $resp->json();
                $roomStays = data_get($json, 'roomStays', data_get($json, 'data.roomStays', []));
                if (!empty($roomStays)) {
                    Log::debug('Exely GET parsed roomStays sample', [
                        'keys'  => array_keys($roomStays[0] ?? []),
                        'first' => $roomStays[0] ?? null,
                    ]);
                    return collect($roomStays)->sortBy('total')->values()->all();
                }
            }
        } catch (\Throwable $e) {
            Log::error('Exely GET exception', ['url' => $url, 'error' => $e->getMessage()]);
        }

        // ---- Если пусто/ошибка — пробуем bulk POST с тем же propertyId ----
        $bulkUrl = $apiBase . '/search/v1/properties/room-stays/search';
        $payload = [
            'propertyIds'         => [$propertyId],
            'adults'              => $adults,
            'arrivalDate'         => $arrival,
            'departureDate'       => $departure,
            'includeExtraStays'   => false,
            'includeExtraServices'=> false,
        ];
        if (!empty($childAges)) {
            $payload['childAges'] = array_values($childAges);
        }

        try {
            $resp = Http::timeout(60)
                ->acceptJson()
                ->withHeaders(['x-api-key' => config('services.exely.key')])
                ->post($bulkUrl, $payload);

            Log::debug('Exely POST bulk room-stays', ['url' => $bulkUrl, 'status' => $resp->status(), 'body' => $resp->body()]);

            if ($resp->successful()) {
                $json = $resp->json();
                // у bulk-ответа иногда структура другая: например data.results[*].roomStays
                $roomStays = data_get($json, 'roomStays')
                    ?? data_get($json, 'data.roomStays')
                    ?? data_get($json, 'results.0.roomStays')
                    ?? data_get($json, 'data.results.0.roomStays')
                    ?? [];

                Log::debug('Exely POST parsed roomStays sample', [
                    'keys'  => array_keys($roomStays[0] ?? []),
                    'first' => $roomStays[0] ?? null,
                ]);

                return collect($roomStays)->sortBy('total')->values()->all();
            }
        } catch (\Throwable $e) {
            Log::error('Exely POST exception', ['url' => $bulkUrl, 'error' => $e->getMessage()]);
        }

        // Совсем пусто
        return [];
    }
    /** Преобразуем массив room-stays Exely → в «наши» комнаты с тарифами, агрегация по комнате */
    /**
     * Приведение структуры Exely room-stays к унифицированному виду.
     * Возвращает массив rooms с тарифами (rates[]), внутри которых meta с полями:
     * propertyId, roomTypeId, ratePlanId, checksum, placements.
     */
    private function mapExelyRoomsToUnified(array $roomStays): array
    {
        if (empty($roomStays)) {
            return [];
        }

        $roomsByKey = [];

        foreach ($roomStays as $stay) {
            $roomTypeId = (string) data_get($stay, 'roomType.id');
            $roomCode   = $roomTypeId ?: md5(json_encode($stay));
            $key        = 'room:' . $roomCode;

            // если комнаты с таким id еще нет — создаем
            if (!isset($roomsByKey[$key])) {
                $roomsByKey[$key] = [
                    'code'      => $roomTypeId,
                    'title'     => 'Room ' . $roomTypeId,
                    'occupancy' => data_get($stay, 'roomType.placements'),
                    'rates'     => [],
                ];
            }

            // базовая информация по stay
            $propertyId = data_get($stay, 'propertyId');
            $ratePlanId = data_get($stay, 'ratePlan.id');
            $checksum   = data_get($stay, 'checksum');
            $placements = data_get($stay, 'roomType.placements');
            $availability = data_get($stay, 'availability');

            // цена и валюта
            $price    = data_get($stay, 'total.priceBeforeTax');
            $currency = data_get($stay, 'currencyCode');

            // добавляем тариф (rate)
            $roomsByKey[$key]['rates'][] = [
                'title'        => data_get($stay, 'ratePlan.name', 'Rate Plan ' . $ratePlanId),
                'price'        => is_numeric($price) ? (float)$price : null,
                'currency'     => $currency,
                'availability' => $availability,
                'meal'         => data_get($stay, 'mealPlanCode', optional(data_get($stay, 'includedServices.0'))['mealPlanCode'] ?? null),
                'conditions'   => data_get($stay, 'cancellationPolicy'),
                'meta' => [
                    'propertyId'  => $propertyId,
                    'roomTypeId'  => $roomTypeId,
                    'ratePlanId'  => $ratePlanId,
                    'checksum'    => $checksum,
                    'placements'  => $placements,
                ],
            ];
        }

        // убираем дубли тарифов (по названию и цене)
        foreach ($roomsByKey as &$room) {
            $uniqueRates = [];
            foreach ($room['rates'] as $r) {
                $key = strtolower($r['title'] ?? '') . '|' . (string)($r['price'] ?? '');
                $uniqueRates[$key] = $r;
            }
            $room['rates'] = array_values($uniqueRates);
        }

        return array_values($roomsByKey);
    }

    /** Слияние: если комната совпадает (по code, либо по normalized title) — объединяем тарифы */
    private function mergeUnifiedRooms(array $localRooms, array $exelyRooms): array
    {
        $keyFor = function ($room) {
            return $room['code']
                ? 'code:' . mb_strtolower(trim((string)$room['code']))
                : 'title:' . mb_strtolower(preg_replace('~\s+~u', ' ', trim((string)($room['title'] ?? ''))));
        };

        $map = [];
        foreach ($localRooms as $r) {
            $map[$keyFor($r)] = $r;
        }
        foreach ($exelyRooms as $r) {
            $k = $keyFor($r);
            if (isset($map[$k])) {
                $map[$k]['rates'] = array_values(array_merge($map[$k]['rates'] ?? [], $r['rates'] ?? []));
            } else {
                $map[$k] = $r;
            }
        }
        return array_values($map);
    }


    /**
     * Поиск конкретного отеля
     *
     */
    public function show(int $id, SearchRequest $request): JsonResponse
    {
        $adult     = max(1, (int) $request->input('adult', 1));
        $hasDates  = $request->filled('arrivalDate') && $request->filled('departureDate');
        $startDate = $hasDates ? \Carbon\Carbon::parse($request->arrivalDate)->toDateString() : null;
        $endDate   = $hasDates ? \Carbon\Carbon::parse($request->departureDate)->toDateString() : null;

        $hotel = \App\Models\Hotel::query()
            ->whereKey($id)->where('status', 1)
            ->with(['city','images','amenities','rooms.rates.cancellations'])
            ->first();

        if (!$hotel) {
            return response()->json(['error' => ['code' => 'not_found', 'message' => 'Отель не найден']], 404);
        }

        // childAges
        $childAges = [];
        if ($request->filled('childAges')) {
            $raw = $request->input('childAges');
            $childAges = is_array($raw)
                ? array_values(array_filter($raw, fn($v)=>$v!==null && $v!==''))
                : array_values(array_filter(array_map('trim', explode(',', (string)$raw))));
        } else {
            $childAges = collect([$request->age1,$request->age2,$request->age3])->filter()->values()->all();
        }

        // локальные → единый формат (на случай объединения)
        $localRoomsUnified = $this->mapLocalRoomsToUnified($hotel->toArray()['rooms'] ?? []);

        // exely → единый формат
        $exelyRoomsUnified = [];
        if ($hotel->exely_id && $hasDates) {
            $stays = $this->fetchExelyRoomStays((string)$hotel->exely_id, $startDate, $endDate, $adult, $childAges);
            $exelyRoomsUnified = $this->mapExelyRoomsToUnified($stays);
        }

        // если не хочешь мерджить — можно вернуть только Exely:
        // $rooms = $exelyRoomsUnified;
        $rooms = $this->mergeUnifiedRooms($localRoomsUnified, $exelyRoomsUnified);

        return response()->json([
            'id'          => $hotel->id,
            'title'       => $hotel->title,
            'city'        => $hotel->city,
            'description' => $hotel->description ?? $hotel->desc ?? null,
            'amenities'   => $this->normalizeAmenities($hotel->amenities),
            'images'      => $this->normalizeImages($hotel->images),
            'rooms'       => $rooms,
        ]);
    }

    /**
     * Актуализация доступности тарифов.
     *
     * Пересчитывает доступность тарифов на заданный период, возвращает или сохраняет результат.
     *
     * @group Availability
     * @operationId availability.actualize
     * @tag Availability
     *
     * @bodyParam start_d date required Начало периода. Example: 2025-10-20
     * @bodyParam end_d date required Конец периода. Example: 2025-10-25
     * @bodyParam hotel_id integer nullable ID отеля. Example: 12
     * @bodyParam persist boolean nullable true — записать изменения в БД. Example: false
     *
     * @response 200 scenario="Успешно"
     * {
     *   "meta": {
     *     "start_d": "2025-10-20",
     *     "end_d": "2025-10-25",
     *     "persist": false
     *   },
     *   "data": []
     * }
     */
    public function actualize(ActualizeRequest $request): JsonResponse
    {
        $start   = $request->start_d;
        $end     = $request->end_d;
        $adult   = $request->adult ?? null;
        $persist = $request->persist ?? false;

        $hotels = Hotel::query()
            ->when($request->filled('hotel_id'), fn($q) => $q->where('id', $request->hotel_id))
            ->where('status', 1)
            ->with(['rooms.rates.cancellations'])
            ->get();

        return response()->json([
            'meta' => [
                'start_d' => $start,
                'end_d'   => $end,
                'persist' => $persist,
            ],
            'data' => $hotels,
        ]);
    }
}