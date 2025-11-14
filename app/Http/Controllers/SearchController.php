<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\City;
use App\Models\Image;
use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public $coef;

    public function search(Request $request)
    {
        // ------- входные ----------
        $hotelId = (int)$request->input('hotel_id');
        $q = trim((string)($request->input('q', $request->input('city', ''))));
        $rating = (int)$request->input('rating', 0);

        $arrival = (string)$request->input('arrivalDate', now()->format('Y-m-d'));
        $depart = (string)$request->input('departureDate', now()->addDay()->format('Y-m-d'));

        // комнаты/гости
        $rooms = (array)$request->input('rooms', []);
        $totalAdults = 0;
        $allChildAges = [];
        foreach ($rooms as $r) {
            $totalAdults += (int)($r['adults'] ?? 0);
            foreach ((array)($r['childAges'] ?? []) as $age) {
                if ($age !== '' && $age !== null) $allChildAges[] = (int)$age;
            }
        }
        if ($totalAdults <= 0) $totalAdults = 1;

        // ночи
        $nights = 1;
        try {
            $n = \Carbon\Carbon::parse($arrival)->diffInDays(\Carbon\Carbon::parse($depart));
            $nights = max(1, $n);
        } catch (\Throwable $e) {
            $nights = 1;
        }

        // ─── валюта пользователя + символы и простые курсы ─────────────────────────
        $fxBase = strtoupper((string)session('currency', 'USD'));
        $symbols = ['USD' => '$', 'EUR' => '€', 'RUB' => '₽', 'KGS' => 'сом', 'KZT' => '₸'];

        // примеры курсов; при желании замените на реальные из вашего источника
        $rates = [
            'USD' => 1.00,
            'EUR' => 0.92,
            'RUB' => 93.0,
            'KGS' => 87.5,
            'KZT' => 480.0,
        ];

        $convert = function (float $amount, string $from, string $to) use ($rates): float {
            $from = strtoupper($from);
            $to = strtoupper($to);
            if (!isset($rates[$from]) || !isset($rates[$to]) || $rates[$from] == 0.0) {
                return round($amount, 2);
            }
            // переводим через USD-эквивалент: amount / rate_from * rate_to
            return round($amount / $rates[$from] * $rates[$to], 2);
        };

        // ====== если выбран конкретный отель ======
        if ($hotelId > 0) {
            $h = \App\Models\Hotel::find($hotelId);

            if (!$h) {
                return view('pages.search.search', ['allHotels' => collect(), 'request' => $request]);
            }

            $rate = \App\Models\Rate::query()
                ->where('hotel_id', $h->id)
                ->orderBy('price', 'asc')
                ->first();

            $pricePerNight = (float)($rate->price ?? 0);
            $totalLocal = $pricePerNight * $nights;

            $srcCurrency = strtoupper($rate->currency ?? 'USD');
            $convPrice = $convert($totalLocal, $srcCurrency, $fxBase);
            $convSymbol = $symbols[$fxBase] ?? $fxBase;

            $localItems = collect([[
                'source' => 'local',
                'hotel' => $h,
                'price' => $totalLocal,     // оригинальная сумма в валюте тарифа
                'conv_total' => $convPrice,      // пересчитано в валюту пользователя
                'conv_symbol' => $convSymbol,
            ]]);

            // + Exely по этому же отелю, если есть exely_id
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
            }

            $all = $localItems->concat($exelyItems)->values();

            return view('pages.search.search', [
                'allHotels' => $all,
                'request' => $request,
            ]);
        }

        // ====== общий поиск (город/название, RU+EN) ======
        $vars = $this->variants($q);
        $likeVars = array_map(fn($v) => '%' . $v . '%', $vars);

        $hotelsQ = \App\Models\Hotel::query();

        if (!empty($likeVars)) {
            $hotelsQ->where(function ($q2) use ($likeVars) {
                foreach ($likeVars as $pat) {
                    $q2->orWhereRaw('LOWER(city) LIKE ?', [$pat])
                        ->orWhereRaw('LOWER(title) LIKE ?', [$pat])
                        ->orWhereRaw('LOWER(title_en) LIKE ?', [$pat]);
                }
            });
        }

        if ($rating > 0) {
            $hotelsQ->where('rating', '>=', $rating);
        }

        $hotels = $hotelsQ->orderByDesc('rating')->get();

        $localItems = $hotels->map(function ($h) use ($nights, $symbols, $fxBase, $convert) {
            $rate = \App\Models\Rate::query()
                ->where('hotel_id', $h->id)
                ->orderBy('price', 'asc')
                ->first();

            $pricePerNight = (float)($rate->price ?? 0);
            $total = $pricePerNight * max(1, (int)$nights);

            $srcCurrency = strtoupper($rate->currency ?? 'USD');
            $convPrice = $convert($total, $srcCurrency, $fxBase);
            $convSymbol = $symbols[$fxBase] ?? $fxBase;

            $isEtg = filled($h->emerging_id);
            $source = $isEtg ? 'etg' : 'local';
            $apiHid = $isEtg ? (string)$h->emerging_id : null;

            return [
                'source' => $source,       // local или etg// local или etg
                'apiHotelId' => $apiHid,       // нужен фронту
                'hotel' => $h,
                'price' => $total,
                'conv_total' => $convPrice,
                'conv_symbol' => $convSymbol,
            ];
        });

        // ====== Exely для всех найденных отелей, у которых есть exely_id ======
        $propertyIds = $hotels->pluck('exely_id')
            ->filter(fn($v) => filled($v))
            ->map(fn($id) => (string)$id)
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
        }

        $exelyPropertyIds = $exelyItems
            ->map(fn($i) => (string)data_get($i, 'roomStay.propertyId'))
            ->filter()
            ->values()
            ->all();


//        //tourmind
//        $hotels['hotels'] = array_map(function ($hotel) use ($fxBase, $fxRates, $symbols) {
//            $rate = $hotel['rates'] ?? null;
//
//            $price    = isset($rate['price']) ? (float)$rate['price'] : 0;
//            $currency = $rate['currency'] ?? 'RUB';
//
//            $totalPrice = $price > 0 ? number_format(($price / ($this->coef ?? 1)), 2, '.', '') : 0;
//
//            $toCurrency = strtoupper($fxBase ?? 'USD');
//            $converted  = $price > 0
//                ? app(\App\Services\FXService::class)->convert($totalPrice, $currency, $fxBase)
//                : 0;
//
//            $symbol = $symbols[$toCurrency] ?? $toCurrency;
//
//            return [
//                'source'       => 'emerging', // ← ВАЖНО: помечаем как emerging
//                'apiName'      => 'HS',
//                'apiHotelId'   => $rate['hotel_id'] ?? '',
//                'hid'          => $hotel['localData']['id'] ?? '',
//                'code'         => $hotel['localData']['code'] ?? '',
//                'title'        => $hotel['localData']['title'] ?? '',
//                'title_en'     => $hotel['localData']['title_en'] ?? '',
//                'rating'       => $hotel['localData']['rating'] ?? '',
//                'city'         => $hotel['localData']['city'] ?? '',
//                'amenities'    => $hotel['localData']['amenity']['services'] ?? '',
//                'images'       => $hotel['localData']['images'] ?? [],
//                'lat'          => $hotel['localData']['lat'] ?? '',
//                'lng'          => $hotel['localData']['lng'] ?? '',
//                'price'        => $price,
//                'totalPrice'   => $totalPrice,
//                'currency'     => $currency,
//                'hash'         => $rate['hash'] ?? '',
//                'provider_id'  => $rate['provider_id'] ?? '',
//                'conv_total'   => round($converted),
//                'conv_symbol'  => $symbol,
//            ];
//        }, $HSHotels);

        $localFiltered = $localItems->filter(function ($item) {
            $hotel = $item['hotel'] ?? null;
            return !$hotel || empty($hotel->exely_id);
        });

        $exelyFiltered = $exelyItems->filter(function ($item) {
            $hotel = $item['hotel'] ?? null;
            return $hotel && filled($hotel->exely_id);
        });

        // ====== Emerging (ETG) — реальные цены из API ======
        // ====== Emerging (ETG) — реальные цены из /search/hp для первых N ======
        $etgItems = collect();
        try {
            $emerCtl    = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $emerResult = $emerCtl->EmergingGetHotels($request);       // /search/serp/region
            $coef       = (float) (config('app.main_coef') ?: 1);
            $fxService  = app(\App\Services\FXService::class);

            $hotelsFromEtg = collect((array) data_get($emerResult, 'data.hotels', []))
                ->filter(fn($h) => !empty(data_get($h, 'localData.id')))
                ->values();

            // дергаем HP только для первых N отелей на странице (чтобы не упереться в 10/мин)
            $N = 8;
            $etgItems = $hotelsFromEtg->take($N)->map(function(array $h) use ($emerCtl, $request, $coef, $fxBase, $symbols, $fxService) {

                $hid   = (int) data_get($h, 'hid');
                $local = (object) data_get($h, 'localData');

                // получаем тарифы от HP
                $hp = $emerCtl->searchRates($request, $hid); // возвращает ['rates'=>..., 'hotel'=>..., 'message'=>...]
                $rates = (array) data_get($hp, 'rates', []);

                // ищем минимальный payment_types.amount
                $min      = null;
                $minCurr  = 'USD';
                foreach ($rates as $rate) {
                    $pt  = data_get($rate, 'payment_options.payment_types.0');
                    $amt = (float) data_get($pt, 'amount');
                    $cur = (string) data_get($pt, 'currency_code', 'USD');
                    if ($amt > 0 && ($min === null || $amt < $min)) { $min = $amt; $minCurr = $cur; }
                }

                // если тарифов нет — пропускаем этот отель в списке
                if (!is_numeric($min) || $min <= 0) {
                    return null;
                }

                // применяем ваш коэффициент и конвертацию в валюту пользователя
                $sellTotal  = $coef > 0 ? ($min / $coef) : $min;
                $converted  = $fxService->convert($sellTotal, $minCurr, $fxBase);
                $convSymbol = $symbols[strtoupper($fxBase)] ?? strtoupper($fxBase);

                return [
                    'source'      => 'etg',
                    'sources'      => 'etg',
                    'apiHotelId'  => (string) $hid,
                    'hotel'       => $local,
                    'price'       => $min,                 // исходная сумма (валюта поставщика)
                    'currency'    => $minCurr,
                    'conv_total'  => round($converted),    // что показываем пользователю
                    'conv_symbol' => $convSymbol,
                ];
            })->filter()->values();

            // если N < общего числа отелей — оставшиеся можно показывать без цены («—»), либо вовсе не добавлять
        } catch (\Throwable $e) {
            \Log::warning('ETG list failed in search()', ['msg' => $e->getMessage()]);
        }

// Теперь объединяем все источники
        $all = collect()
            ->concat($localFiltered)
            ->concat($exelyFiltered)
            ->concat($etgItems)
            ->values();

// если нужна единая наценка — применяйте её аккуратно и НЕ округляйте в 0:
        $all = $all->map(function ($item) {
            if (!isset($item['conv_total'])) return $item;
            $item['conv_total'] = (int) ceil($item['conv_total']); // безопасное округление вверх
            $item['price']      = $item['conv_total'];
            return $item;
        })->sortBy(fn($i) => (float)($i['conv_total'] ?? PHP_FLOAT_MAX))->values();


        return view('pages.search.search', [
            'allHotels' => $all,
            'request' => $request,
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
            $payload = [
                'propertyIds' => array_values($propertyIds),
                'adults' => max(1, (int)$totalAdults),
                'childAges' => array_values($allChildAges),
                'arrivalDate' => (string)$arrival,
                'departureDate' => (string)$depart,
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
                    'status' => $response->status(),
                    'payload' => $payload,
                    'body' => $response->body(),
                ]);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Illuminate\Support\Facades\Log::error('Exely connection error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Exely unexpected error: ' . $e->getMessage());
        }

        $roomStays = collect(data_get($results, 'roomStays', []));

        return $roomStays->map(function ($roomStay) use ($symbols, $fxBase, $convert) {
            $basePrice = (float)data_get($roomStay, 'total.priceBeforeTax', 0);
            $srcCurr = (string)(
                data_get($roomStay, 'currencyCode') ??
                data_get($roomStay, 'total.currencyCode') ?? 'USD'
            );

            $propertyId = data_get($roomStay, 'propertyId');
            $hotelModel = $propertyId
                ? \App\Models\Hotel::where('exely_id', $propertyId)->first()
                : null;

            $convPrice = $convert($basePrice, $srcCurr, $fxBase);
            $convSymbol = $symbols[$fxBase] ?? $fxBase;


            return [
                'source' => 'exely',
                'roomStay' => $roomStay,
                'hotel' => $hotelModel,
                'price' => $basePrice,   // оригинальная из Exely
                'conv_total' => $convPrice,   // пересчитано в валюту пользователя
                'conv_symbol' => $convSymbol,
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
    private function fetchEmergingItems(
        \Illuminate\Http\Request $request,
        string                   $fxBase,
        callable                 $convert,
        array                    $symbols,
        float                    $coef = 0.92
    )
    {
        try {
            $emerSearch = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $emerHotelsRes = $emerSearch->emergingGetHotels($request); // ['data' => ['hotels' => [...]]]
            $rawHotels = (array)data_get($emerHotelsRes, 'data.hotels', []);

            if (empty($rawHotels)) {
                Log::debug('Emerging: empty hotels payload');
                return collect();
            }

            // Фильтр по meal — вызывать mapping только если он действительно нужен
            $selectedMealIds = (array)$request->input('meal', []);
            if (!empty($selectedMealIds)) {
                try {
                    $mappingMeals = $emerSearch->mappingMealsGrouped(); // вызывем только при необходимости
                    $allowedMeals = collect($selectedMealIds)
                        ->map(fn($id) => $mappingMeals[$id] ?? [])
                        ->flatten()
                        ->toArray();

                    $rawHotels = array_map(function ($h) use ($allowedMeals) {
                        $rates = (array)data_get($h, 'rates', []);
                        $rates = array_values(array_filter($rates, fn($r) => in_array(data_get($r, 'meal'), $allowedMeals, true)));
                        $h['rates'] = $rates;
                        return $h;
                    }, $rawHotels);

                    // убрать отели без тарифов
                    $rawHotels = array_values(array_filter($rawHotels, fn($h) => !empty($h['rates'])));
                } catch (\Throwable $e) {
                    // если внутри mapping что-то сломалось — просто не фильтруем по meal
                    Log::warning('Emerging meals mapping skipped: ' . $e->getMessage());
                }
            }

            $toCurrency = strtoupper($fxBase ?: 'USD');
            $symbol = $symbols[$toCurrency] ?? $toCurrency;

            $items = array_map(function ($h) use ($coef, $convert, $toCurrency, $symbol) {
                // безопасно берём ПЕРВЫЙ тариф (если есть)

                $rates = (array)data_get($h, 'rates', []);
                $rates = array_values($rates);              // нормализуем индексы 0..N
                $rate = $rates[0] ?? [];                   // берём первый доступный тариф

                // безопасно берём ПЕРВЫЙ payment_type (если есть)
                $payList = (array)data_get($rate, 'payment_options.payment_types', []);
                $pay = is_array($payList) ? (reset($payList) ?: []) : [];


                $amount = (float)data_get($pay, 'amount', data_get($rate, 'amount', 0));
                $cur = (string)data_get($pay, 'currency_code', data_get($rate, 'currency', 'USD'));

                // net с коэфом
                $net = $coef > 0 ? (float)number_format($amount / $coef, 2, '.', '') : $amount;

                // конвертация в валюту пользователя
                $converted = $net > 0 ? $convert($net, $cur, $toCurrency) : 0;

                return [
                    'source' => 'emerging',
                    'apiName' => 'ETG',
                    'apiHotelId' => (string)data_get($h, 'hid', ''),
                    'title' => (string)data_get($h, 'localData.title', ''),
                    'title_en' => (string)data_get($h, 'localData.title_en', ''),
                    'rating' => data_get($h, 'localData.rating'),
                    'city' => (string)data_get($h, 'localData.city', ''),
                    'images' => (array)data_get($h, 'localData.images', []),

                    // цена без нашей 8% надбавки; её добавим общим постпроцессом
                    'price' => $converted,
                    'conv_total' => $converted,
                    'conv_symbol' => $symbol,
                ];
            }, $rawHotels);

            return collect($items)->values();
        } catch (\Throwable $th) {
            Log::error('Emerging API in search failed (wrapper): ' . $th->getMessage());
            return collect();
        }
    }

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

        return view('pages.search.emerging.hotel', [
            'hotel' => $hotel,         // может быть null — шаблон уже устойчив
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
