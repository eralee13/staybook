<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\City;
use App\Models\Image;
use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public $coef;

    public function search(Request $request)
    {
        \Log::debug('SEARCH START', [
            'q'           => $request->input('q'),
            'city_id'     => $request->input('city_id'),
            'hotel_id'    => $request->input('hotel_id'),
            'region_id'   => $request->input('region_id'),
            'arrivalDate' => $request->input('arrivalDate'),
            'departureDate' => $request->input('departureDate'),
            'residency'   => $request->input('residency'),
            'rooms'       => $request->input('rooms'),
        ]);

        // ------ INPUT ------
        $hotelId = (int)$request->input('hotel_id');
        $q       = trim((string)($request->input('q', $request->input('city', ''))));
        $rating  = (int)$request->input('rating', 0);

        $arrival = (string)$request->input('arrivalDate', now()->format('Y-m-d'));
        $depart  = (string)$request->input('departureDate', now()->addDay()->format('Y-m-d'));

        // ------- ROOMS -------
        $rooms        = $request->input('rooms', []);
        $totalAdults  = 0;
        $allChildAges = [];

        foreach ($rooms as $r) {
            $totalAdults += (int)($r['adults'] ?? 0);
            foreach ((array)($r['childAges'] ?? []) as $age) {
                if ($age !== '' && $age !== null) {
                    $allChildAges[] = (int)$age;
                }
            }
        }
        if ($totalAdults <= 0) {
            $totalAdults = 1;
        }

        // ------- NIGHTS -------
        try {
            $nights = max(
                1,
                \Carbon\Carbon::parse($arrival)->diffInDays(\Carbon\Carbon::parse($depart))
            );
        } catch (\Throwable $e) {
            $nights = 1;
        }

        // ------- CURRENCY -------
        $fxBase  = strtoupper((string)session('currency', 'USD'));
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'RUB' => '₽',
            'KGS' => 'сом',
            'KZT' => '₸',
        ];

        $ratesTable = [
            'USD' => 1.00,
            'EUR' => 0.92,
            'RUB' => 93.0,
            'KGS' => 87.5,
            'KZT' => 480.0,
        ];

        $convert = function (float $amount, string $from, string $to) use ($ratesTable): float {
            $from = strtoupper($from);
            $to   = strtoupper($to);

            if (!isset($ratesTable[$from], $ratesTable[$to]) || $ratesTable[$from] == 0.0) {
                return round($amount, 2);
            }

            return round($amount / $ratesTable[$from] * $ratesTable[$to], 2);
        };

        // =====================================================================
        // 1) --- ОДИН КОНКРЕТНЫЙ ОТЕЛЬ ---
        // =====================================================================
        if ($hotelId > 0) {
            /** @var \App\Models\Hotel|null $h */
            $h = \App\Models\Hotel::find($hotelId);

            if (!$h) {
                return view('pages.search.search', [
                    'allHotels' => collect(),
                    'request'   => $request,
                ]);
            }

            // Локальный тариф
            $rate = \App\Models\Rate::query()
                ->where('hotel_id', $h->id)
                ->orderBy('price')
                ->first();

            $pricePerNight = (float)($rate->price ?? 0);
            $totalLocal    = $pricePerNight * $nights;

            $srcCurrency = strtoupper($rate->currency ?? 'USD');
            $convPrice   = $convert($totalLocal, $srcCurrency, $fxBase);
            $convSymbol  = $symbols[$fxBase] ?? $fxBase;

            $localItems = collect([[
                'source'      => 'local',
                'apiHotelId'  => null,
                'hotel'       => $h,
                'roomStay'    => null,
                'price'       => $totalLocal,
                'conv_total'  => $convPrice,
                'conv_symbol' => $convSymbol,
            ]]);

            // ——— EXELY по одному отелю
            $exelyItems = collect();
            if (filled($h->exely_id)) {

                $exelyItems = $this->fetchExelyItems(
                    [$h->exely_id],
                    $totalAdults,
                    $allChildAges,
                    $arrival,
                    $depart,
                    $symbols,
                    $fxBase,
                    $convert
                );

                // приведём к общей структуре
                $exelyItems = $exelyItems->map(function ($item) use ($h) {
                    $item['source'] = 'exely';
                    $item['hotel']  = $h; // важно!
                    return $item;
                });
            }

            return view('pages.search.search', [
                'allHotels' => $localItems->concat($exelyItems)->values(),
                'request'   => $request,
            ]);
        }

        // =====================================================================
        // 2) --- ОБЩИЙ ПОИСК ПО БД ОТЕЛЕЙ ---
        // =====================================================================
        $hotelsQ = \App\Models\Hotel::query();

        if ($q !== '') {
            $len = mb_strlen($q);

            if ($len >= 3) {
                $hotelsQ->whereRaw(
                    "MATCH(title, title_en, city) AGAINST (? IN BOOLEAN MODE)",
                    [$q . '*']
                );
            } else {
                $hotelsQ->where(function ($qq) use ($q) {
                    $qq->where('title', 'LIKE', $q . '%')
                        ->orWhere('title_en', 'LIKE', $q . '%')
                        ->orWhere('city', 'LIKE', $q . '%');
                });
            }
        }

        if ($rating > 0) {
            $hotelsQ->where('rating', '>=', $rating);
        }

        $hotels = $hotelsQ
            ->orderByDesc('rating')
            ->paginate(40);

        // =====================================================================
        // 3) --- LOCAL HOTELS (ТОЛЬКО С ТАРИФАМИ > 0) ---
        // =====================================================================
        $localItems = collect();

        foreach ($hotels as $h) {
            $rate = \App\Models\Rate::where('hotel_id', $h->id)
                ->orderBy('price')
                ->first();

            $pricePerNight = (float)($rate->price ?? 0);
            $total         = $pricePerNight * $nights;

            // если нет тарифа или он нулевой — такой отель НЕ показываем
            if ($total <= 0) {
                continue;
            }

            $srcCurrency = strtoupper($rate->currency ?? 'USD');
            $convPrice   = $convert($total, $srcCurrency, $fxBase);
            $convSymbol  = $symbols[$fxBase] ?? $fxBase;

            $localItems->push([
                'source'      => 'local',
                'apiHotelId'  => null,
                'hotel'       => $h,
                'price'       => $total,
                'conv_total'  => $convPrice,
                'conv_symbol' => $convSymbol,
            ]);
        }

        // =====================================================================
        // 4) --- EXELY ДЛЯ ОТЕЛЕЙ ТЕКУЩЕЙ СТРАНИЦЫ ---
        // =====================================================================
        $propertyIds = $hotels->pluck('exely_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $exelyItems = collect();

        if (!empty($propertyIds)) {

            $exelyItems = $this->fetchExelyItems(
                $propertyIds,
                $totalAdults,
                $allChildAges,
                $arrival,
                $depart,
                $symbols,
                $fxBase,
                $convert
            );

            // унифицируем структуру
            $exelyItems = $exelyItems->map(function ($item) {
                $item['source'] = 'exely';
                $item['hotel']  = \App\Models\Hotel::where('exely_id', $item['propertyId'])->first();
                return $item;
            });
        }

        // =====================================================================
        // 5) --- ETG: МИНИМАЛЬНЫЙ ТАРИФ ПО КАЖДОМУ HID (search/hp) ---
        // =====================================================================
        $etgItems = collect();

        try {
            $emerCtl = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();

            // Берём только отели из текущей страницы, у которых есть emerging_id
            $etgHotels = $hotels->filter(fn($h) => filled($h->emerging_id));

            foreach ($etgHotels as $h) {
                $hid = (int)$h->emerging_id;
                if ($hid <= 0) {
                    continue;
                }

                try {
                    // search/hp по конкретному HID
                    $hp = $emerCtl->searchRates($request, $hid);
                } catch (\Throwable $e) {
                    \Log::warning('ETG searchRates failed', [
                        'hid' => $hid,
                        'msg' => $e->getMessage(),
                    ]);
                    continue;
                }

                $rates = data_get($hp, 'rates', []);
                if (!is_array($rates) || empty($rates)) {
                    // нет тарифов — этот ETG-отель не показываем
                    continue;
                }

                // ищем минимальную цену
                $min     = null;
                $minCurr = 'USD';

                foreach ($rates as $rate) {
                    $pt  = data_get($rate, 'payment_options.payment_types.0');
                    $amt = (float) data_get($pt, 'amount', 0);
                    $cur = (string) data_get($pt, 'currency_code', 'USD');

                    if ($amt > 0 && ($min === null || $amt < $min)) {
                        $min     = $amt;
                        $minCurr = $cur;
                    }
                }

                if (!is_numeric($min) || $min <= 0) {
                    // тарифы есть, но все 0/отрицательные — тоже не показываем
                    continue;
                }

                $coef = (float) config('app.main_coef', 1);
                $sell = $coef > 0 ? ($min / $coef) : $min;

                $converted  = app(\App\Services\FXService::class)->convert($sell, $minCurr, $fxBase);
                $convSymbol = $symbols[$fxBase] ?? $fxBase;

                $etgItems->push([
                    'source'      => 'etg',
                    'apiHotelId'  => (string)$hid,
                    'hotel'       => $h,
                    'price'       => $min,
                    'currency'    => $minCurr,
                    'conv_total'  => (int)ceil($converted),
                    'conv_symbol' => $convSymbol,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('ETG block error: ' . $e->getMessage());
            $etgItems = collect();
        }

        // Убираем локальные дубликаты тех отелей, для которых есть ETG-тарифы (чтобы не было 2 карточек)
        $etgHotelIds = $etgItems->pluck('hotel.id')->filter()->unique()->all();
        if (!empty($etgHotelIds)) {
            $localItems = $localItems
                ->reject(function ($item) use ($etgHotelIds) {
                    return in_array($item['hotel']->id ?? null, $etgHotelIds, true);
                })
                ->values();
        }

        // =====================================================================
        // 6) --- СВОДИМ ВСЁ В ОДНУ КОЛЛЕКЦИЮ ---
        // =====================================================================
        $all = collect()
            ->concat($localItems)
            ->concat($exelyItems)
            ->concat($etgItems)
            ->sortBy(fn($i) => (float)$i['conv_total'])
            ->values();

        \Log::debug('SEARCH FINISH', [
            'local' => $localItems->count(),
            'exely' => $exelyItems->count(),
            'etg'   => $etgItems->count(),
            'all'   => $all->count(),
        ]);

        return view('pages.search.search', [
            'allHotels' => $all,
            'request'   => $request,
            'paginator' => $hotels,
        ]);
    }

    /**
     * Хелпер: запрос в Exely и сбор карточек с ценой/валютой (конвертация → $fxBase).
     */
    private function fetchExelyItems(
        array    $propertyIds,
        int      $totalAdults,
        array    $allChildAges,
        string   $arrival,
        string   $depart,
        array    $symbols,
        string   $fxBase,
        callable $convert
    )
    {
        $results = null;

        try {
            \Log::debug('EXELY propertyIds', $propertyIds);
            $payload = [
                'propertyIds'  => array_map('strval', array_values($propertyIds)),
                'adults'       => max(1, (int)$totalAdults),
                'childAges'    => array_map('intval', array_values($allChildAges)),

                // форматируем даты в YYYY-MM-DD на всякий случай
                'arrivalDate'  => \Carbon\Carbon::parse($arrival)->format('Y-m-d'),
                'departureDate'=> \Carbon\Carbon::parse($depart)->format('Y-m-d'),
            ];

            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->connectTimeout(5)
                ->retry(2, 100)
                ->accept('application/json')
                ->withHeaders([
                    'x-api-key' => (string) config('services.exely.key'),
                ])
                ->post(
                    rtrim((string) config('services.exely.base_url'), '/')
                    . '/search/v1/properties/room-stays/search',
                    $payload
                );

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

        $roomStays = collect(data_get($results, 'roomStays', []));

        return $roomStays->map(function ($roomStay) use ($symbols, $fxBase, $convert) {

            $basePrice = (float) data_get($roomStay, 'total.priceBeforeTax', 0);

            $srcCurr = (string) (
                data_get($roomStay, 'currencyCode')
                ?? data_get($roomStay, 'total.currencyCode')
                ?? 'USD'
            );

            // FIX: правильный путь к propertyId
            $propertyId = data_get($roomStay, 'property.id');

            // находим нужный отель в БД
            $hotelModel = $propertyId
                ? \App\Models\Hotel::where('exely_id', $propertyId)->first()
                : null;

            $convPrice   = $convert($basePrice, $srcCurr, $fxBase);
            $convSymbol  = $symbols[$fxBase] ?? $fxBase;

            return [
                'source'      => 'exely',
                'roomStay'    => $roomStay,
                'hotel'       => $hotelModel,
                'price'       => $basePrice,
                'conv_total'  => $convPrice,
                'conv_symbol' => $convSymbol,
                'propertyId'  => $propertyId, // ← полезно сохранить
            ];

        })->values();
    }


    public function suggest(Request $request)
    {
        try {
            $q = trim((string)$request->get('q', ''));
            if (mb_strlen($q) < 2) {
                return response()->json(['items' => []]);
            }

            $vars = $this->variants($q);
            $likeVars = array_map(fn($v) => '%' . $v . '%', $vars);

            $hotels = Hotel::query()
                ->select(['id', 'title', 'title_en', 'city', 'rating'])
                ->where(function ($qq) use ($likeVars) {
                    foreach ($likeVars as $pat) {
                        $qq->orWhereRaw('LOWER(city) LIKE ?', [$pat])
                            ->orWhereRaw('LOWER(title) LIKE ?', [$pat])
                            ->orWhereRaw('LOWER(title_en) LIKE ?', [$pat]);
                    }
                })
                ->orderByDesc('rating')
                ->limit(10)
                ->get()
                ->map(function ($h) {
                    return [
                        'type' => 'hotel',
                        'id' => $h->id,
                        'label' => $h->title ?: $h->title_en,
                        'alt' => $h->title_en ?: $h->title,
                        'city' => $h->city,
                        'rating' => $h->rating,
                    ];
                });

            return response()->json(['items' => $hotels->values()]);
        } catch (\Throwable $e) {
            Log::error('suggest failed', ['e' => $e->getMessage()]);
            return response()->json(['items' => []], 500);
        }
    }

    private function norm(string $s): string
    {
        // Только трим, нижний регистр и схлопывание пробелов — БЕЗ iconv!
        $s = trim(mb_strtolower($s, 'UTF-8'));
        $s = preg_replace('/\s+/u', ' ', $s);
        return $s;
    }

    private function variants(string $term): array
    {
        $base = $this->norm($term);
        if ($base === '') return [];

        $vars = [$base];

        $ru2en = $this->translitRuToEn($base);
        $en2ru = $this->translitEnToRu($base);
        if ($ru2en !== $base) $vars[] = $ru2en;
        if ($en2ru !== $base) $vars[] = $en2ru;

        // простые синонимы/исторические
        $syn = [
            'bishkek' => ['frunze', 'бишкек'],
            'бишкек' => ['фрунзе', 'bishkek'],
            'osh' => ['ош'],
            'алматы' => ['almaty'],
            'almaty' => ['алматы'],
            'астана' => ['нур-султан', 'nursultan', 'astana'],
            'нур-султан' => ['астана', 'astana', 'nursultan'],
            'astana' => ['астана', 'nursultan'],
        ];
        foreach ($syn as $k => $arr) {
            if (str_contains($base, $k)) {
                foreach ($arr as $a) $vars[] = $this->norm($a);
            }
        }

        return array_values(array_unique($vars));
    }

    private function translitRuToEn(string $s): string
    {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y',
            'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
            'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];
        return strtr($s, $map);
    }

    private function translitEnToRu(string $s): string
    {
        $seq = [
            'shch' => 'щ', 'yo' => 'ё', 'yu' => 'ю', 'ya' => 'я', 'kh' => 'х', 'ts' => 'ц', 'zh' => 'ж', 'ch' => 'ч', 'sh' => 'ш',
        ];
        foreach ($seq as $lat => $ru) {
            $s = str_replace($lat, $ru, $s);
        }
        $map = [
            'a' => 'а', 'b' => 'б', 'v' => 'в', 'g' => 'г', 'd' => 'д', 'e' => 'е', 'z' => 'з', 'i' => 'и', 'y' => 'й',
            'k' => 'к', 'l' => 'л', 'm' => 'м', 'n' => 'н', 'o' => 'о', 'p' => 'п', 'r' => 'р', 's' => 'с', 't' => 'т', 'u' => 'у', 'f' => 'ф',
            'h' => 'х', 'c' => 'к', 'q' => 'к', 'w' => 'в', 'x' => 'кс',
        ];
        return strtr($s, $map);
    }

    /**
     * Emerging API → приводим к единому виду карточек (source = 'emerging')
     */

    //page hotel
    public function findHotel($code, Request $request)
    {
        $hotel = Hotel::where('code', $code)->firstOrFail();
        $images = Image::where('hotel_id', $hotel->id)->get();

        $arrival = Carbon::parse($request->arrivalDate);
        $departure = Carbon::parse($request->departureDate);
        $count_day = $arrival->diffInDays($departure);
        $adult = (int)($request->adult ?? 1);

        $startTime = (string)$request->arrivalDate;
        $endTime = (string)$request->departureDate;

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
                        'latestAllotment' => function ($b) use ($overlap) {
                            $overlap($b);
                        },
                        'latestPrice' => function ($b) use ($overlap) {
                            $overlap($b);
                        },
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
            ->filter(fn($room) => $room->rates->isNotEmpty())
            // сортируем комнаты по минимальной цене тарифа
            ->sortBy('min_effective_price', SORT_NUMERIC)
            ->values();

        return view('pages.search.hotel', compact(
            'hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms', 'images'
        ));
    }

    //exely

    public function findHotelExely(Request $request, $propertyId = null)
    {
        // Собираем все входные данные + route-параметр
        $input = $request->all();
        if ($propertyId && empty($input['propertyId'])) {
            $input['propertyId'] = (string) $propertyId;
        }

        Log::debug('EXELY VERIFY INPUT', $input);

        // ⚠️ Ручная валидация без редиректа
        $validator = Validator::make($input, [
            'propertyId'    => 'required|string',
            'arrivalDate'   => 'required|date',
            'departureDate' => 'required|date|after:arrivalDate',
            'adultCount'    => 'required|integer|min:1',
            // childAges может быть строкой/массивом – разберём ниже сами
        ]);

        if ($validator->fails()) {
            Log::warning('Exely validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input'  => $input,
            ]);

            // Никаких редиректов – просто показываем пустой список
            return view('pages.search.exely.hotel', [
                'rooms'   => [],
                'request' => $request,
                'errors'  => $validator->errors(),
            ]);
        }

        // Нормализуем childAges: могут прийти как [""] или ["5, 7"] и т.п.
        $rawChildAges = $request->input('childAges', []);
        if (!is_array($rawChildAges)) {
            $rawChildAges = [$rawChildAges];
        }

        $childs = [];
        foreach ($rawChildAges as $value) {
            // Разбиваем по запятой/пробелам: "5, 7" -> ['5','7']
            $parts = preg_split('/[,\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $p) {
                $age = (int) $p;
                if ($age >= 0) {
                    $childs[] = $age;
                }
            }
        }

        $params = [
            'arrivalDate'          => $input['arrivalDate'],
            'departureDate'        => $input['departureDate'],
            'adults'               => (int)$input['adultCount'],
            'includeExtraStays'    => 'false',
            'includeExtraServices' => 'false',
        ];

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        foreach ($childs as $age) {
            $queryString .= '&childAges=' . urlencode($age);
        }

        $propertyIdFinal = $input['propertyId'];

        $url = rtrim(config('services.exely.base_url'), '/')
            . "/search/v1/properties/{$propertyIdFinal}/room-stays?"
            . $queryString;

        $response = Http::withHeaders([
            'x-api-key' => config('services.exely.key'),
            'accept'    => 'application/json',
        ])->get($url);

        Log::debug('📥 Ответ Exely:', [
            'url'    => $url,
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if (!$response->successful()) {
            Log::warning('Exely HP error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return view('pages.search.exely.hotel', [
                'rooms'   => [],
                'request' => $request,
            ]);
        }

        $data = json_decode($response->body());

        if (!isset($data->roomStays) || !is_array($data->roomStays)) {
            Log::warning('Exely: Нет roomStays в ответе', ['response' => $data]);

            return view('pages.search.exely.hotel', [
                'rooms'   => [],
                'request' => $request,
            ]);
        }

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


    public function hotel_etg($hid, Request $request)
    {
        // --- нормализация входа
        $hidUrl = (int)preg_replace('/\D+/', '', (string)$hid);

        $hotel = \App\Models\Hotel::where(function ($q) use ($hidUrl) {
            $q->where('emerging_id', $hidUrl)     // ← сначала пытаемся по emerging_id
            ->orWhere('id', $hidUrl);           // fallback (на всякий)
        })->with('amenity')->first();

        // если по URL нашли лок. id — всё равно возьмём истинный ETG HID из модели
        $etgHid = (int)($request->input('apiHotelId') ?: ($hotel->emerging_id ?? 0) ?: $hidUrl);

        $arrival = \Illuminate\Support\Str::of((string)$request->input('arrivalDate', now()->format('Y-m-d')))->trim()->substr(0, 10)->__toString();
        $departure = \Illuminate\Support\Str::of((string)$request->input('departureDate', now()->addDay()->format('Y-m-d')))->trim()->substr(0, 10)->__toString();
        try {
            $arrivalC = \Carbon\Carbon::parse($arrival);
        } catch (\Throwable) {
            $arrivalC = now();
        }
        try {
            $departC = \Carbon\Carbon::parse($departure);
        } catch (\Throwable) {
            $departC = (clone $arrivalC)->addDay();
        }
        if ($departC->lessThanOrEqualTo($arrivalC)) $departC = (clone $arrivalC)->addDay();

        $residency = strtoupper(\Illuminate\Support\Str::of((string)$request->input('residency', 'KG'))->trim()->substr(0, 2));
        $rooms = collect((array)$request->input('rooms', [['adults' => 1, 'childAges' => []]]))
            ->map(fn($r) => [
                'adults' => max(1, (int)($r['adults'] ?? 1)),
                'childAges' => collect($r['childAges'] ?? [])->map(fn($a) => (int)$a)->filter(fn($a) => $a >= 0)->values()->all()
            ])->values()->all();

        // --- вызов Emerging по ИСТИННОМУ etgHid
        $clean = $request->duplicate([
            'arrivalDate' => $arrivalC->format('Y-m-d'),
            'departureDate' => $departC->format('Y-m-d'),
            'residency' => $residency,
            'hid' => (string)$etgHid,     // ← ETG HID
            'apiHotelId' => (string)$etgHid,     // ← ETG HID
            'rooms' => $rooms,
        ]);

        $amenities = [];
        $tmimages = \App\Models\Image::where('hotel_id', $hotel->id ?? 0)->where('caption', 'guest_rooms')->get('image');
        $meals = \App\Models\Meal::pluck('title', 'id');

        try {
            $etg = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $etgrooms = $etg->searchRates($clean, $etgHid);   // ← сюда тоже ETG HID
        } catch (\Throwable $e) {
            \Log::error('ETG searchRates failed', ['e' => $e->getMessage(), 'hid' => $etgHid]);
            $etgrooms = ['rates' => [], 'message' => 'Поставщик недоступен'];
        }

        $etgHotel = data_get($etgrooms, 'hotel');

        if (is_array($etgHotel)) {
            $etgHotel = (object) $etgHotel;
        }

        return view('pages.search.emerging.hotel', [
            'hotel' => $hotel,
            'etgHotel'    => $etgHotel,
            'arrival' => $arrivalC,
            'departure' => $departC,
            'request' => $clean,
            'roomAmenity' => $amenities,
            'etgrooms' => $etgrooms,
            'tmimages' => $tmimages,
            'meals' => $meals,
        ]);
    }
//
//    // Hotelstar
//    public function hotel_hs($hid, Request $request)
//    {
//        $hotel = Hotel::where('id', $hid)->with(['amenity'])->first();
//        $froom = Room::where('hotel_id', $hid)->get(['amenities'])->first();
//        $amenities = explode(',', $froom->amenities ?? '');
//        $roomAmenity = array_slice($amenities, 0, 8);
//        $meals = Meal::pluck('title', 'id');
//        $arrival = Carbon::createFromDate($request->arrivalDate);
//        $departure = Carbon::createFromDate($request->departureDate);
//
//        $hotelstar = new \App\Http\Controllers\API\V1\Hotelstar\HotelstarFormController();
//        $hsroom = $hotelstar->searchHotelsByCityOrId($request, $hotel->id);
//
//
//        if ( isset($hsroom) ) {
//
//            $grouped = [];
//
//            // группируем по room_name
//            foreach ($hsroom as $item) {
//                $roomName = $item['room_name'];
//
//                if (!isset($grouped[$roomName])) {
//                    $grouped[$roomName] = [
//                        'room_name' => $roomName,
//                        'rates' => []
//                    ];
//                }
//
//                $grouped[$roomName]['rates'][] = $item;
//            }
//
//            // сортируем тарифы в каждой комнате по цене
//            foreach ($grouped as &$room) {
//                usort($room['rates'], fn($a, $b) => $a['price'] <=> $b['price']);
//            }
//
//            // сортируем сами комнаты по минимальной цене тарифа
//            usort($grouped, function ($a, $b) {
//                $minA = $a['rates'][0]['price'] ?? PHP_INT_MAX;
//                $minB = $b['rates'][0]['price'] ?? PHP_INT_MAX;
//                return $minA <=> $minB;
//            });
//
//            // сбрасываем ключи в обычный массив
//            $hsroom = array_values($grouped);
//        }
//
//        dump($hsroom);
//
//        $tmimages = Image::where('hotel_id', $hotel->id)->where('caption', 'guest_rooms')->get('image');
//
//        $city = City::where('title', $hotel->city)->first(['country_code']);
//
//        if (!$hotel->utc && $city && ($utc = $hotelService->getUtcOffsetByCountryCode($city->country_code))) {
//            $hotel->utc = $utc;
//            $hotel->save();
//        }
//
//
//        return view('pages.search.hotelstar.hotel', compact('hotel', 'arrival', 'departure', 'request', 'roomAmenity', 'hsroom', 'tmimages', 'meals'));
//    }
}
