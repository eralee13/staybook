<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\FXService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\City;
use App\Models\Image;
use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public $coef;

    public function __construct()
    {
        $this->coef = config('app.main_coef');
    }

    public function suggest(Request $request)
    {
        $q = Str::lower(trim((string)$request->get('q','')));
        if ($q === '') return response()->json([]);

        $countries = Country::selectRaw("id, name, alpha2, 'country' as type")
            ->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
            ->orWhere('alpha2', Str::upper($q))
            ->orWhere('code', Str::upper($q))
            ->limit(5)->get();

        $cities = City::selectRaw("id, title as name, country_code, 'city' as type")
            ->whereRaw('LOWER(title) LIKE ?', ["%{$q}%"])
            ->orWhereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
            ->orWhereRaw('LOWER(code) LIKE ?', ["%{$q}%"])
            ->limit(10)->get();

        return response()->json($countries->concat($cities)->values());
    }

    private function resolveLocation(string $q = null): array
    {
        $qLower = Str::lower($q ?? '');
        $country = null; $city = null;

        $norm2 = function (?string $code) {
            if (!$code) return null; $u = Str::upper($code);
            return $u === 'KGS' ? 'KG' : $u; // спец-кейс KGS→KG
        };

        // 1) Если ввели alpha2/alpha3/название страны
        if ($qLower !== '') {
            $country = Country::query()
                ->where('alpha2', $norm2($qLower))
                ->orWhere('code', Str::upper($qLower))
                ->orWhereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"])
                ->first();
        }

        // 2) Явный Бишкек (ru/en/ошибки ввода)
        $isBishkek = Str::contains($qLower, ['bishkek','бишкек','biskek','bishk']);
        if ($isBishkek) {
            $city = City::query()
                ->when($country, fn($w)=>$w->where('country_id',$country->id),
                    fn($w)=>$w->whereIn('country_code',['KG','KGS']))
                ->where(function($w){
                    $w->whereRaw('LOWER(title) = ?', ['bishkek'])
                        ->orWhereRaw('LOWER(title) = ?', ['бишкек'])
                        ->orWhereRaw('LOWER(name)  = ?', ['bishkek'])
                        ->orWhere('code', 'bishkek');
                })->first();

            if (!$city) {
                $city = City::whereIn('country_code',['KG','KGS'])
                    ->where(function($w) use ($qLower){
                        $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                            ->orWhereRaw('LOWER(name)  LIKE ?', ["%{$qLower}%"])
                            ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
                    })->first();
            }

            if ($city && !$country) $country = $city->country;
            return [$country,$city];
        }

        // 3) Если страна определена и строка похожа на город этой страны
        if ($country && $qLower !== '') {
            $city = City::where('country_id',$country->id)
                ->where(function($w) use ($qLower){
                    $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                        ->orWhereRaw('LOWER(name)  LIKE ?', ["%{$qLower}%"])
                        ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
                })->orderBy('title')->first();
        }

        // 4) Если страны нет — ищем город глобально
        if (!$city && $qLower !== '') {
            $city = City::where(function($w) use ($qLower){
                $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                    ->orWhereRaw('LOWER(name)  LIKE ?', ["%{$qLower}%"])
                    ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
            })->orderBy('title')->first();
            if ($city && !$country) $country = $city->country;
        }

        return [$country,$city];
    }

    public function search(Request $request)
    {
        $q = trim((string)$request->get('q',''));
        $country = null; $city = null;

        // --- резолвер ---
        [$country, $city] = $this->resolveLocation($q);

        // Отели строго по city_id (если нашли город)
        $hotels = collect();
        if ($city) {
            $hotels = Hotel::where('city_id', $city->id)->get();

            // fallback для старых данных без city_id — по строковому названию:
            if ($hotels->isEmpty()) {
                $hotels = Hotel::whereIn('city', array_filter([$city->title, $city->name, 'Bishkek','Бишкек']))->get();
            }
        }

        // Валюта/курсы
        $fxBase  = strtoupper(session('currency', 'USD'));
        $fxRates = array_change_key_case(app(\App\Services\FXService::class)->getRatesBaseCentral() ?? [], CASE_UPPER);
        $symbols = ['USD'=>'$','EUR'=>'€','KGS'=>'сом','KZT'=>'₸','RUB'=>'₽','TRY'=>'₺','GBP'=>'£','UZS'=>'сўм'];

        // Хелперы конвертации (заметьте: это просто присваивания переменных-замыканий — НИКАКИХ use-импортов тут!)
        $toUSD = function (float $amount, string $from) use ($fxRates): float {
            $from = strtoupper($from ?: 'USD');
            $kgsPerFrom = (float)($fxRates[$from] ?? 0);
            $kgsPerUSD  = (float)($fxRates['USD']  ?? 0);
            if ($kgsPerFrom <= 0 || $kgsPerUSD <= 0) return $amount;
            return $amount * $kgsPerFrom / $kgsPerUSD;
        };
        $usdTo = function (float $usd, string $to) use ($fxRates): float {
            $to = strtoupper($to ?: 'USD');
            $kgsPerUSD = (float)($fxRates['USD'] ?? 0);
            $kgsPerTo  = (float)($fxRates[$to]  ?? 0);
            if ($kgsPerUSD <= 0 || $kgsPerTo <= 0) return $usd;
            return $usd * $kgsPerUSD / $kgsPerTo;
        };

        // ---------- Гости/комнаты ----------
        $roomsInput   = \Illuminate\Support\Arr::wrap($request->input('rooms'));
        $totalAdults  = 0;
        $allChildAges = [];
        foreach ($roomsInput as $room) {
            $totalAdults += (int)\Illuminate\Support\Arr::get($room, 'adults', 0);
            foreach (\Illuminate\Support\Arr::wrap(\Illuminate\Support\Arr::get($room, 'childAges', [])) as $age) {
                $age = is_numeric($age) ? (int)$age : null;
                if ($age !== null && $age >= 0) $allChildAges[] = $age;
            }
        }

        $childAges = array_values($allChildAges);

        // ---------- Базовый запрос отелей ----------
        $hotelQuery = \App\Models\Hotel::with([
            'rates' => function ($q) use ($request, $totalAdults) {
                if ($request->filled('rooms')) {
                    $q->where('availability', '>=', $totalAdults);
                }
                if ($request->filled('meal')) {
                    $q->whereIn('meal_id', \Illuminate\Support\Arr::wrap($request->input('meal')));
                }
                if ($request->filled('start_d') && $request->filled('end_d')) {
                    $start = $request->input('start_d');
                    $end   = $request->input('end_d');
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
        if (\Illuminate\Support\Facades\Schema::hasColumn('hotels', 'city_id')) {
            if ($cityId) {
                $hotelQuery->where('city_id', (int)$cityId);
            } elseif ($request->filled('city')) {
                $rawCity       = (string)$request->input('city', '');
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
                $cityTitle = \App\Models\City::whereKey($cityId)->value('title');
                if ($cityTitle) {
                    $cityLc = mb_strtolower($cityTitle);
                    $hotelQuery->whereRaw('LOWER(city) LIKE ?', ["%{$cityLc}%"]);
                }
            } elseif ($request->filled('city')) {
                $rawCity       = (string)$request->input('city', '');
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

        // ---------- Локальные отели -> в USD ----------
        $local = $hotelQuery->where('status', 1)->get();

        $localHotels = $local
            ->filter(fn($hotel) => empty($hotel->exely_id))
            ->map(function ($hotel) use ($fxRates) {
                // минимальная цена среди price/price2/price3
                $minRate = 0.0;
                if ($hotel->rates && $hotel->rates->count()) {
                    $minRate = $hotel->rates->map(function($r){
                        $candidates = [];
                        foreach (['price','price2','price3'] as $field) {
                            if (isset($r->$field) && is_numeric($r->$field) && $r->$field > 0) {
                                $candidates[] = (float)$r->$field;
                            }
                        }
                        return count($candidates) ? min($candidates) : null;
                    })->filter()->min() ?? 0.0;
                }

                // валюта тарифа (у первого), приводим к USD
                $currency   = strtoupper($hotel->rates?->first()?->currency ?? 'USD');
                $kgsPerFrom = (float)($fxRates[$currency] ?? 0.0);
                $kgsPerUSD  = (float)($fxRates['USD']     ?? 0.0);
                $priceUsd   = ($kgsPerFrom > 0 && $kgsPerUSD > 0)
                    ? ($minRate * $kgsPerFrom / $kgsPerUSD)
                    : $minRate;

                return [
                    'source' => 'local',
                    'hotel'  => $hotel,
                    'price'  => round($priceUsd, 2), // USD
                ];
            });

        // ---------- Exely ----------
        $propertyIds = $local->pluck('exely_id')
            ->filter(fn($v) => filled($v))
            ->map(fn($id) => (string)$id)
            ->unique()
            ->values()
            ->all();

        $results = null;
        if (!empty($propertyIds)) {
            try {
                $payload = [
                    'propertyIds'   => array_values($propertyIds),
                    'adults'        => (int)$totalAdults,
                    'childAges'     => array_values($allChildAges),
                    'arrivalDate'   => (string)$request->input('arrivalDate', now()->format('Y-m-d')),
                    'departureDate' => (string)$request->input('departureDate', now()->addDay()->format('Y-m-d')),
                ];

                $response = \Illuminate\Support\Facades\Http::timeout(30)
                    ->connectTimeout(5)
                    ->retry(2, 100)
                    ->accept('application/json')
                    ->withHeaders(['x-api-key' => (string)config('services.exely.key')])
                    ->post(rtrim((string)config('services.exely.base_url'), '/') . '/search/v1/properties/room-stays/search', $payload);

                if ($response->successful()) {
                    $results = $response->object();
                } else {
                    \Illuminate\Support\Facades\Log::warning('Exely search failed', [
                        'status'  => $response->status(),
                        'payload' => $payload,
                        'body'    => $response->body(),
                    ]);
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                \Illuminate\Support\Facades\Log::error('Exely connection error: ' . $e->getMessage());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Exely unexpected error: ' . $e->getMessage());
            }
        }

        $exelyRoomStays = collect(data_get($results, 'roomStays', []));
        $exelyHotels = $exelyRoomStays->map(function ($roomStay) use ($toUSD) {
            $basePrice = (float)data_get($roomStay, 'total.priceBeforeTax', 0);
            $currency  = (string)(data_get($roomStay, 'currencyCode', 'USD') ?? 'USD');
            $priceUsd  = round($toUSD($basePrice / 0.92, $currency), 2);
           // $priceUsd = round($priceUsd / 0.92);

            $propertyId = data_get($roomStay, 'propertyId');
            $hotelModel = $propertyId ? \App\Models\Hotel::where('exely_id', $propertyId)->first() : null;

            return [
                'source'   => 'exely',
                'roomStay' => $roomStay,
                'hotel'    => $hotelModel,
                'price'    => $priceUsd, // USD
            ];
        });

        // ---------- Tourmind через ваш сервис ----------
        $tmHotels = collect();
        try {
            $hotelService = new \App\Services\Tourmind\HotelServices();
            $tm = $hotelService->tmGetHotels($request); // уже с localData, заранее отфильтрованный/схлопнутый

            if (!empty($tm['Hotels']) && is_array($tm['Hotels'])) {
                $tmHotels = collect($tm['Hotels'])->map(function (array $h) use ($toUSD, $usdTo, $fxBase, $symbols) {
                    $rt    = $h['RoomTypes'][0] ?? [];
                    $rate  = $rt['RateInfos'][0] ?? [];
                    $total = (float)($rate['TotalPrice'] ?? 0);
                    $ccy   = (string)($rate['CurrencyCode'] ?? 'USD');

                    $priceUsd = round($toUSD($total, $ccy), 2);

                    $local = $h['localData'] ?? null;

                    // ВАЖНО: НЕ задаём 'hotel' (Eloquent) — чтобы TM не сливался с local
                    return [
                        'source'      => 'tm',
                        'tm'          => (object)[
                            'hid'      => $local['id']   ?? null,
                            'code'     => $local['code'] ?? null,
                            'title'    => $local['title'] ?? ($h['HotelName'] ?? ''),
                            'title_en' => $local['title_en'] ?? '',
                            'rating'   => $local['rating'] ?? '',
                            'city'     => $local['city'] ?? '',
                            'images'   => $local['images'] ?? [],
                            'lat'      => $local['lat'] ?? null,
                            'lng'      => $local['lng'] ?? null,
                        ],
                        'hotel'       => null,                 // принципиально null
                        'price'       => $priceUsd,           // USD
                        'conv_total'  => (float)round($usdTo($priceUsd, $fxBase)),
                        'conv_symbol' => $symbols[$fxBase] ?? $fxBase,
                    ];
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::channel('Tourmind')->error('TM in Search error', ['msg' => $e->getMessage()]);
        }

        // ---------- Объединяем ----------
        $all = $localHotels->concat($exelyHotels)->concat($tmHotels)->values();

        // ---------- Схлопываем до 1 записи на отель (с учётом источника) ----------
        $byHotel = $all->groupBy(function ($item) {
            $src = data_get($item, 'source');

            if ($src === 'tm') {
                // Привязываем к локальному id (если есть) или к TM-коду — но с префиксом tm:
                $tmKey = (string)(data_get($item, 'tm.hid') ?: data_get($item, 'tm.code'));
                return 'tm:' . $tmKey;
            }

            if ($src === 'exely') {
                $pid = (string)data_get($item, 'roomStay.propertyId');
                return 'exely:' . $pid;
            }

            // local по id модели
            $hid = (string)data_get($item, 'hotel.id');
            return 'local:' . $hid;
        });

        $hotelsMin = $byHotel->map(function ($items) use ($usdTo, $fxBase, $symbols) {
            $best = $items->sortBy('price', SORT_NUMERIC)->first(); // USD
            $usd  = (float)data_get($best, 'price', 0);

            // Если уже проставлены conv_* (TM-блок) — используем их, иначе считаем из USD
            $conv   = data_get($best, 'conv_total');
            $symbol = data_get($best, 'conv_symbol');
            if ($conv === null) {
                $conv   = round($usdTo($usd, $fxBase));
                $symbol = $symbols[$fxBase] ?? $fxBase;
            }

            return [
                'source'      => data_get($best, 'source'),
                'hotel'       => data_get($best, 'hotel'),
                'roomStay'    => data_get($best, 'roomStay'),
                'tm'          => data_get($best, 'tm'),
                'price'       => (float)$usd,  // USD (для сортировки)
                'min_price'   => (float)$usd,  // алиас
                'conv_total'  => (float)$conv,
                'conv_symbol' => (string)$symbol,
            ];
        })->values();

        // ---------- Поиск по названию ----------
        if ($request->filled('title')) {
            $needle = mb_strtolower((string)$request->input('title'));
            $hotelsMin = $hotelsMin->filter(function ($item) use ($needle) {
                $title    = mb_strtolower((string)data_get($item, 'hotel.title', data_get($item, 'tm.title', '')));
                $title_en = mb_strtolower((string)data_get($item, 'hotel.title_en', data_get($item, 'tm.title_en', '')));
                return str_contains($title, $needle) || str_contains($title_en, $needle);
            })->values();
        }

        // ---------- Сортировки ----------
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

        // ---------- Рендер ----------
        return view('pages.search.search', [
            'allHotels' => $hotelsMin,   // единый список: один элемент на отель (по источнику)
            'fxBase'    => $fxBase,
            'fxRates'   => $fxRates,
            'request'   => $request,
            'cities'    => $cities,
            'childAges' => $childAges,
            'q'               => $q,
            'countries'       => $countries,
            'hotels'          => $hotels,
            'selectedCountry' => $country?->alpha2 ?? $country?->code,
            'selectedCity'    => $city?->id,
        ]);
    }


    public function findHotel($code, Request $request)
    {
        $hotel   = Hotel::where('code', $code)->firstOrFail();
        $images  = Image::where('hotel_id', $hotel->id)->get();

        $arrival    = Carbon::parse($request->arrivalDate);
        $departure  = Carbon::parse($request->departureDate);
        $count_day  = $arrival->diffInDays($departure);
        $adult      = (int)($request->adult ?? 1);

        $startTime = (string)$request->arrivalDate;
        $endTime   = (string)$request->departureDate;

        // функция-предикат пересечения дат для переиспользования в замыканиях
        $overlap = function ($q) use ($startTime, $endTime) {
            $q->where(function ($ov) use ($startTime, $endTime) {
                $ov->whereBetween('arrivalDate', [$startTime, $endTime])
                    ->orWhereBetween('departureDate', [$startTime, $endTime])
                    ->orWhere(function ($qq) use ($startTime, $endTime) {
                        $qq->where('arrivalDate', '<=', $startTime)
                            ->where('departureDate', '>=', $endTime);
                    });
            });
        };

        $rooms = Room::query()
            ->where('hotel_id', $hotel->id)
            ->with([
                'rates' => function ($q) use ($request, $overlap, $startTime, $endTime) {
                    // фильтр по питанию (если пришёл)
                    if ($request->filled('meal') && is_array($request->meal)) {
                        $q->whereIn('meal_id', $request->meal);
                    }

                    // исключить пересечения с реальными бронированиями (reserved)
                    if ($startTime && $endTime) {
                        $q->whereDoesntHave('bookings', function ($b) use ($overlap) {
                            $overlap($b);
                        });
                    }

                    // подгружаем ПО ОДНОЙ «последней по id» записи для квоты и для цены,
                    // но только среди тех, что пересекают выбранный интервал
                    $q->with([
                        'latestAllotment' => function ($b) use ($overlap) { $overlap($b); },
                        'latestPrice'     => function ($b) use ($overlap) { $overlap($b); },
                    ]);
                }
            ])
            ->get();

        // постобработка: применяем квоты и цены из календаря, сортируем
        $rooms = $rooms->map(function ($room) use ($adult) {
            // сначала преобразуем тарифы
            $filteredRates = $room->rates->map(function ($rate) use ($adult) {
                // 1) доступность по квоте
                $quota = optional($rate->latestAllotment)->adult; // null | int
                $passesQuota =
                    is_null($quota)               // нет записи — не ограничиваем
                    || ($quota > 0 && $quota >= $adult);

                // 2) актуальная цена
                $calendarPrice = optional($rate->latestPrice)->price;
                $effective = (is_numeric($calendarPrice) && $calendarPrice > 0)
                    ? (float)$calendarPrice
                    : (float)$rate->price;

                // проставим для шаблона
                $rate->effective_price = $effective;
                $rate->passes_quota = $passesQuota;

                return $rate;
            })
                // фильтруем по квоте ТОЛЬКО здесь (без WHERE в SQL)
                ->filter(fn($r) => $r->passes_quota)
                // сортируем визуально по актуальной цене
                ->sortBy('effective_price', SORT_NUMERIC)
                ->values();

            $room->rates = $filteredRates;
            $room->min_effective_price = $filteredRates->min('effective_price');

            return $room;
        })
            // оставляем комнаты, где остались тарифы
            ->filter(fn ($room) => $room->rates->isNotEmpty())
            // сортируем комнаты по минимальной цене тарифа
            ->sortBy('min_effective_price', SORT_NUMERIC)
            ->values();

        return view('pages.search.hotel', compact(
            'hotel','arrival','departure','adult','count_day','request','rooms','images'
        ));
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



        return view('pages.search.searсh', compact('hotel', 'arrival', 'departure', 'request', 'roomAmenity', 'tmroom', 'tmimages', 'meals'));
    }

    // Emerging
//    public function hotel_etg($hid, Request $request)
//    {
//        $hotel = Hotel::where('id', $hid)->with(['amenity'])->first();
//        $room = Room::where('hotel_id', $hid)->get(['amenities'])->first();
//        $amenities = explode(',', $room->amenities ?? '');
//        $roomAmenity = array_slice($amenities, 0, 8);
//        $meals = Meal::pluck('title', 'id');
//        $arrival = Carbon::createFromDate($request->arrivalDate);
//        $departure = Carbon::createFromDate($request->departureDate);
//
//        $emergingSearch = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
//        $etgroom = $emergingSearch->searchRates($request, $hotel->id);
//        // dd($etgroom);
//        $tmimages = Image::where('hotel_id', $hotel->id)->where('caption', 'guest_rooms')->get('image');
//
//        $city = City::where('title', $hotel->city)->first(['country_code']);
//
//        if (!$hotel->utc && $city && ($utc = $hotelService->getUtcOffsetByCountryCode($city->country_code))) {
//            $hotel->utc = $utc;
//            $hotel->save();
//        }
//
//        return view('pages.search.emerging.hotel', compact('hotel', 'arrival', 'departure', 'request', 'roomAmenity', 'etgroom', 'tmimages', 'meals'));
//    }

}
