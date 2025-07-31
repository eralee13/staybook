<?php

namespace App\Http\Controllers;

use App\Services\FXService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\City;
use App\Models\Image;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Meal;

class SearchController extends Controller
{
    public $coef;

    public function __construct(){
        $this->coef = config('app.main_coef');
    }

    public function search(Request $request)
    {
        $cities = City::whereNull('country_id')->orderBy('title')->get();
        $fxBase = session('currency', 'USD');
        $fxRates = app(\App\Services\FXService::class)->getRatesBaseCentral();

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

        $hotelQuery = Hotel::with(['rates' => function ($q) use ($request, $totalAdults) {
            if ($request->filled('rooms')) {
                $q->where('availability', '>=', $totalAdults);
            }
            if ($request->filled('meal')) {
                $q->whereIn('meal_id', (array)$request->meal);
            }
            if ($request->filled('start_d') && $request->filled('end_d')) {
                $q->whereDoesntHave('bookings', function ($b) use ($request) {
                    $start = $request->start_d;
                    $end = $request->end_d;
                    $b->where('status', 'reserved')->where(function ($qb) use ($start, $end) {
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

        $local = $hotelQuery->get();

        $localHotels = $local
            ->filter(fn($hotel) => empty($hotel->exely_id))
            ->map(function ($hotel) use ($fxRates) {
                $minRate = $hotel->rates->min('price') ?? 0;
                $currency = $hotel->rates->first()?->currency ?? 'USD';
                $rateFrom = $fxRates[$currency] ?? 1;
                $priceUsd = $rateFrom > 0 ? $minRate / $rateFrom : $minRate;

                return [
                    'source' => 'local',
                    'hotel' => $hotel,
                    'price' => round($priceUsd, 2),
                ];
            });

        $propertyIds = $local
            ->pluck('exely_id')
            ->filter()
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

        $results = null;


        // ######## Emerging API ########

           $emerSearch = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
           $emerHotels = $emerSearch->EmergingGetHotels($request);
        //    dd($emerHotels['data']['hotels']);

           if( isset($emerHotels['data']['hotels']) ){

               $filteredHotels = array_filter($emerHotels['data']['hotels'], function ($hotel) {
                   return isset($hotel['localData']['id']);
               });
               // dd($filteredHotels);
               $hotels['hotels'] = array_map(function ($hotel) {
                   // dd($hotel);
                   $rate = $hotel['rates'][0];
                   $price = (float)$rate['payment_options']['payment_types'][0]['amount'] ?? 0;
                   $totalPrice = number_format( ($price * $this->coef) + $price , 2, '.', '');

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


        // ***** Start Tourmind API *****

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

        // ***** End Tourmind API *****


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
                }
            } catch (ConnectionException $e) {
                Log::error('Exely error: ' . $e->getMessage());
            }
        }

        $exelyHotels = collect($results->roomStays ?? [])->map(function ($roomStay) use ($fxRates) {
            $basePrice = $roomStay->total->priceBeforeTax ?? 0;
            $currency = $roomStay->currencyCode ?? 'USD';
            $rateFrom = $fxRates[$currency] ?? 1;
            $priceUsd = $rateFrom > 0 ? $basePrice / $rateFrom : $basePrice;

            return [
                'source' => 'exely',
                'roomStay' => $roomStay,
                'hotel' => Hotel::where('exely_id', $roomStay->propertyId)->first(),
                'price' => round($priceUsd, 2),
            ];
        });

        $allHotels = $localHotels->concat($exelyHotels)->sortBy('price')->values();

        //dd($fxRates);

        //dd($allHotels->pluck('price'));

        if ($request->sort === 'lowest_price') {
            $allHotels = $allHotels->sortBy('price')->values();
        } elseif ($request->sort === 'highest_price') {
            $allHotels = $allHotels->sortByDesc('price')->values();
        }


        return view('pages.search.search', [
            'allHotels' => $allHotels,
            'results' => $results,
            'fxBase' => $fxBase,
            'fxRates' => $fxRates,
            'request' => $request,
            'cities' => $cities,
        ]);
    }


    public function findHotel($code, Request $request)
    {
        $hotel = Hotel::where('code', $code)->first();
        $images = Image::where('hotel_id', $hotel->id)->get();
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

            if ($request->filled('meal') && is_array($request->meal)) {
                $q->whereIn('meal_id', $request->meal);
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
            return view('pages.search.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms', 'images'));
        } else {
            return view('pages.search.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms', 'images'));
        }
    }

    //exely
    public function findHotelExely(Request $request)
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
