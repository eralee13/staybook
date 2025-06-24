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
    public function search(Request $request)
    {
        // 1. Собираем города и дату «завтра» для формы
        $cities   = City::whereNull('country_id')->orderBy('title')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // 2. Извлекаем массив комнат и сразу считаем общее число взрослых и массив возрастов детей
        $rooms = $request->input('rooms', []); // если нет — пустой массив

        $totalAdults    = 0;
        $allChildAges   = [];
        foreach ($rooms as $room) {
            // Взрослые
            $totalAdults += (int) ($room['adults'] ?? 0);

            // Возрасты детей (если есть) собираем в единый массив
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $allChildAges[] = (int) $age;
                }
            }
        }

        // 3. Строим локальный запрос отелей с фильтрацией по тарифам
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
                $end   = $request->end_d;
                $q->whereDoesntHave('bookings', function ($b) use ($start, $end) {
                    $b->where('status', 'reserved')
                        ->where(function ($qb) use ($start, $end) {
                            $qb->whereBetween('arrivalDate',   [$start, $end])
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

            $emerSearch = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $emerHotels = $emerSearch->EmergingGetHotels($request);
            // dd($emerHotels['data']['hotels']);

            if( isset($emerHotels['data']['hotels']) ){

                $filteredHotels = array_filter($emerHotels['data']['hotels'], function ($hotel) {
                    return isset($hotel['localData']['id']);
                });
                // dd($filteredHotels);
                $hotels['hotels'] = array_map(function ($hotel) {
                    // dd($hotel);
                    $rate = $hotel['rates'][0];
                    $price = (float)$rate['payment_options']['payment_types'][0]['amount'] ?? 0;
                    $totalPrice = number_format( ($price * 0.08) + $price , 2, '.', '');

                    return [
                        'apiName' => 'ETG',
                        'apiHotelId' => $hotel['hid'],
                        'hid' => $hotel['localData']['id'] ?? '',
                        'code' => $hotel['localData']['code'] ?? '',
                        'title' => $hotel['localData']['title'] ?? '',
                        'title_en' => $hotel['localData']['title_en'] ?? '',
                        'rating' => $hotel['localData']['rating'] ?? '',
                        'city' => $hotel['localData']['city'] ?? '',
                        'amenities' => $hotel['localData']['amenity']['services'] ?? '',
                        'images' => $hotel['localData']['images'] ?? [],
                        'price' => $price ?? 0,
                        'totalPrice' => $totalPrice ?? 0,
                        'currency' => $rate['payment_options']['payment_types'][0]['currency_code'] ?? 0,
                        'match_hash' => $rate['match_hash'] ?? 0,
                    ];
                }, $filteredHotels);

                $results = json_decode(json_encode($hotels));
            }
            

        // ######## End Emerging API ########


        // ***** Start Tourmind api *****

        // $hotelService = new \App\Services\Tourmind\HotelServices();
        // $tmhotels = $hotelService->tmGetHotels($request);
        // // dd($tmhotels['Hotels']);
        
        // if ( isset($tmhotels['Hotels']) ){
            
        //     $filteredHotels = array_filter($tmhotels['Hotels'], function ($hotel) {
        //         return isset($hotel['localData']['id']);
        //     });
        //     $hotels['hotels'] = array_map(function ($hotel) {
        //         $rate = $hotel['RoomTypes'][0]['RateInfos'][0];
        //         $price = $rate['TotalPrice'] ?? 0;
        //         $totalPrice = number_format( (($price * 8) / 100) + $price , 2, '.', '');

        //         return [
        //             'apiName' => 'TM',
        //             'apiHotelId' => $hotel['HotelCode'],
        //             'hid' => $hotel['localData']['id'] ?? '',
        //             'code' => $hotel['localData']['code'] ?? '',
        //             'title' => $hotel['localData']['title'] ?? '',
        //             'title_en' => $hotel['localData']['title_en'] ?? '',
        //             'rating' => $hotel['localData']['rating'] ?? '',
        //             'city' => $hotel['localData']['city'] ?? '',
        //             'amenities' => $hotel['localData']['amenity']['services'] ?? '',
        //             'images' => $hotel['localData']['images'] ?? [],
        //             'price' => $rate['TotalPrice'] ?? 0,
        //             'totalPrice' => $totalPrice ?? 0,
        //             'currency' => $rate['CurrencyCode'] ?? 0,
        //         ];
        //     }, $filteredHotels);

        //     $results = json_decode(json_encode($hotels));
        //     // dd($results->hotels);
        // }
        // ***** end Tourmind api *****



        if (!empty($propertyIds)) {
            try {
                // Формируем полезную нагрузку (payload) для Exely API
                $payload = [
                    'propertyIds'   => $propertyIds,
                    'adults'        => $totalAdults,
                    'childAges'     => $allChildAges,
                    'arrivalDate'   => $request->arrivalDate,
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

        // 6. Связанные отели (пример жестко закодированных ID — при желании адаптируйте)
        $related = Hotel::whereNull('tourmind_id')
            ->whereIn('id', [14, 15])
            ->get();

        // 7. Возвращаем вьюшку с объединёнными данными
        return view('pages.search.search', [
            'hotels'   => $localHotels,
            'cities'   => $cities,
            'tomorrow' => $tomorrow,
            'request'  => $request,
            'results'  => $results,
            'related'  => $related,
        ]);
    }


    public function hotel($code, Request $request)
    {
        $hotel = Hotel::cacheFor(now()->addHours(2))->where('code', $code)->first();
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
        $childs = [];
        if (!empty($request['childAges']) && is_array($request['childAges'])) {
            foreach ($request['childAges'] as $age) {
                // Приводим к int/строке на всякий случай
                $childs[] = trim((string) $age);
            }
        }

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
