<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Room;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // 1. Локальная часть: города + дата + отели с фильтрами
        $cities = City::whereNull('country_id')->orderBy('title')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $hotelQuery = Hotel::with(['rates' => function ($q) use ($request) {
            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }
            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }
            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }
            if ($request->filled('start_d') && $request->filled('end_d')) {
                $start = $request->start_d;
                $end = $request->end_d;
                $q->whereDoesntHave('bookings', function ($b) use ($start, $end) {
                    $b->where('status', 'reserved')
                        ->where(function ($qb) use ($start, $end) {
                            $qb->whereBetween('arrivalDate', [$start, $end])
                                ->orWhereBetween('departureDate', [$start, $end])
                                ->orWhere(function ($qbb) use ($start, $end) {
                                    $qbb->where('arrivalDate', '<=', $start)
                                        ->where('departureDate', '>=', $end);
                                });
                        });
                });
            }
        }]);

        if ($request->filled('city')) {
            $hotelQuery->where('city', $request->city);
        }
        if ($request->filled('rating')) {
            $hotelQuery->where('rating', '>=', $request->rating);
        }
        if ($request->sort === 'highest_rating') {
            $hotelQuery->orderBy('rating', 'desc');
        } elseif ($request->sort === 'lowest_rating') {
            $hotelQuery->orderBy('rating', 'asc');
        }

        // Собираем отели и сразу отфильтровываем тех, у кого нет хоть одного тарифа
        $localHotels = $hotelQuery->get();
        //$localHotels = $hotelQuery->get()->filter(fn($h) => $h->rates->isNotEmpty());

        // Дополнительно сортируем по цене, если нужно
        if ($request->sort === 'lowest_price') {
            $localHotels = $localHotels->sortBy(fn($h) => $h->rates->min('price'))->values();
        } elseif ($request->sort === 'highest_price') {
            $localHotels = $localHotels->sortByDesc(fn($h) => $h->rates->max('price'))->values();
        }

        // 2. Собираем exely_id для API
        $propertyIds = $localHotels
            ->pluck('exely_id')
            ->filter()              // убираем null/пустые
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

        $results = null;
        if (!empty($propertyIds)) {
            try {
                $child_array = array_filter([
                    $request->age1,
                    $request->age2,
                    $request->age3,
                ], fn($a) => $a !== null);

                $payload = [
                    'propertyIds' => $propertyIds,
                    'adults' => $request->adult,
                    'childAges' => $child_array,
                    'arrivalDate' => $request->arrivalDate,
                    'departureDate' => $request->departureDate,
                ];

                $response = Http::timeout(30)
                    ->connectTimeout(5)
                    ->retry(2, 100)
                    ->accept('application/json')
                    ->withHeaders(['x-api-key' => config('services.exely.key')])
                    ->post(config('services.exely.base_url') . 'search/v1/properties/room-stays/search', $payload);

                if ($response->successful()) {
                    $results = $response->object();
                } elseif ($response->serverError()) {
                    Log::warning("Exely 5xx: {$response->status()}");
                    return response()->view('errors.500', [], 500);
                } else {
                    Log::warning("Exely 4xx: {$response->status()}");
                    return response()->view('errors.400', [], 400);
                }
            } catch (ConnectionException $e) {
                Log::error('ConnectionException при Exely: ' . $e->getMessage());
                return response()->view('errors.503', ['message' => 'Сервис временно недоступен'], 503);
            }
        }

        // 3. «Пришиваем» к каждому отелю результаты API по exely_id
        if ($results && property_exists($results, 'propertyRoomStayResponses')) {
            $apiMap = collect($results->propertyRoomStayResponses)
                ->keyBy(fn($item) => (string)$item->propertyId);

            $localHotels = $localHotels->map(function ($hotel) use ($apiMap) {
                $hotel->api_room_stays = $apiMap
                    ->get((string)$hotel->exely_id, (object)['roomStays' => []])
                    ->roomStays;
                return $hotel;
            });
        }

        // 4. Связанные отели
        $related = Hotel::whereNull('tourmind_id')
            ->whereIn('id', [14, 15])
            ->get();

        // 5. Отдаём одну вьюшку с объединёнными данными
        return view('pages.search.search', [
            'hotels' => $localHotels,
            'cities' => $cities,
            'tomorrow' => $tomorrow,
            'request' => $request,
            'results' => $results,
            'related' => $related,
        ]);

    }

    public function hotel($code, Request $request)
    {
        $hotel = Hotel::cacheFor(now()->addHours(24))->where('code', $code)->first();
        $arrival = Carbon::createFromDate($request->arrivalDate);
        $departure = Carbon::createFromDate($request->departureDate);
        $count_day = $arrival->diffInDays($departure);
        $adult = $request->adult;

        $query = Room::with(['rates' => function ($q) use ($request) {
            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }

            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }

            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }

            // Показать только те тарифы, у которых нет бронирования
            if ($request->filled('arrivalDate') && $request->filled('departureDate')) {
                $startTime = $request->arrivalDate;
                $endTime = $request->departureDate;

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
        }])->where('hotel_id', $hotel->id);

        $rooms = $query->get()->filter(function ($room) {
            return $room->rates->isNotEmpty();
        });

        if ($hotel->exely_id != null) {
            return view('pages.search.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms'));
        } else {
            return view('pages.search.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms'));
        }
    }


    //exely
    public function hotel_exely(Request $request)
    {
        //dd($request->all());
        $childs = explode(',', $request->childAges);
        if (in_array('', $childs, true)) {
            $response = Http::withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->get(config('services.exely.base_url') . 'search/v1/properties/' . $request->propertyId . '/room-stays?arrivalDate=' . $request->arrivalDate . '&departureDate=' . $request->departureDate . '&adults=' . $request->adultCount . '&includeExtraStays=false&includeExtraServices=false');
        } else {
            foreach ($childs as $child) {
                $items[] = '&childAges=' . $child;
            }
            $response = Http::withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->get(config('services.exely.base_url') . 'search/v1/properties/' . $request->propertyId . '/room-stays?arrivalDate=' . $request->arrivalDate . '&departureDate=' . $request->departureDate . '&adults=' . $request->adultCount . implode($items) . '&includeExtraStays=false&includeExtraServices=false');
        }
        //dd($response->object());
        $rooms = $response->object()->roomStays;
        $rooms = collect($rooms)->sortBy('total')->values()->all();

        return view('pages.search.exely.hotel', compact('rooms', 'request'));
    }

}
