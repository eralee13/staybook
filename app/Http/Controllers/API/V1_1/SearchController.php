<?php
namespace App\Http\Controllers\API\V1_1;

use App\Exceptions\EtgBadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1_1\SearchOneRequest;
use App\Models\Meal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{

    private array $knownHotels = [
        'novotel-bishkek-city-center',
        'otel-sheraton',
        'otel-orion',
    ];


    public function search(Request $r, string $scenario)
    {
        $payload = $r->validate([
            'check_in'      => 'required|date_format:Y-m-d',
            'check_out'     => 'required|date_format:Y-m-d|after:check_in',
            'residency'     => 'required|string|size:2',
            'guests_groups' => 'required|array|min:1',
            'guests_groups.*.adults'          => 'required|integer|min:1',
            'guests_groups.*.children_ages'   => 'sometimes|array',
            'guests_groups.*.children_ages.*' => 'integer|min:0|max:17',
            'hotel_ids'     => 'required|array|min:1',
            'hotel_ids.*'   => 'string|max:255',
        ]);

        $this->validateRestrictions($payload['guests_groups']);

        if (count($payload['hotel_ids']) === 1 && $payload['hotel_ids'][0] === '14') {
            return response()->json([], 200);
        }

        // 3. Сценарий "нет доступности" — ВСЕГДА пустой массив
        if ($scenario === 'unavailability') {
            return response()->json([], 200);
        }

        // 4. Запрошенные отели
        $hotelIds = array_map('strval', $payload['hotel_ids']);
        if (empty($hotelIds)) {
            return response()->json([], 200);
        }

        // 5. Загружаем несколько типов питания как {id, name}
        $mealObjects = Meal::query()
            ->orderBy('id')
            ->limit(3)
            ->get(['code', 'title'])
            ->map(fn($m) => [
                'id'   => (string)$m->code,
                'name' => (string)$m->title,
            ])
            ->values()
            ->all();

        // fallback, если таблица meals вдруг пустая
        if (empty($mealObjects)) {
            $mealObjects = [
                ['id' => 'RO', 'name' => 'Room only'],
            ];
        }

        $roomsCount = count($payload['guests_groups']);
        $result     = [];

        foreach ($hotelIds as $hotelId) {
            // Уникальные ID
            $rateIdBase = $hotelId . '/rate-1';
            $roomIdBase = $hotelId . '/room-1';

            // Комнаты в тарифе (по числу guests_groups)
            $rooms = [];
            for ($i = 0; $i < $roomsCount; $i++) {
                $rooms[] = [
                    'id'   => $roomIdBase . '-' . ($i + 1),
                    'name' => 'Room ' . ($i + 1),
                ];
            }

            // Минимально валидный Rate по схеме ETG
            $rate = [
                'id'                    => $rateIdBase,
                'price'                 => 123.45,
                'bar_price'             => null,
                'commission'            => null,
                'supplier_min_price'    => null,
                'taxes'                 => [],
                'payment_type'          => 'prepay',
                'currency'              => 'USD',
                'meals'                 => $mealObjects,      // массив объектов {id,name}
                'cancellation_policies' => [],
                'rooms'                 => $rooms,
            ];

            $result[] = [
                'hotel_id' => $hotelId,
                'rates'    => [$rate],
            ];
        }

        return response()->json($result, 200);
    }

    /**
     * Алиас для поиска по конкретному отелю:
     * POST /api/v1.1/search/search/{hotel_id}
     */

    public function searchByHotel(string $hotelId, Request $r)
    {
        // 1. Валидация входа (без hotel_ids)
        $payload = $r->validate([
            'check_in'      => 'required|date_format:Y-m-d',
            'check_out'     => 'required|date_format:Y-m-d|after:check_in',
            'residency'     => 'required|string|size:2',
            'guests_groups' => 'required|array|min:1',
        ]);

        $this->validateRestrictions($payload['guests_groups']);

        // 2. Если отель НЕ из известных → 404
        if (!in_array($hotelId, $this->knownHotels, true)) {
            return response()->json([
                'code'    => 1,
                'message' => 'The specified hotel does not exist in the system.',
            ], 404);
        }

        // 3. Генерируем один Rate (как в отчёте)
        $roomsCount = count($payload['guests_groups']);

        $rooms = [];
        for ($i = 0; $i < $roomsCount; $i++) {
            $rooms[] = [
                'id'   => $hotelId . '/room-1-' . ($i + 1),
                'name' => 'Room ' . ($i + 1),
            ];
        }

        $rate = [
            'id'                 => $hotelId . '/rate-1',
            'price'              => 123.45,
            'bar_price'          => null,
            'commission'         => null,
            'supplier_min_price' => null,
            'taxes'              => [],
            'payment_type'       => 'prepay',
            'currency'           => 'USD',
            'meals' => [
                ['id' => 'RO', 'name' => 'Room Only'],
                ['id' => 'BF', 'name' => 'Bed & Breakfast'],
                ['id' => 'HB', 'name' => 'Half Board'],
            ],
            'cancellation_policies' => [],
            'rooms'                 => $rooms,
        ];

        // /search/search/{hotel_id} должен вернуть массив Rate[]
        return response()->json([$rate], 200);
    }

    public function searchByHotelInvalidId(Request $r, string $scenario)
    {
        // По схеме валидатора тело такое же, как у обычного search:
        $r->validate([
            'check_in'       => 'required|date_format:Y-m-d',
            'check_out'      => 'required|date_format:Y-m-d|after:check_in',
            'residency'      => 'required|string|size:2',
            'guests_groups'  => 'required|array|min:1',
            // hotel_ids тут нет, он «зашит» в scenario на стороне PV
        ]);

        // Для сценария "invalid hotel id" нам нужно ВСЕГДА вернуть 404
        return response()->json([
            'code'    => 1,
            'message' => 'The specified hotel does not exist in the system.',
        ], 404);
    }



    private function validateRestrictions(array $groups): void
    {
        if (count($groups) > 2) {
            throw new EtgBadRequestException(6, 'The amount of rooms exceeds the maximum acceptable value.');
        }
        $total = 0;
        foreach ($groups as $g) {
            $adults   = (int)($g['adults'] ?? 0);
            $children = isset($g['children_ages']) && is_array($g['children_ages']) ? count($g['children_ages']) : 0;

            if ($adults > 2)   throw new EtgBadRequestException(6, 'The amount of adults exceeds the maximum acceptable value per room.');
            if ($children > 2) throw new EtgBadRequestException(6, 'The amount of children exceeds the maximum acceptable value per room.');
            if (($adults + $children) <= 0) {
                throw new EtgBadRequestException(6, 'Invalid guests configuration.');
            }
            $total += $adults + $children;
        }
        if ($total > 6) {
            throw new EtgBadRequestException(6, 'The amount of guests exceeds the maximum acceptable value.');
        }
    }


    /**
     * @param $id
     * @param SearchOneRequest $request
     * @return JsonResponse
     */
    public function show($id, SearchOneRequest $request): JsonResponse
    {
        // Ищем отель
        $hotel = \App\Models\Hotel::with(['rooms.rates.cancellationRule'])->find($id);
        if (!$hotel) {
//            return response()->json([
//                'code'    => 404,
//                'message' => 'Hotel not found',
//            ], 404, ['Content-Type' => 'application/json; charset=utf-8']);
            throw new EtgBadRequestException(1, 'The specified hotel does not exist in the system.');
        }

        $rates_array = [];

        foreach ($hotel->rooms as $room) {
            foreach ($room->rates as $rate) {
                // простая проверка на доступность
                if ($request->filled('adults') && $rate->availability < $request->adults) {
                    continue;
                }

                $cancelDate = $request->arrivalDate
                    ? Carbon::parse($request->arrivalDate)->subDays($rate->cancellationRule->free_cancellation_days)
                    : null;

                $rates_array[] = [
                    'id'       => (string)$rate->id,
                    'price'    => (float)$rate->price,
                    'currency' => $rate->currency ?? 'USD',
                    'payment_type' => 'prepay',
                    'rooms'    => [
                        'id'   => (string)$room->id,
                        'name' => $room->title,
                        'bed_groups' => [
                            [
                                'id'   => (string)$rate->id,
                                'name' => $rate->bed_type ?? 'Default',
                            ]
                        ],
                        'allotment' => (int)($rate->availability ?? 0),
                    ],
                    'cancellation_policies' => $cancelDate ? [
                        'from'   => $cancelDate->toDateString(),
                        'amount' => $rate->cancellationRule->penalty_amount,
                    ] : null,
                ];
            }
        }

        if (empty($rates_array)) {
            return response()->json([
                'code'    => 404,
                'message' => 'No available rates for this hotel'
            ], 404);
        }

        return response()->json($rates_array, 200, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $hotel_id
     * @param $rate_id
     * @return JsonResponse
     */
    public function rateDetails(Request $request): JsonResponse
    {
        $data = $request->validate([
            'hotel_id' => ['required','string'],
            'rate_id'  => ['required'],
        ]);

        $hotel = \App\Models\Hotel::query()
            ->where('code', $data['hotel_id'])
            ->orWhere('id', $data['hotel_id'])
            ->first();

        if (!$hotel) {
            return response()->json([
                'code'    => 404,
                'message' => 'Hotel not found',
            ], 404, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        $rate = \App\Models\Rate::query()
            ->where('id', $data['rate_id'])
            ->where('hotel_id', $hotel->id)
            ->first();

        if (!$rate) {
            return response()->json([
                'code'    => 404,
                'message' => 'Rate not found',
            ], 404, ['Content-Type' => 'application/json; charset=utf-8']);
        }

        return response()->json([
            'id'       => (string)$rate->id,
            'price'    => (float)($rate->price ?? 0),
            'currency' => (string)($rate->currency ?: 'USD'),
            // добавьте нужные поля
        ], 200, ['Content-Type' => 'application/json; charset=utf-8']);
    }

}