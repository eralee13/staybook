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
use Illuminate\Support\Facades\Schema;


class SearchController extends Controller
{
    public $coef;

    // Внутри SearchController (private-секция)
    private function ruToEn(string $s): string {
        $map = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y',
            'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
            'х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
            // частые киргизские буквы
            'ө'=>'o','ү'=>'u','ң'=>'ng',
        ];
        $s = mb_strtolower($s);
        $res = '';
        for ($i=0;$i<mb_strlen($s);$i++){
            $ch = mb_substr($s,$i,1);
            $res .= $map[$ch] ?? $ch;
        }
        return $res;
    }

    private function enToRu(string $s): string {
        $s = mb_strtolower($s);
        // грубая, но рабочая обратная замена по частым сочетаниям
        $pairs = [
            'sch'=>'щ','yo'=>'ё','yu'=>'ю','ya'=>'я','zh'=>'ж','ch'=>'ч','sh'=>'ш','ts'=>'ц','ng'=>'ң',
        ];
        foreach ($pairs as $en=>$ru) $s = str_replace($en, $ru, $s);
        $map = [
            'a'=>'а','b'=>'б','v'=>'в','g'=>'г','d'=>'д','e'=>'е','z'=>'з','i'=>'и','y'=>'ы','k'=>'к','l'=>'л',
            'm'=>'м','n'=>'н','o'=>'о','p'=>'п','r'=>'р','s'=>'с','t'=>'т','u'=>'у','f'=>'ф','h'=>'х',
        ];
        $res = '';
        for ($i=0;$i<mb_strlen($s);$i++){
            $ch = mb_substr($s,$i,1);
            $res .= $map[$ch] ?? $ch;
        }
        return $res;
    }

    /** Возвращает массив вариантов строки: [оригинал, RU->EN, EN->RU] (без дублей) */
    private function variants(string $q): array {
        $t = mb_strtolower(trim($q));
        $vars = array_filter(array_unique([$t, $this->ruToEn($t), $this->enToRu($t)]));
        return array_values($vars);
    }

    public function search(Request $request)
    {
        // --- 1) Резолвим город ---
        $city = null;
        if ($cityId = $request->get('city_id')) {
            $city = \App\Models\City::find($cityId);
        }
        if (!$city && $term !== '') {
            $terms = $this->variants($term);
            $city = \App\Models\City::query()
                ->where(function($q) use ($terms) {
                    foreach ($terms as $v) {
                        $q->orWhereRaw('LOWER(title) LIKE ?', ['%'.$v.'%']);
                    }
                })
                ->first();
        }

// Бишкек/ Bishkek спец-случай (если нужно)
        $countryCode = null;
        if (!$city && $term !== '' && in_array(mb_strtolower($term), ['бишкек','bishkek','frunze'])) {
            $countryCode = 'KGS';
            $city = \App\Models\City::where(function($q){
                $q->whereRaw('LOWER(title) = "бишкек"')
                    ->orWhereRaw('LOWER(title) = "bishkek"');
            })->first();
        }

// --- 2) Фильтр отелей (учёт наличия hotels.city_id) ---
        $hasCityIdColumn = \Illuminate\Support\Facades\Schema::hasColumn('hotels', 'city_id');

        $hotelsQ = \App\Models\Hotel::query()
            ->with(['amenity'])
            ->when((int)$request->get('rating',0) > 0, fn($q) => $q->where('rating','>=',(int)$request->get('rating',0)));

        if ($hasCityIdColumn) {
            $hotelsQ
                ->when($city, fn($q) => $q->where('city_id', $city->id))
                ->when(!$city && $countryCode, function($q) use ($countryCode){
                    $q->whereHas('city', fn($qc)=>$qc->where('country_code',$countryCode));
                })
                ->when(!$city && !$countryCode && $term !== '', function($q) use ($term){
                    $terms = $this->variants($term);
                    $q->where(function($qq) use ($terms){
                        foreach ($terms as $v) {
                            $qq->orWhereRaw('LOWER(title) LIKE ?', ['%'.$v.'%'])
                                ->orWhereRaw('LOWER(title_en) LIKE ?', ['%'.$v.'%']);
                        }
                    })
                        ->orWhereHas('city', function($qc) use ($terms){
                            $qc->where(function($qq) use ($terms){
                                foreach ($terms as $v) {
                                    $qq->orWhereRaw('LOWER(title) LIKE ?', ['%'.$v.'%']);
                                }
                            });
                        });
                });
        } else {
            // Без city_id — по строковому полю hotels.city
            $hotelsQ
                ->when($city, function($q) use ($city){
                    $t = mb_strtolower($city->title);
                    $vars = $this->variants($t);
                    $q->where(function($qq) use ($vars){
                        foreach ($vars as $v) $qq->orWhereRaw('LOWER(city) LIKE ?', ['%'.$v.'%']);
                    });
                })
                ->when(!$city && !$countryCode && $request->filled('city'), function($q) use ($request) {
                    $term  = trim((string)$request->get('city'));
                    $terms = $this->variants($term);

                    $q->where(function($qq) use ($terms) {
                        foreach ($terms as $v) {
                            $qq->orWhereRaw('LOWER(title) LIKE ?', ['%'.$v.'%'])
                                ->orWhereRaw('LOWER(title_en) LIKE ?', ['%'.$v.'%']);
                        }
                    })
                        ->orWhereHas('city', function($qc) use ($terms) {
                            $qc->where(function($qq) use ($terms) {
                                foreach ($terms as $v) {
                                    $qq->orWhereRaw('LOWER(title) LIKE ?', ['%'.$v.'%']);
                                }
                            });
                        });
                });
        }

        $hotels = $hotelsQ->orderByDesc('rating')->get();

        // --- 3) Отдаём представление ---
        // Если у вас есть «тяжёлый» шаблон pages.search.search — можно туда;
        // если вам достаточно простого списка, убедитесь, что шаблон его выводит.
        return view('pages.search.search', [
            'allHotels' => $hotels->map(fn($h) => [
                'source'      => 'local',
                'hotel'       => $h,
                'conv_total'  => $h->min_price ?? 0,   // подставьте вашу цену/конвертацию
                'conv_symbol' => 'KGS',               // или ваша валюта
            ]),
            'request'   => $request,
        ]);
    }

    /*** АПИ ПОДСКАЗОК
     * Возвращает items: [ {type:'city'|'hotel', label, alt, city, city_id, rating?}, ... ]
     */
    public function suggest(Request $request)
    {
        try {
            $q = trim((string)$request->get('q',''));
            if (mb_strlen($q) < 2) {
                return response()->json([]);
            }
            $terms = $this->variants($q);

            // Города
            $cities = \App\Models\City::query()
                ->where(function($w) use ($terms){
                    foreach ($terms as $v) {
                        $w->orWhereRaw('LOWER(title) LIKE ?', ['%'.$v.'%']);
                    }
                })
                ->limit(6)->get()
                ->map(fn($c)=>[
                    'type' => 'city',
                    'id'   => $c->id,
                    'name' => $c->title,
                    'note' => $c->country_code ?? '', // если есть
                ]);

            // Отели (с учётом наличия city_id)
            $hasCityIdColumn = \Illuminate\Support\Facades\Schema::hasColumn('hotels','city_id');

            $hotelsQ = \App\Models\Hotel::query();
            $hotelsQ->where(function($w) use ($terms){
                foreach ($terms as $v) {
                    $w->orWhereRaw('LOWER(title) LIKE ?', ['%'.$v.'%'])
                        ->orWhereRaw('LOWER(title_en) LIKE ?', ['%'.$v.'%']);
                }
            });

            if ($hasCityIdColumn) {
                $hotelsQ->with('city:id,title')->limit(6);
                $hotels = $hotelsQ->get()->map(fn($h)=>[
                    'type' => 'hotel',
                    'id'   => $h->id,
                    'name' => $h->title ?? $h->title_en,
                    'note' => $h->city?->title ?? '',
                    'alpha2' => '', // заполняйте при наличии стран
                ]);
            } else {
                $hotelsQ->select(['id','title','title_en','city'])->limit(6);
                $hotels = $hotelsQ->get()->map(fn($h)=>[
                    'type' => 'hotel',
                    'id'   => $h->id,
                    'name' => $h->title ?? $h->title_en,
                    'note' => $h->city ?? '',
                    'alpha2' => '',
                ]);
            }

            // Сначала города, потом отели
            $items = $cities->concat($hotels)->take(10)->values();

            return response()->json($items);
        } catch (\Throwable $e) {
            \Log::error('suggest failed', ['e'=>$e->getMessage()]);
            return response()->json([], 200);
        }
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
