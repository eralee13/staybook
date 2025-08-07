<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Image;
use App\Models\Room;
use App\Models\Hotel;
use App\Services\FXService;
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

        $local = $hotelQuery->where('status', 1)->get();
        $localHotels = $local
            ->filter(fn($hotel) => empty($hotel->exely_id))
            ->map(function ($hotel) use ($fxRates) {
                $minRate = (float) $hotel->rates->min('price') ?? 0;
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

        if ($request->filled('title')) {
            $search = mb_strtolower($request->title);

            $allHotels = $allHotels->filter(function ($item) use ($search) {
                $title = $item['hotel']->title ?? '';
                return str_contains(mb_strtolower($title), $search);
            })->values();
        }


//        if ($request->filled('title')) {
//            $hotelQuery->where('title', 'like', '%' . $request->title . '%');
//        }

        if ($request->sort === 'lowest_price') {
            $allHotels = $allHotels->sortBy('price')->values();
        } elseif ($request->sort === 'highest_price') {
            $allHotels = $allHotels->sortByDesc('price')->values();
        }
//        } elseif ($request->sort === 'title_asc') {
//            $allHotels = $allHotels->sortBy(fn($h) => mb_strtolower($h['hotel']->title ?? ''))->values();
//        }
//        elseif ($request->sort === 'title_desc') {
//            $allHotels = $allHotels->sortByDesc(fn($h) => mb_strtolower($h['hotel']->title ?? ''))->values();
//        }

        return view('pages.search.search', [
            'allHotels' => $allHotels,
            'fxBase' => $fxBase,
            'fxRates' => $fxRates,
            'request' => $request,
            'cities' => $cities,
        ]);
    }


    public function findHotel($code, Request $request)
    {
        $cities = City::whereNull('country_id')->orderBy('title')->get();
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

            $q->orderBy('price', 'asc'); // сортировка тарифов внутри комнаты
        }])
            ->where('hotel_id', $hotel->id)
            ->withMin('rates', 'price') // добавляем минимальную цену тарифа
            ->orderBy('rates_min_price', 'asc'); // сортировка комнат по минимальной цене тарифа

        $rooms = $query->get()->filter(fn($room) => $room->rates->isNotEmpty());

        return view('pages.search.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms', 'images', 'cities'));
    }

    //exely
    public function findHotelExely(Request $request)
    {
        $cities = City::whereNull('country_id')->orderBy('title')->get();
        // ✅ Валидация входных параметров
        $request->validate([
            'propertyId' => 'required|string',
            'arrivalDate' => 'required|date',
            'departureDate' => 'required|date|after:arrivalDate',
            'adultCount' => 'required|integer|min:1',
            'childAges' => 'nullable|array',
        ]);

        // ✅ Очистка массива childAges от пустых значений
        $childAgesInput = (array) $request->input('childAges', []);
        $childs = collect($childAgesInput)
            ->flatMap(fn($ageString) => explode(',', $ageString)) // разбиваем строку "2, 3" на ["2", " 3"]
            ->map(fn($age) => (int) trim($age))                   // убираем пробелы и делаем числа
            ->filter(fn($age) => $age > 0)                        // фильтруем пустые/нулевые
            ->values()                                            // пересобираем индексы
            ->toArray();

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

        $hotel = Hotel::where('exely_id', $request->propertyId)->first();
        $images = Image::where('hotel_id', $hotel->id)->get();

        return view('pages.search.exely.hotel', compact('rooms', 'request', 'images'));
    }
}
