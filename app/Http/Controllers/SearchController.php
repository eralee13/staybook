<?php

namespace App\Http\Controllers;

use App\Services\FXService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\City;
use App\Models\Image;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Meal;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    public $coef;

    public function __construct()
    {
        $this->coef = config('app.main_coef');
    }

    /**
     * AJAX-подсказки: города и отели (top-5 + top-5)
     * GET /api/suggest?q=би
     */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['items' => []]);
        }

        $qLc  = mb_strtolower($q);
        $like = '%'.$qLc.'%';

        $cityHasTitleEn  = Schema::hasColumn('cities', 'title_en');
        $hotelHasTitleEn = Schema::hasColumn('hotels', 'title_en');
        $hotelHasCityCol = Schema::hasColumn('hotels', 'city');

        // Города
        $citySelect = ['id','title'];
        if ($cityHasTitleEn) $citySelect[] = 'title_en';

        $cities = City::query()->select($citySelect)
            ->whereRaw('LOWER(title) LIKE ?', [$like])
            ->when($cityHasTitleEn, fn($q) => $q->orWhereRaw('LOWER(title_en) LIKE ?', [$like]))
            ->limit(5)
            ->get()
            ->map(fn($c) => [
                'type'    => 'city',
                'label'   => $c->title,
                'alt'     => $cityHasTitleEn ? ($c->title_en ?? null) : null,
                'city'    => null,
                'rating'  => null,
                'city_id' => $c->id,
                'url'     => route('search', ['city_id' => $c->id]),
            ]);

        // Отели
        $hotelSelect = ['id','title','status','rating'];
        if ($hotelHasTitleEn) $hotelSelect[] = 'title_en';
        if ($hotelHasCityCol) $hotelSelect[] = 'city';

        $hotels = Hotel::query()->select($hotelSelect)
            ->where('status', 1)
            ->where(function($q) use ($like, $hotelHasTitleEn, $hotelHasCityCol) {
                $q->whereRaw('LOWER(title) LIKE ?', [$like]);
                if ($hotelHasTitleEn) $q->orWhereRaw('LOWER(title_en) LIKE ?', [$like]);
                if ($hotelHasCityCol) $q->orWhereRaw('LOWER(city) LIKE ?', [$like]);
            })
            ->limit(10)
            ->get()
            ->map(function($h) use ($hotelHasTitleEn, $hotelHasCityCol) {

                // Гибкий выбор роута показа отеля
                if (Route::has('hotel.show')) {
                    $url = route('hotel.show', $h->id);
                } elseif (Route::has('hotels.show')) {
                    $url = route('hotels.show', $h->id);
                } elseif (Route::has('hotel')) {
                    $url = route('hotel', $h->id);
                } elseif (Route::has('hotels.view')) {
                    $url = route('hotels.view', $h->id);
                } else {
                    $url = url('/hotel/'.$h->id); // запасной вариант
                }

                return [
                    'type'   => 'hotel',
                    'label'  => $h->title,
                    'alt'    => $hotelHasTitleEn ? ($h->title_en ?? null) : null,
                    'city'   => $hotelHasCityCol ? ($h->city ?? null) : null,
                    'rating' => $h->rating,
                    'url'    => $url,
                ];
            });

        return response()->json([
            'items' => $cities->concat($hotels)->values(),
        ]);
    }

    /**
     * Умный поиск: по одному полю q принимаем и город, и отель
     * + даты + гости (rooms[]), фильтры (рейтинг, питание)
     * Объединяем: локальные + Exely (+ при желании Tourmind)
     */
    public function search(Request $request)
    {
        // Города для фильтра/подсказок
        $cities = City::whereNull('country_id')->orderBy('title')->get();

        // Валюта/курсы
        $fxBase = session('currency', 'USD');
        $fxRates = app(\App\Services\FXService::class)->getRatesBaseCentral() ?? [];

        // ---------- Гости/комнаты: безопасный парс ----------
        $roomsInput = Arr::wrap($request->input('rooms')); // [] если null/скаляр
        $totalAdults = 0;
        $allChildAges = [];

        foreach ($roomsInput as $room) {
            $totalAdults += (int)Arr::get($room, 'adults', 0);

            $childAges = Arr::get($room, 'childAges', []);
            foreach (Arr::wrap($childAges) as $age) {
                $age = is_numeric($age) ? (int)$age : null;
                if ($age !== null && $age >= 0) {
                    $allChildAges[] = $age;
                }
            }
        }

        // ---------- Базовый запрос отелей ----------
        $hotelQuery = Hotel::with([
            'rates' => function ($q) use ($request, $totalAdults) {
                if ($request->filled('rooms')) {
                    $q->where('availability', '>=', $totalAdults);
                }
                if ($request->filled('meal')) {
                    $q->whereIn('meal_id', Arr::wrap($request->input('meal')));
                }
                if ($request->filled('start_d') && $request->filled('end_d')) {
                    $start = $request->input('start_d');
                    $end = $request->input('end_d');
                    $q->whereDoesntHave('bookings', function ($b) use ($start, $end) {
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
            },
            'city'
        ]);

        // ---------- Фильтр по городу ----------
        $cityId = $request->input('city_id');

        if (Schema::hasColumn('hotels', 'city_id')) {
            if ($cityId) {
                $hotelQuery->where('city_id', (int)$cityId);
            } elseif ($request->filled('city')) {
                $rawCity = (string)$request->input('city', '');
                $cityForSearch = trim(preg_replace('/^\s*\d+\s*-\s*/u', '', $rawCity));
                if ($cityForSearch !== '') {
                    $cityLc = mb_strtolower($cityForSearch);
                    $hotelQuery->where(function ($q) use ($cityLc) {
                        $q->whereHas('city', function ($cq) use ($cityLc) {
                            $cq->whereRaw('LOWER(title) LIKE ?', ["%{$cityLc}%"])
                                ->orWhereRaw('LOWER(title_en) LIKE ?', ["%{$cityLc}%"]);
                        })
                            ->orWhereRaw('LOWER(city) LIKE ?', ["%{$cityLc}%"])
                            ->orWhereRaw('LOWER(title) LIKE ?', ["%{$cityLc}%"])
                            ->orWhereRaw('LOWER(title_en) LIKE ?', ["%{$cityLc}%"]);
                    });
                }
            }
        } else {
            if ($cityId) {
                $cityTitle = City::whereKey($cityId)->value('title');
                if ($cityTitle) {
                    $cityLc = mb_strtolower($cityTitle);
                    $hotelQuery->whereRaw('LOWER(city) LIKE ?', ["%{$cityLc}%"]);
                }
            } elseif ($request->filled('city')) {
                $rawCity = (string)$request->input('city', '');
                $cityForSearch = trim(preg_replace('/^\s*\d+\s*-\s*/u', '', $rawCity));
                if ($cityForSearch !== '') {
                    $cityLc = mb_strtolower($cityForSearch);
                    $hotelQuery->where(function ($q) use ($cityLc) {
                        $q->whereRaw('LOWER(city) LIKE ?', ["%{$cityLc}%"])
                            ->orWhereRaw('LOWER(title) LIKE ?', ["%{$cityLc}%"])
                            ->orWhereRaw('LOWER(title_en) LIKE ?', ["%{$cityLc}%"]);
                    });
                }
            }
        }

        // ---------- Фильтр по рейтингу ----------
        if ($request->filled('rating')) {
            $hotelQuery->where('rating', '>=', (int)$request->input('rating'));
        }

        // ---------- Локальные отели ----------
        $local = $hotelQuery->where('status', 1)->get();

        $localHotels = $local
            ->filter(fn($hotel) => empty($hotel->exely_id)) // только не-Exely
            ->map(function ($hotel) use ($fxRates) {
                $minRate = (float)(($hotel->rates?->min('price')) ?? 0.0);
                $currency = $hotel->rates?->first()?->currency ?? 'USD';
                $rateFrom = (float)($fxRates[$currency] ?? 1.0);
                $priceUsd = $rateFrom > 0 ? $minRate / $rateFrom : $minRate;

                return [
                    'source' => 'local',
                    'hotel' => $hotel,
                    'price' => round($priceUsd, 2), // приводим к USD для единообразной сортировки
                ];
            });

        // ---------- Подготовка propertyIds для Exely ----------
        $propertyIds = $local
            ->pluck('exely_id')
            ->filter(fn($v) => filled($v))
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

        // ---------- Запрос в Exely ----------
        $results = null;
        if (!empty($propertyIds)) {
            try {
                $payload = [
                    'propertyIds' => array_values($propertyIds),
                    'adults' => (int)$totalAdults,
                    'childAges' => array_values($allChildAges),
                    'arrivalDate' => (string)$request->input('arrivalDate', now()->format('Y-m-d')),
                    'departureDate' => (string)$request->input('departureDate', now()->addDay()->format('Y-m-d')),
                ];

                $response = Http::timeout(30)
                    ->connectTimeout(5)
                    ->retry(2, 100)
                    ->accept('application/json')
                    ->withHeaders(['x-api-key' => (string)config('services.exely.key')])
                    ->post(rtrim((string)config('services.exely.base_url'), '/') . '/search/v1/properties/room-stays/search', $payload);

                if ($response->successful()) {
                    $results = $response->object();
                } else {
                    Log::warning('Exely search failed', [
                        'status' => $response->status(),
                        'payload' => $payload,
                        'body' => $response->body(),
                    ]);
                }
            } catch (ConnectionException $e) {
                Log::error('Exely connection error: ' . $e->getMessage());
            } catch (\Throwable $e) {
                Log::error('Exely unexpected error: ' . $e->getMessage());
            }
        }

        // ---------- Нормализуем Exely-ответ ----------
        $exelyRoomStays = collect(data_get($results, 'roomStays', []));
        $exelyHotels = $exelyRoomStays->map(function ($roomStay) use ($fxRates) {
            $basePrice = (float)data_get($roomStay, 'total.priceBeforeTax', 0);
            $currency = (string)(data_get($roomStay, 'currencyCode', 'USD') ?? 'USD');
            $rateFrom = (float)($fxRates[$currency] ?? 1.0);
            $priceUsd = $rateFrom > 0 ? $basePrice / $rateFrom : $basePrice;

            $propertyId = data_get($roomStay, 'propertyId');
            $hotelModel = $propertyId ? Hotel::where('exely_id', $propertyId)->first() : null;

            return [
                'source' => 'exely',
                'roomStay' => $roomStay,
                'hotel' => $hotelModel,
                'price' => round($priceUsd, 2), // к USD
            ];
        });

        // ---------- Объединяем ----------
        $allHotels = $localHotels->concat($exelyHotels)->values();

        // ---------- Схлопываем до 1 записи на отель с минимальной ценой ----------
        $byHotel = $allHotels->groupBy(function ($item) {
            $hid = data_get($item, 'hotel.id');
            return $hid ? 'local:' . $hid : 'exely:' . (string)data_get($item, 'roomStay.propertyId');
        });

        $hotelsMin = $byHotel->map(function ($items) {
            $best = $items->sortBy('price', SORT_NUMERIC)->first();
            return [
                'source' => data_get($best, 'source'),
                'hotel' => data_get($best, 'hotel'),
                'roomStay' => data_get($best, 'roomStay'),
                'price' => (float)data_get($best, 'price', 0), // USD
                'min_price' => (float)data_get($best, 'price', 0), // алиас
            ];
        })->values();

        // ---------- Поиск по названию ----------
        if ($request->filled('title')) {
            $needle = mb_strtolower((string)$request->input('title'));
            $hotelsMin = $hotelsMin->filter(function ($item) use ($needle) {
                $title = mb_strtolower((string)data_get($item, 'hotel.title', ''));
                $title_en = mb_strtolower((string)data_get($item, 'hotel.title_en', ''));
                return str_contains($title, $needle) || str_contains($title_en, $needle);
            })->values();
        }

        // ---------- Сортировки (по умолчанию: lowest_price) ----------
        $sort = (string)$request->input('sort', 'lowest_price');
        switch ($sort) {
            case 'highest_price':
                $hotelsMin = $hotelsMin->sortByDesc('min_price', SORT_NUMERIC)->values();
                break;
            case 'lowest_price':
            default:
                $hotelsMin = $hotelsMin->sortBy('min_price', SORT_NUMERIC)->values();
                break;
        }

        // Рендер
        return view('pages.search.search', [
            'allHotels' => $hotelsMin, // уже один элемент на отель, отсортировано
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
        $request->validate([
            'propertyId'    => 'required|string',
            'arrivalDate'   => 'required|date',
            'departureDate' => 'required|date|after:arrivalDate',
            'adultCount'    => 'required|integer|min:1',
            'childAges'     => 'nullable|array',
        ]);

        // Надёжно собираем массив возрастов детей
        $childs = collect(Arr::wrap($request->input('childAges')))
            ->flatten()
            ->filter(fn($v) => is_scalar($v))     // отбрасываем вложенные массивы
            ->map(fn($v) => trim((string)$v))
            ->filter(fn($v) => $v !== '' && is_numeric($v))
            ->map(fn($v) => (int)$v)
            ->filter(fn($v) => $v >= 0)
            ->values()
            ->all();

        $params = [
            'arrivalDate'          => $request->arrivalDate,
            'departureDate'        => $request->departureDate,
            'adults'               => (int) $request->adultCount,
            'includeExtraStays'    => 'false',
            'includeExtraServices' => 'false',
        ];

        // Строим query
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        foreach ($childs as $age) {
            $query .= '&childAges=' . rawurlencode((string)$age);
        }

        $url = rtrim(config('services.exely.base_url'), '/') .
            "/search/v1/properties/{$request->propertyId}/room-stays?{$query}";

        $response = Http::withHeaders([
            'x-api-key' => config('services.exely.key'),
            'accept'    => 'application/json',
        ])->get($url);

        // Получаем как МАССИВ (уменьшаем шанс словить null/объекты)
        $payload = $response->json() ?? [];

        // Забираем roomStays безопасно, гарантируем массив
        $roomStays = data_get($payload, 'roomStays', []);
        if (!is_array($roomStays)) {
            $roomStays = [];
        }

        // На всякий случай нормализуем вложенные коллекции комнат
        $rooms = collect($roomStays)
            ->map(function ($r) {
                // Если где-то дальше во вьюхе делают array_filter($r['rates']), защитим это тут
                $r['rates']      = isset($r['rates']) && is_array($r['rates']) ? $r['rates'] : [];
                $r['amenities']  = isset($r['amenities']) && is_array($r['amenities']) ? $r['amenities'] : [];
                $r['images']     = isset($r['images']) && is_array($r['images']) ? $r['images'] : [];
                return $r;
            })
            ->sortBy('total.priceBeforeTax')
            ->values()
            ->all();

        if (empty($rooms)) {
            Log::warning('Exely: Нет roomStays или они пустые', [
                'status'   => $response->status(),
                'url'      => $url,
                // Логируем только верхний уровень, чтобы не раздувать логи
                'keys'     => array_keys($payload),
                'roomStays_type' => gettype(data_get($payload, 'roomStays')),
            ]);
        }

        return view('pages.search.exely.hotel', [
            'rooms'   => $rooms,     // точно массив
            'request' => $request,
        ]);
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
