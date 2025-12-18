<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Image;
use App\Models\Room;
use App\Models\Hotel;

class SearchController extends Controller
{
    public $coef;

    public function search(Request $request)
    {
        // ================= INPUT =================
        $q        = trim((string) ($request->input('q', $request->input('city', ''))));
        $rating   = (int) $request->input('rating', 0);
        $arrival  = (string) $request->input('arrivalDate', now()->format('Y-m-d'));
        $depart   = (string) $request->input('departureDate', now()->addDay()->format('Y-m-d'));

        // ================= ROOMS =================
        $rooms        = (array) $request->input('rooms', []);
        $totalAdults  = 0;
        $childAges    = [];

        foreach ($rooms as $r) {
            $totalAdults += (int)($r['adults'] ?? 0);
            foreach ((array)($r['childAges'] ?? []) as $age) {
                if ($age !== '' && $age !== null) {
                    $childAges[] = (int)$age;
                }
            }
        }
        if ($totalAdults <= 0) $totalAdults = 1;

        // ================= NIGHTS =================
        try {
            $nights = max(1, \Carbon\Carbon::parse($arrival)->diffInDays($depart));
        } catch (\Throwable $e) {
            $nights = 1;
        }

        // ================= CURRENCY =================
        $fxBase = strtoupper(session('currency', 'USD'));

        $symbols = [
            'USD' => '$', 'EUR' => '€', 'RUB' => '₽',
            'KGS' => 'сом', 'KZT' => '₸', 'GEL' => '₾', 'AZN' => '₼',
        ];

        $rates = [
            'USD' => 1, 'EUR' => 0.92, 'RUB' => 93,
            'KGS' => 87.5, 'KZT' => 480, 'GEL' => 2.7, 'AZN' => 1.7,
        ];

        $convert = fn(float $a, string $f, string $t)
        => isset($rates[$f], $rates[$t]) && $rates[$f] > 0
            ? round($a / $rates[$f] * $rates[$t], 2)
            : round($a, 2);

        // ================= HOTELS QUERY =================
        $hotelsQ = \App\Models\Hotel::query();

        if ($request->filled('city_id')) {
            $cityId = (int) $request->city_id;
            $cityTitle = \App\Models\City::where('id', $cityId)->value('title') ?? '';
            $vars = $cityTitle ? $this->variants($cityTitle) : [];

            $hotelsQ->where(function ($q) use ($cityId, $cityTitle, $vars) {
                $q->orWhere('city', (string)$cityId)
                    ->orWhere('city', $cityId);

                if ($cityTitle) {
                    $q->orWhereRaw('LOWER(city) = ?', [mb_strtolower($cityTitle)]);
                }

                foreach ($vars as $v) {
                    $q->orWhereRaw('LOWER(city) LIKE ?', [mb_strtolower($v) . '%']);
                }
            });
        } elseif ($q !== '') {
            $vars = $this->variants($q);
            $hotelsQ->where(function ($qq) use ($vars) {
                foreach ($vars as $v) {
                    $qq->orWhereRaw('LOWER(city) LIKE ?', [$v . '%'])
                        ->orWhereRaw('LOWER(title) LIKE ?', [$v . '%'])
                        ->orWhereRaw('LOWER(title_en) LIKE ?', [$v . '%']);
                }
            });
        }

        if ($rating > 0) {
            $hotelsQ->where('rating', '>=', $rating);
        }

        $hotels = $hotelsQ
            ->orderByRaw('exely_id IS NOT NULL DESC')
            ->orderByRaw('COALESCE(rating,0) DESC')
            ->paginate(100);

        // ================= LOCAL PRICES =================
        $ratesByHotel = \App\Models\Rate::whereIn('hotel_id', $hotels->pluck('id'))
            ->get(['hotel_id','currency','price','price2','price3'])
            ->groupBy('hotel_id');

        $localMap = collect();

        foreach ($hotels as $hotel) {

            // ❗ если есть exely_id — local НЕ добавляем
            if (filled($hotel->exely_id)) continue;

            $rows = $ratesByHotel->get($hotel->id, collect());
            $min = null; $ccy = 'USD';

            foreach ($rows as $r) {
                foreach (['price','price2','price3'] as $f) {
                    $v = (float)($r->$f ?? 0);
                    if ($v > 0 && ($min === null || $v < $min)) {
                        $min = $v;
                        $ccy = strtoupper($r->currency ?? 'USD');
                    }
                }
            }

            if (!$min) continue;

            $localMap[$hotel->id] = [
                'source'      => 'local',
                'hotel'       => $hotel,
                'conv_total'  => $convert($min * $nights, $ccy, $fxBase),
                'conv_symbol' => $symbols[$fxBase] ?? $fxBase,
            ];
        }

        // ================= EXELY =================
        $exelyHotels = $hotels->filter(fn($h) => filled($h->exely_id));
        $exelyMap    = $exelyHotels->keyBy(fn($h) => (string)$h->exely_id);

        $exelyItems = collect();

        if ($exelyMap->isNotEmpty()) {
            $exelyItems = $this->fetchExelyItems(
                $exelyMap->keys()->all(),
                $totalAdults,
                $childAges,
                $arrival,
                $depart,
                $symbols,
                $fxBase,
                $convert
            )->map(function ($i) use ($exelyMap) {
                $i['source'] = 'exely';
                $i['hotel']  = $exelyMap->get((string)$i['propertyId']);
                return $i;
            })->filter(fn($i) => $i['hotel'])->values();
        }

        // ================= FINAL =================
        $allHotels = collect()
            ->concat($exelyItems)   // приоритет
            ->concat($localMap->values())
            ->sortBy('conv_total')
            ->values();

        return view('pages.search.search', [
            'allHotels' => $allHotels,
            'request'   => $request,
            'paginator' => $hotels,
        ]);
    }

    /**
     * Хелпер: запрос в Exely и сбор карточек с ценой/валютой (конвертация → $fxBase).
     */
    private function fetchExelyItems(
        array $propertyIds,
        int $adults,
        array $childAges,
        string $arrival,
        string $depart,
        array $symbols,
        string $fxBase,
        callable $convert
    ): \Illuminate\Support\Collection {

        $propertyIds = array_map('strval', array_unique(array_filter($propertyIds)));

        $payload = [
            'propertyIds'   => $propertyIds,
            'adults'        => $adults,
            'arrivalDate'   => $arrival,
            'departureDate' => $depart,
        ];

        if (!empty($childAges)) {
            $payload['childAges'] = array_map('intval', $childAges);
        }

        $endpoint = rtrim(config('services.exely.base_url'), '/')
            . '/search/v1/properties/room-stays/search';

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(45)
                ->acceptJson()
                ->withHeaders([
                    'x-api-key'    => config('services.exely.key'),
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if (!$response->successful()) {
                \Log::warning('EXELY HTTP ERROR', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 1000),
                ]);
                return collect();
            }

            $roomStays = $response->json('roomStays', []);


            return collect($response->json('roomStays', []))
                ->filter(fn($rs) => (float) data_get($rs, 'total.priceBeforeTax', 0) > 0)
                ->groupBy(fn($rs) => (string) data_get($rs, 'propertyId'))
                ->map(function ($group) use ($convert, $symbols, $fxBase) {
                    $min = $group->sortBy(fn($rs) => (float) data_get($rs, 'total.priceBeforeTax', 0))->first();

                    $price = (float) data_get($min, 'total.priceBeforeTax', 0);
                    $ccy   = (string) data_get($min, 'currencyCode', 'USD');

                    return [
                        'propertyId'  => (string) data_get($min, 'propertyId'),
                        'roomStay'    => $min, // ✅ минимальный roomStay
                        'conv_total'  => round($convert($price, $ccy, $fxBase)),
                        'conv_symbol' => $symbols[$fxBase] ?? $fxBase,
                    ];
                })
                ->values();

        } catch (\Throwable $e) {
            \Log::error('EXELY EXCEPTION', ['msg' => $e->getMessage()]);
            return collect();
        }
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

            // --- HOTELS ---
            $hotels = \App\Models\Hotel::query()
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
                        'type'   => 'hotel',
                        'id'     => $h->id,
                        'label'  => $h->title ?: $h->title_en,
                        'alt'    => $h->title_en ?: $h->title,
                        'city'   => $h->city,
                        'rating' => $h->rating,
                        // можно сразу дать url если нужно:
                        // 'url' => route('search', ['hotel_id' => $h->id]),
                    ];
                });

            // --- CITIES (из базы) ---
            $cities = \App\Models\City::query()
                ->select(['id', 'title'])
                ->where(function ($qq) use ($likeVars) {
                    foreach ($likeVars as $pat) {
                        $qq->orWhereRaw('LOWER(title) LIKE ?', [$pat]);
                    }
                })
                ->orderBy('title')
                ->limit(10)
                ->get()
                ->map(function ($c) {
                    return [
                        'type'    => 'city',
                        'id'      => $c->id,
                        'city_id' => $c->id,                 // ✅ для твоего JS (заполняет #city_id)
                        'label'   => $c->title ?: $c->title_en,
                        'alt'     => $c->title_en ?: $c->title,
                        'city'    => $c->title,
                        'rating'  => null,
                        // 'url' => route('search', ['city' => $c->title, 'city_id' => $c->id]),
                    ];
                });

            // Склеиваем: сначала отели, потом города
            $items = $hotels->concat($cities)->values();

            return response()->json(['items' => $items]);
        } catch (\Throwable $e) {
            \Log::error('suggest failed', ['e' => $e->getMessage()]);
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
        // ------------------ INPUT ------------------
        $input = $request->all();
        if ($propertyId && empty($input['propertyId'])) {
            $input['propertyId'] = (string) $propertyId;
        }

        $validator = \Validator::make($input, [
            'propertyId'    => 'required|string',
            'arrivalDate'   => 'required|date',
            'departureDate' => 'required|date|after:arrivalDate',
            'adultCount'    => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            \Log::warning('Exely validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input'  => $input,
            ]);

            return view('pages.search.exely.hotel', [
                'rooms'   => [],
                'request' => $request,
                'errors'  => $validator->errors(),
            ]);
        }

        // ------------------ CHILD AGES normalize ------------------
        $rawChildAges = $request->input('childAges', []);
        if (!is_array($rawChildAges)) $rawChildAges = [$rawChildAges];

        $childs = [];
        foreach ($rawChildAges as $value) {
            $parts = preg_split('/[,\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $p) {
                $age = (int) $p;
                if ($age >= 0) $childs[] = $age;
            }
        }

        // ------------------ FX / symbols ------------------
        $fxBase = strtoupper((string) session('currency', 'USD'));
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'RUB' => '₽',
            'KGS' => 'сом',
            'KZT' => '₸',
            'GEL' => '₾',
            'AZN' => '₼',
            'UZS' => "сўм",
        ];

        $fx = app(\App\Services\FXService::class);

        // markup coef (как у тебя в blade)
        $pricingCoef = auth()->check() && auth()->user()->hasRole('Hotelios')
            ? (float) config('pricing.hotelios', 1.05)
            : (float) config('pricing.default', 1.08);

        // ------------------ BUILD URL ------------------
        $params = [
            'arrivalDate'          => $input['arrivalDate'],
            'departureDate'        => $input['departureDate'],
            'adults'               => (int) $input['adultCount'],
            'includeExtraStays'    => 'false',
            'includeExtraServices' => 'false',
        ];

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        foreach ($childs as $age) {
            $queryString .= '&childAges=' . urlencode((string)$age);
        }

        $propertyIdFinal = (string) $input['propertyId'];

        $url = rtrim((string) config('services.exely.base_url'), '/')
            . "/search/v1/properties/{$propertyIdFinal}/room-stays?"
            . $queryString;

        // ------------------ REQUEST ------------------
        $response = \Http::withHeaders([
            'x-api-key' => (string) config('services.exely.key'),
            'accept'    => 'application/json',
        ])->get($url);

        if (!$response->successful()) {
            \Log::warning('Exely HP error', [
                'status' => $response->status(),
                'body'   => mb_substr((string) $response->body(), 0, 2000),
                'url'    => $url,
            ]);

            return view('pages.search.exely.hotel', [
                'rooms'   => [],
                'request' => $request,
            ]);
        }

        $data = $response->json();
        $roomStays = data_get($data, 'roomStays', []);

        if (!is_array($roomStays)) {
            \Log::warning('Exely: roomStays missing or invalid', ['keys' => array_keys((array)$data)]);
            return view('pages.search.exely.hotel', [
                'rooms'   => [],
                'request' => $request,
            ]);
        }

        // ------------------ MAP rooms: add conv_total/conv_cancel/conv_symbol ------------------
        $rooms = collect($roomStays)
            ->map(function ($room) use ($fx, $fxBase, $symbols, $pricingCoef) {

                // гарантия что это массив
                if (is_object($room)) $room = (array) $room;

                $ccy = (string) data_get($room, 'currencyCode', 'USD');
                $rawPrice = (float) data_get($room, 'total.priceBeforeTax', 0);
                $sellPrice = $rawPrice > 0 ? ($rawPrice * $pricingCoef) : 0;
                $convTotal = $fx->convert($sellPrice, $ccy, $fxBase);

                $room['conv_total']  = round($convTotal);
                $room['conv_symbol'] = $symbols[$fxBase] ?? $fxBase;

// ---------- CANCELLATION (fixed for numeric penaltyAmount) ----------
                $cancelRaw = 0.0;

// penaltyAmount может быть числом (как у тебя в логе)
                $penalty = data_get($room, 'cancellationPolicy.penaltyAmount', 0);
                if (is_numeric($penalty)) {
                    $cancelRaw = (float) $penalty;
                }

// если вдруг придёт объектом (на будущее)
                if ($cancelRaw <= 0) {
                    $cancelRaw = (float) data_get($room, 'cancellationPolicy.penaltyAmount.amount', 0);
                }
                if ($cancelRaw <= 0) {
                    $cancelRaw = (float) data_get($room, 'cancellationPolicy.penaltyAmount.amount.amount', 0);
                }

// fallback: penalties[0]
                if ($cancelRaw <= 0) {
                    $cancelRaw = (float) data_get($room, 'cancellationPolicy.penalties.0.amount.amount', 0);
                }
                if ($cancelRaw <= 0) {
                    $cancelRaw = (float) data_get($room, 'cancellationPolicy.penalties.0.amount', 0);
                }

// fallback: percent
                if ($cancelRaw <= 0) {
                    $percent = (float) data_get($room, 'cancellationPolicy.penalties.0.percent', 0);
                    if ($percent > 0 && $rawPrice > 0) {
                        $cancelRaw = $rawPrice * ($percent / 100);
                    }
                }

// markup + convert
                $sellCancel = $cancelRaw > 0 ? ($cancelRaw * $pricingCoef) : 0;

                if ($sellCancel > 0) {
                    $convCancel = $fx->convert($sellCancel, $ccy, $fxBase);
                    $room['conv_cancel'] = round($convCancel);
                } else {
                    $room['conv_cancel'] = null;
                }

                return $room;
            })
            ->sortBy(fn($r) => (float) data_get($r, 'total.priceBeforeTax', 0))
            ->values()
            ->all();

        return view('pages.search.exely.hotel', [
            'rooms'   => $rooms,
            'request' => $request,
            'fxBase'  => $fxBase,
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
