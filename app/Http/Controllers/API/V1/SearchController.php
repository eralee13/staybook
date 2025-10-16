<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\SearchRequest;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    /**
     * Поиск отелей
     */
    public function index(SearchRequest $request)
    {
        // входные
        $adult     = $request->filled('adult') ? (int) $request->adult : null;
        $hasDates  = $request->filled('start_d') && $request->filled('end_d');
        $startTime = $hasDates ? (string) $request->start_d : null; // YYYY-MM-DD
        $endTime   = $hasDates ? (string) $request->end_d   : null; // YYYY-MM-DD

        $hotelsQuery = Hotel::query()
            ->where('status', 1);

        // фильтры по самому отелю
        if ($request->filled('region')) {
            $hotelsQuery->where('city', $request->get('region'));
        }
        if ($request->filled('rating')) {
            $hotelsQuery->where('rating', '>=', (int) $request->rating);
        }

        $hotelsQuery
            // оставить в выдаче только те отели, у которых есть комнаты с подходящими тарифами
            ->whereHas('rooms.rates', function ($q) use ($adult, $hasDates, $startTime, $endTime) {
                if (!is_null($adult)) {
                    $q->where('availability', '>=', $adult); // или 'adult', если ваша квота хранится там
                }
                if ($hasDates) {
                    $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                        $b->where('status', 'reserved')
                            ->where(function ($query) use ($startTime, $endTime) {
                                $query->whereBetween('arrivalDate',   [$startTime, $endTime])
                                    ->orWhereBetween('departureDate', [$startTime, $endTime])
                                    ->orWhere(function ($q) use ($startTime, $endTime) {
                                        $q->where('arrivalDate', '<=', $startTime)
                                            ->where('departureDate', '>=', $endTime);
                                    });
                            });
                    });
                }
            })
            ->with(['rooms' => function ($rooms) use ($adult, $hasDates, $startTime, $endTime) {
                // оставляем только те комнаты, у которых есть хотя бы один подходящий тариф
                $rooms->whereHas('rates', function ($q) use ($adult, $hasDates, $startTime, $endTime) {
                    if (!is_null($adult)) {
                        $q->where('availability', '>=', $adult); // или ->where('adult', '>=', $adult)
                    }
                    if ($hasDates) {
                        $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                            $b->where('status', 'reserved')
                                ->where(function ($query) use ($startTime, $endTime) {
                                    $query->whereBetween('arrivalDate',   [$startTime, $endTime])
                                        ->orWhereBetween('departureDate', [$startTime, $endTime])
                                        ->orWhere(function ($q) use ($startTime, $endTime) {
                                            $q->where('arrivalDate', '<=', $startTime)
                                                ->where('departureDate', '>=', $endTime);
                                        });
                                });
                        });
                    }
                })
                    ->with(['rates' => function ($q) use ($adult, $hasDates, $startTime, $endTime) {
                        if (!is_null($adult)) {
                            $q->where('availability', '>=', $adult); // или 'adult'
                        }
                        if ($hasDates) {
                            $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                                $b->where('status', 'reserved')
                                    ->where(function ($query) use ($startTime, $endTime) {
                                        $query->whereBetween('arrivalDate',   [$startTime, $endTime])
                                            ->orWhereBetween('departureDate', [$startTime, $endTime])
                                            ->orWhere(function ($q) use ($startTime, $endTime) {
                                                $q->where('arrivalDate', '<=', $startTime)
                                                    ->where('departureDate', '>=', $endTime);
                                            });
                                    });
                            });
                        }
                    }]);
            }]);

        $hotels = $hotelsQuery->get();

        return response()->json($hotels);
    }

    /**
     * @param $id
     * @param SearchRequest $request
     * @return JsonResponse
     */
    /**
     * Поиск определенного отеля
     */
    public function show(int $id, SearchRequest $request)
    {
        $adult     = $request->filled('adult') ? (int) $request->adult : null;
        $child     = $request->filled('child') ? (int) $request->child : null;
        $hasDates  = $request->filled('arrivalDate') && $request->filled('departureDate');
        $startTime = $hasDates ? (string) $request->arrivalDate   : null;
        $endTime   = $hasDates ? (string) $request->departureDate : null;

        $hotelQuery = Hotel::query()
            ->where('id', $id)
            ->where('status', 1)
            // подгружаем связанные данные
            ->with([
                'city',
                'images',
                'amenities',
                // Комнаты отеля
                'rooms' => function ($rooms) use ($adult, $child, $hasDates, $startTime, $endTime) {
                    // оставляем только комнаты, у которых есть подходящие тарифы
                    $rooms->whereHas('rates', function ($q) use ($adult, $child, $hasDates, $startTime, $endTime) {
                        if (!is_null($adult)) {
                            $q->where('availability', '>=', $adult); // или ->where('adult', '>=', $adult)
                        }
                        if (!is_null($child)) {
                            $q->where('child', '>=', $child);
                        }
                        if ($hasDates) {
                            $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                                $b->where('status', 'reserved')
                                    ->where(function ($query) use ($startTime, $endTime) {
                                        $query->whereBetween('arrivalDate',   [$startTime, $endTime])
                                            ->orWhereBetween('departureDate', [$startTime, $endTime])
                                            ->orWhere(function ($q) use ($startTime, $endTime) {
                                                $q->where('arrivalDate', '<=', $startTime)
                                                    ->where('departureDate', '>=', $endTime);
                                            });
                                    });
                            });
                        }
                    });

                    // подгружаем тарифы для этих комнат
                    $rooms->with(['rates' => function ($q) use ($adult, $child, $hasDates, $startTime, $endTime) {
                        if (!is_null($adult)) {
                            $q->where('availability', '>=', $adult);
                        }
                        if (!is_null($child)) {
                            $q->where('child', '>=', $child);
                        }
                        if ($hasDates) {
                            $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                                $b->where('status', 'reserved')
                                    ->where(function ($query) use ($startTime, $endTime) {
                                        $query->whereBetween('arrivalDate',   [$startTime, $endTime])
                                            ->orWhereBetween('departureDate', [$startTime, $endTime])
                                            ->orWhere(function ($q) use ($startTime, $endTime) {
                                                $q->where('arrivalDate', '<=', $startTime)
                                                    ->where('departureDate', '>=', $endTime);
                                            });
                                    });
                            });
                        }
                        // можно добавить сортировку по цене
                        // $q->orderBy('price_minor');
                    }]);
                }
            ]);

        $hotel = $hotelQuery->first();

        if (!$hotel) {
            return response()->json([
                'error' => [
                    'code'    => 'not_found',
                    'message' => 'Отель не найден',
                ]
            ], 404);
        }

        return response()->json($hotel);
    }

}
