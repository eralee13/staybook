<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1_1\SearchOneRequest;
use App\Http\Requests\API\V1_1\SearchRequest;
use App\Models\Hotel;
use App\Models\Meal;
use App\Models\Rate;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function search(SearchRequest $request)
    {
        $query = Hotel::with(['rates' => function ($q) use ($request) {
            if ($request->filled('adults')) {
                $q->where('availability', '>=', $request->adults);
            }

            if ($request->filled('start_d') && $request->filled('end_d')) {
                $startTime = $request->check_in;
                $endTime = $request->check_out;

                $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                    $b->where('status', 'reserved')
                        ->where(function ($query) use ($startTime, $endTime) {
                            $query->whereBetween('arrivalDate', [$startTime, $endTime])
                                ->orWhereBetween('departureDate', [$startTime, $endTime])
                                ->orWhere(function ($q) use ($startTime, $endTime) {
                                    $q->where('arrivalDate', '<=', $startTime)
                                        ->where('departureDate', '>=', $endTime);
                                });
                        });
                });
            }

        }]);
        if ($request->filled('hotel_ids')) {
            $query->whereIn('id', $request->get('hotel_ids'))->orWhereIn('exely_id', $request->get('hotel_ids'));
        }

//        if ($request->filled('rating')) {
//            $query->where('rating', '>=', $request->rating);
//        }

        $hotels = $query->where('status', 1)->get();

        $hotels_array = [];
        foreach ($hotels as $hotel) {
            $rates_array = [];
            foreach ($hotel->rates as $rate) {
                $room_array = [];
                foreach ($hotel->rooms as $room) {
                    $room_array = [
                        'id' => $room->id,
                        'name' => $room->title,
                        'bed_groups' => [
                            'id' => $rate->id,
                            'name' => $rate->bed_type,
                        ],
                        'allotment' => $rate->availability,
                    ];
                }
                $rates_array[] = [
                    'id' => $rate->id,
                    'price' => $rate->price,
                    'payment_type' => 'prepay',
                    'currency' => $rate->currency ?? '$',
                    'rooms' => $room_array,
                ];
            }
            $hotels_array[] = [
                'hotel_id' => $hotel->id,
                'rates' => $rates_array,
            ];
        }
        return response()->json($hotels_array);
    }

    /**
     * @param $id
     * @param SearchOneRequest $request
     * @return JsonResponse
     */
    public function show($id, SearchOneRequest $request)
    {
        $query = Room::with(['rates' => function ($q) use ($request) {
            if ($request->filled('adults')) {
                $q->where('availability', '>=', $request->adults);
            }

            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }
            if ($request->filled('arrivalDate') && $request->filled('departureDate')) {
                $startTime = $request->check_in;
                $endTime = $request->check_out;

                $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                    $b->where('status', 'reserved')
                        ->where(function ($query) use ($startTime, $endTime) {
                            $query->whereBetween('arrivalDate', [$startTime, $endTime])
                                ->orWhereBetween('departureDate', [$startTime, $endTime])
                                ->orWhere(function ($q) use ($startTime, $endTime) {
                                    $q->where('arrivalDate', '<=', $startTime)
                                        ->where('departureDate', '>=', $endTime);
                                });
                        });
                });
            }
        }])->where('hotel_id', $id);

        $rooms = $query->get()->filter(function ($room) {
            return $room->rates->isNotEmpty();
        });

        $rooms_array = [];
        foreach ($rooms as $room) {
            $rates_array = [];
            foreach ($room->rates as $rate) {
                //meal
                $meals = Meal::where('id', $rate->meal_id)->get();
                $meals_array = [];
                foreach ($meals as $meal) {
                    $meals_array[] = [
                        'id' => $meal->id,
                        'name' => $meal->title,
                    ];
                }

                $cancelDate = Carbon::parse($request->arrivalDate)->subDays($rate->cancellationRule->free_cancellation_days);
                $rates_array = [
                    'id' => $rate->id,
                    'price' => $rate->price,
                    'bar_price' => null,
                    'comission' => null,
                    'supplier_min_price' => null,
                    'taxes' => [
                        'type' => null,
                        'currency' => '$',
                        'is_included' => true,
                        'amount' => round($rate->price * 0.14, 2),
                    ],
                    'payment_type' => 'prepay',
                    'currency' => '$',
                    'meals' => $meals_array,
                    'cancellation_policies' => [
                        'from' => $cancelDate,
                        'amount' => $rate->cancellationRule->penalty_amount,
                    ],
                    'rooms' => [
                        'id' => $room->id,
                        'name' => $room->title,
                        'bed_groups' => [
                            'id' => $rate->id,
                            'name' => $rate->bed_type,
                        ],
                        'allotment' => $rate->availability,
                    ]
                ];
            }
        }

        return response()->json($rates_array);
    }

    /**
     * @param $hotel_id
     * @param $rate_id
     * @return JsonResponse
     */
    public function ratedetails($hotel_id, $rate_id)
    {
        $rates = Rate::where('hotel_id', $hotel_id)->where('id', $rate_id)->get();

        return response()->json($rates);
    }

}
