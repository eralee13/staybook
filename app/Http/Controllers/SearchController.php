<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\City;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\Meal;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $cities = City::whereNull('country_id')->orderBy('title')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $rooms = $request->input('rooms', []);
        $totalAdults = 0;
        $allChildAges = [];

        foreach ($rooms as $room) {
            $totalAdults += (int)($room['adults'] ?? 0);
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $allChildAges[] = (int)$age;
                }
            }
        }

        $start = $request->input('arrivalDate');
        $end = $request->input('departureDate');

        $hotelQuery = Hotel::with(['rates' => function ($q) use ($request, $totalAdults, $start, $end) {
            if ($request->filled('rooms')) {
                $q->where('availability', '>=', $totalAdults);
            }

            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }

            if ($start && $end) {
                $q->whereDoesntHave('bookings', function ($b) use ($start, $end) {
                    $b->where(function ($qb) use ($start, $end) {
                        $qb->whereBetween('arrivalDate', [$start, $end])
                            ->orWhereBetween('departureDate', [$start, $end])
                            ->orWhere(function ($qbb) use ($start, $end) {
                                $qbb->where('arrivalDate', '<=', $start)
                                    ->where('departureDate', '>=', $end);
                            });
                    });
                });

                $q->with(['bookings' => function ($b) use ($start, $end) {
                    $b->where(function ($qb) use ($start, $end) {
                        $qb->whereBetween('arrivalDate', [$start, $end])
                            ->orWhereBetween('departureDate', [$start, $end])
                            ->orWhere(function ($qbb) use ($start, $end) {
                                $qbb->where('arrivalDate', '<=', $start)
                                    ->where('departureDate', '>=', $end);
                            });
                    });
                }]);
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

        $localHotels = $hotelQuery->get();

        // Перезаписываем цену если есть брони
        foreach ($localHotels as $hotel) {
            foreach ($hotel->rates as $rate) {
                $customBook = $rate->bookings->first();
                $rate->effective_price = $customBook?->price ?? $rate->price;
            }
        }

        if ($request->sort === 'lowest_price') {
            $localHotels = $localHotels->sortBy(fn($h) => $h->rates->min('effective_price'))->values();
        } elseif ($request->sort === 'highest_price') {
            $localHotels = $localHotels->sortByDesc(fn($h) => $h->rates->max('effective_price'))->values();
        }

        // API Exely
        $propertyIds = $localHotels
            ->pluck('exely_id')
            ->filter()
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

        $results = null;

        if (!empty($propertyIds)) {
            try {
                $payload = [
                    'propertyIds' => $propertyIds,
                    'adults' => $totalAdults,
                    'childAges' => $allChildAges,
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

        $localHotels = $localHotels->filter(function ($hotel) {
            $hasLocalRates = $hotel->rates && $hotel->rates->isNotEmpty();
            $hasApiRates = !empty($hotel->api_room_stays);
            return $hasLocalRates || $hasApiRates;
        })->values();

        if ($localHotels->isEmpty()) {
            return view('pages.search.search', [
                'hotels' => [],
                'cities' => $cities,
                'tomorrow' => $tomorrow,
                'request' => $request,
                'results' => $results,
                'error' => 'По вашему запросу отели не найдены.',
            ]);
        }

        return view('pages.search.search', [
            'hotels' => $localHotels,
            'cities' => $cities,
            'tomorrow' => $tomorrow,
            'request' => $request,
            'results' => $results,
        ]);
    }

    public function hotel($code, Request $request)
    {
        $hotel = Hotel::where('code', $code)->first();
        $arrival = Carbon::createFromDate($request->arrivalDate);
        $departure = Carbon::createFromDate($request->departureDate);
        $count_day = $arrival->diffInDays($departure);
        $adult = $request->adult;

        $query = Room::with(['rates' => function ($q) use ($request, $arrival, $departure) {
            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }
            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }
            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }

            if ($request->filled('arrivalDate') && $request->filled('departureDate')) {
                $start = $request->arrivalDate;
                $end = $request->departureDate;

                $q->whereDoesntHave('bookings', function ($b) use ($start, $end) {
                    $b->where(function ($b2) {
                        $b2->where('status', 'reserved')
                            ->orWhere(function ($b3) {
                                $b3->where('status', 'pending')->where('adult', 0);
                            });
                    })
                        ->where(function ($b4) use ($start, $end) {
                            $b4->whereBetween('arrivalDate', [$start, $end])
                                ->orWhereBetween('departureDate', [$start, $end])
                                ->orWhere(function ($b5) use ($start, $end) {
                                    $b5->where('arrivalDate', '<=', $start)
                                        ->where('departureDate', '>=', $end);
                                });
                        });
                });
            }
        }])
            ->where('hotel_id', $hotel->id);

        $rooms = $query->get()->filter(function ($r) use ($arrival, $departure) {
            $r->rates->transform(function ($rate) use ($arrival, $departure) {
                $bookPrice = Book::where('rate_id', $rate->id)
                    ->where('arrivalDate', '<=', $arrival)
                    ->where('departureDate', '>=', $departure)
                    ->whereNotNull('price')
                    ->orderByDesc('id')
                    ->value('price');
                if ($bookPrice !== null) {
                    $rate->price = $bookPrice;
                }
                return $rate;
            });
            return $r->rates->isNotEmpty();
        });

        $start = $arrival->copy()->startOfDay();
        $end = $departure->copy()->startOfDay();

        $bookingPrices = \App\Models\Book::whereIn('rate_id', $rooms->flatMap->rates->pluck('id'))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('arrivalDate', [$start, $end])
                    ->orWhereBetween('departureDate', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('arrivalDate', '<=', $start)
                            ->where('departureDate', '>=', $end);
                    });
            })
            ->whereNotNull('price')
            ->get()
            ->groupBy('rate_id');

        $ratePrices = [];

        foreach ($rooms as $room) {
            foreach ($room->rates as $rate) {
                if (isset($bookingPrices[$rate->id])) {
                    $ratePrices[$rate->id] = $bookingPrices[$rate->id]->first()->price;
                }
            }
        }

        return view('pages.search.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms', 'ratePrices'));
    }

    //exely
    public function hotel_exely(Request $request)
    {
        // ✅ Валидация входных параметров
        $request->validate([
            'propertyId' => 'required|string',
            'arrivalDate' => 'required|date',
            'departureDate' => 'required|date|after:arrivalDate',
            'adultCount' => 'required|integer|min:1',
            'childAges' => 'nullable|array',
        ]);

        // ✅ Очистка массива childAges от пустых значений
        $childAgesInput = (array)$request->input('childAges', []);
        $childs = array_filter($childAgesInput, fn($age) => trim($age) !== '');
        $childs = array_map('intval', $childs); // безопасное преобразование в числа

        // ✅ Параметры запроса
        $params = [
            'arrivalDate' => $request->arrivalDate,
            'departureDate' => $request->departureDate,
            'adults' => $request->adultCount,
            'includeExtraStays' => 'false',
            'includeExtraServices' => 'false',
        ];

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        foreach ($childs as $age) {
            $queryString .= '&childAges=' . urlencode($age);
        }

        // ✅ Финальный URL
        $url = rtrim(config('services.exely.base_url'), '/') . "/search/v1/properties/{$request->propertyId}/room-stays?" . $queryString;

        // ✅ Выполняем запрос
        $response = Http::withHeaders([
            'x-api-key' => config('services.exely.key'),
            'accept' => 'application/json',
        ])->get($url);

        // ✅ Лог ответа
        Log::debug('📥 Ответ Exely:', [
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        // ✅ Безопасное получение roomStays
        $data = json_decode($response->body());

        if (!isset($data->roomStays) || !is_array($data->roomStays)) {
            Log::warning('Exely: Нет roomStays в ответе', ['response' => $data]);
            return view('pages.search.exely.hotel', [
                'rooms' => [],
                'request' => $request,
            ]);
        }

        // ✅ Сортировка по цене
        $rooms = collect($data->roomStays)
            ->sortBy('total.priceBeforeTax')
            ->values()
            ->all();

        return view('pages.search.exely.hotel', compact('rooms', 'request'));


    }

    // tourmind
    public function hotel_tm($hid, Request $request)
    {
        $hotel = Hotel::where('id', $hid)->with(['amenity'])->first();
        $room = Room::where('hotel_id', $hid)->where('tourmind_id', $hotel->tourmind_id)->get(['amenities'])->first();
        $amenities = explode(',', $room->amenities ?? '');
        $roomAmenity = array_slice($amenities, 0, 8);
        $meals = Meal::pluck('title', 'id');
        $arrival = Carbon::createFromDate($request->arrivalDate);
        $departure = Carbon::createFromDate($request->departureDate);

        $hotelService = new \App\Services\Tourmind\HotelServices();
        $tmroom = $hotelService->getOneDetail($request, $hotel->id);
        $tmimages = Image::where('hotel_id', $hotel->id)->where('caption', 'Room')->get('image');

        $city = City::where('title', $hotel->city)->first(['country_code']);

        if (!$hotel->utc && $city && ($utc = $hotelService->getUtcOffsetByCountryCode($city->country_code))) {
            $hotel->utc = $utc;
            $hotel->save();
        }


        return view('pages.search.tourmind.hotel', compact('hotel', 'arrival', 'departure', 'request', 'roomAmenity', 'tmroom', 'tmimages', 'meals'));
    }

    // Emerging
    public function hotel_etg($hid, Request $request)
    {
        $hotel = Hotel::where('id', $hid)->with(['amenity'])->first();
        $room = Room::where('hotel_id', $hid)->get(['amenities'])->first();
        $amenities = explode(',', $room->amenities ?? '');
        $roomAmenity = array_slice($amenities, 0, 8);
        $meals = Meal::pluck('title', 'id');
        $arrival = Carbon::createFromDate($request->arrivalDate);
        $departure = Carbon::createFromDate($request->departureDate);

        $emergingSearch = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
        $etgroom = $emergingSearch->searchRates($request, $hotel->id);
        // dd($etgroom);
        $tmimages = Image::where('hotel_id', $hotel->id)->where('caption', 'guest_rooms')->get('image');

        $city = City::where('title', $hotel->city)->first(['country_code']);

        if (!$hotel->utc && $city && ($utc = $hotelService->getUtcOffsetByCountryCode($city->country_code))) {
            $hotel->utc = $utc;
            $hotel->save();
        }


        return view('pages.search.emerging.hotel', compact('hotel', 'arrival', 'departure', 'request', 'roomAmenity', 'etgroom', 'tmimages', 'meals'));
    }
}
