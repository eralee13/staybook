<?php
namespace App\Http\Controllers\API\V1_1;

use App\Exceptions\EtgBadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1_1\SearchOneRequest;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\API\V1_1\SearchRequest;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * POST /api/v1.1/search
     */
    public function search(Request $r)
    {
        $payload = $r->validate([
            'check_in'       => 'required|date_format:Y-m-d',
            'check_out'      => 'required|date_format:Y-m-d|after:check_in',
            'residency'      => 'required|string|size:2',
            'guests_groups'  => 'required|array|min:1',
            'hotel_ids'      => 'sometimes|array',
            'hotel_ids.*'    => 'string',
        ]);

        $this->validateRestrictions($payload['guests_groups']); // метод ДОЛЖЕН существовать

        // Если передали hotel_ids — неизвестные игнорим, все неизвестны => 200 + []
        if (isset($payload['hotel_ids']) && is_array($payload['hotel_ids'])) {
            $requested = array_map('strval', $payload['hotel_ids']);
            $known     = array_map('strval', $this->knownHotels ?? []); // обеспечьте свойство
            $filtered  = array_values(array_intersect($requested, $known));
            if (count($filtered) === 0) {
                return response()->json([], 200); // массив, не объект!
            }
            // ...ищите только по $filtered (если нужно)
        }

        // Ваша логика поиска. Нет офферов? → пустой массив.
        $result = [];
        return response()->json($result, 200);
    }

    private array $knownHotels = ['14','16']; // пример

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