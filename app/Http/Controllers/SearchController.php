<?php

namespace App\Http\Controllers;

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
    public $coef;

    public function __construct(){
        $this->coef = config('app.main_coef');
    }

    public function search(Request $request)
    {
        
        $cities = City::whereNull('country_id')->orderBy('title')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $rooms = $request->input('rooms', []); // если нет — пустой массив
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

        $hotelQuery = Hotel::with(['rates' => function ($q) use ($request, $totalAdults) {
            // Фильтруем тарифы: проверяем, что есть места для всех взрослых и детей
            if ($request->filled('rooms')) {
                $q->where('availability', '>=', $totalAdults);
            }

            // Если задан meal_id — фильтруем по питанию
            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }

            // Фильтрация по датам: исключаем тарифы, у которых уже зарезервированы подходящие даты
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

        // Фильтруем по городу и рейтингу
        if ($request->filled('city')) {
            $hotelQuery->where('city', $request->city);
        }
        if ($request->filled('rating')) {
            $hotelQuery->where('rating', '>=', $request->rating);
        }

        // Сортировка по рейтингу
        if ($request->sort === 'highest_rating') {
            $hotelQuery->orderBy('rating', 'desc');
        } elseif ($request->sort === 'lowest_rating') {
            $hotelQuery->orderBy('rating', 'asc');
        }

        // Выполняем запрос и получаем коллекцию отелей вместе с уже подгруженными тарифами
        $localHotels = $hotelQuery->get();


        // Дополнительная сортировка по цене (если задана)
        if ($request->sort === 'lowest_price') {
            $localHotels = $localHotels->sortBy(fn($h) => $h->rates->min('price'))->values();
        } elseif ($request->sort === 'highest_price') {
            $localHotels = $localHotels->sortByDesc(fn($h) => $h->rates->max('price'))->values();
        }

        // 4. Берём exely_id из отелей для запроса к API
        $propertyIds = $localHotels
            ->pluck('exely_id')
            ->filter()                    // убираем null/пустые
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

        $results = null;


        // ######## Emerging API ########

//            $emerSearch = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
//            $emerHotels = $emerSearch->EmergingGetHotels($request);
//            // dd($emerHotels['data']['hotels']);
//
//            if( isset($emerHotels['data']['hotels']) ){
//
//                $filteredHotels = array_filter($emerHotels['data']['hotels'], function ($hotel) {
//                    return isset($hotel['localData']['id']);
//                });
//                // dd($filteredHotels);
//                $hotels['hotels'] = array_map(function ($hotel) {
//                    // dd($hotel);
//                    $rate = $hotel['rates'][0];
//                    $price = (float)$rate['payment_options']['payment_types'][0]['amount'] ?? 0;
//                    $totalPrice = number_format( ($price * $this->coef) + $price , 2, '.', '');
//
//                    return [
//                        'apiName' => 'ETG',
//                        'apiHotelId' => $hotel['hid'],
//                        'hid' => $hotel['localData']['id'] ?? '',
//                        'code' => $hotel['localData']['code'] ?? '',
//                        'title' => $hotel['localData']['title'] ?? '',
//                        'title_en' => $hotel['localData']['title_en'] ?? '',
//                        'rating' => $hotel['localData']['rating'] ?? '',
//                        'city' => $hotel['localData']['city'] ?? '',
//                        'amenities' => $hotel['localData']['amenity']['services'] ?? '',
//                        'images' => $hotel['localData']['images'] ?? [],
//                        'price' => $price ?? 0,
//                        'totalPrice' => $totalPrice ?? 0,
//                        'currency' => $rate['payment_options']['payment_types'][0]['currency_code'] ?? 0,
//                        'match_hash' => $rate['match_hash'] ?? 0,
//                    ];
//                }, $filteredHotels);
//
//                $results = json_decode(json_encode($hotels));
//            }
//

        // ######## End Emerging API ########


        // ***** Start Tourmind api *****

        $hotelService = new \App\Services\Tourmind\HotelServices();
        $tmhotels = $hotelService->tmGetHotels($request);
        // dd($tmhotels);

        if ( isset($tmhotels['Hotels']) ){

            $filteredHotels = array_filter($tmhotels['Hotels'], function ($hotel) {
                return isset($hotel['localData']['id']);
            });
            $hotels['hotels'] = array_map(function ($hotel) {
                $rate = $hotel['RoomTypes'][0]['RateInfos'][0];
                $price = $rate['TotalPrice'] ?? 0;
                $totalPrice = number_format( ($price * $this->coef) + $price , 2, '.', '');

                return [
                    'apiName' => 'TM',
                    'apiHotelId' => $hotel['HotelCode'],
                    'hid' => $hotel['localData']['id'] ?? '',
                    'code' => $hotel['localData']['code'] ?? '',
                    'title' => $hotel['localData']['title'] ?? '',
                    'title_en' => $hotel['localData']['title_en'] ?? '',
                    'rating' => $hotel['localData']['rating'] ?? '',
                    'city' => $hotel['localData']['city'] ?? '',
                    'amenities' => $hotel['localData']['amenity']['services'] ?? '',
                    'images' => $hotel['localData']['images'] ?? [],
                    'price' => $rate['TotalPrice'] ?? 0,
                    'totalPrice' => $totalPrice ?? 0,
                    'currency' => $rate['CurrencyCode'] ?? 0,
                ];
            }, $filteredHotels);

            $results = json_decode(json_encode($hotels));
            // dd($results->hotels);
        }

        // ***** end Tourmind api *****


        if (!empty($propertyIds)) {
            try {
                // Формируем полезную нагрузку (payload) для Exely API
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

        // 5. Привязываем полученные от API «roomStays» к локальным моделям отелей (по exely_id)
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

        if ($localHotels->isEmpty()) {
            return view('pages.search.search', [
                'hotels' => [],
                'cities' => $cities,
                'tomorrow' => $tomorrow,
                'request' => $request,
                'results' => $results,
                'error' => 'По вашему запросу отели не найдены.',
            ]);
        } else {
            // 7. Возвращаем вьюшку с объединёнными данными
            return view('pages.search.search', [
                'hotels' => $localHotels,
                'cities' => $cities,
                'tomorrow' => $tomorrow,
                'request' => $request,
                'results' => $results,
            ]);
        }

    }


    public function hotel($code, Request $request)
    {
        $hotel = Hotel::where('code', $code)->first();
        //$hotel = Hotel::cacheFor(now()->addHours(2))->where('code', $code)->first();
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

            // 2) Если указаны даты — исключаем и reserved, и pending с adult=0
            if ($request->filled('arrivalDate') && $request->filled('departureDate')) {
                $start = $request->arrivalDate;
                $end = $request->departureDate;

                $q->whereDoesntHave('bookings', function ($b) use ($start, $end) {
                    // Сначала отбираем «проблемные» брони:
                    //   status = reserved  OR  (status = pending AND adult = 0)
                    $b->where(function ($b2) {
                        $b2->where('status', 'pending')
                            ->where('adult', '===', 0);
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
        $rooms = $query->get()->filter(fn($r) => $r->rates->isNotEmpty());


        return view('pages.search.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms'));
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
        $meals = [
                        1 => 'No Breakfast',
                        2 => 'Breakfast',
                        3 => 'Lunch',
                        4 => 'Dinner',
                        5 => 'Lunch and Dinner',
                        6 => 'HalfBoard',
                        7 => 'FullBoard',
                        8 => 'AllInclusive',
                        9 => 'SelfCatering',
                    ];
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
