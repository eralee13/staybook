<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;

class BookingCalendarPriceController extends Controller
{
    /**
     * Страница с FullCalendar (ресурсы + events пробрасываются в Blade).
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $user = Auth::user();

        // 1) Список отелей для селекта
        $hotelsQuery = Hotel::select('id', 'title')
            ->where('apiName', 'local')
            ->orderBy('title', 'asc');

        // если не админ/менеджер — только свои
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Manager')) {
            $hotelsQuery->where('user_id', $user->id);
        }
        $hotelslist = $hotelsQuery->get();

        $requestedId = (int) $request->hotel;
        $hotelId = $requestedId && $hotelslist->contains('id', $requestedId)
            ? $requestedId
            : optional($hotelslist->first())->id;

        // период (2 месяца наперёд)
        $startDate = Carbon::now()->startOfDay();
        $endDate   = Carbon::now()->copy()->addDays(60)->endOfDay();

        $meals = Meal::all()->keyBy('id');

        $hotel = Hotel::find($hotelId);
        $roomHotelId = $hotel?->exely_id ?: $hotelId;

        $rooms = Room::with('rates')
            ->where('hotel_id', $roomHotelId)
            ->get();

        $resources = [];
        $events    = [];

        // карта символов валют
        $symbolMap = [
            'USD' => '$', 'RUB' => '₽', 'KGS' => 'сом', 'UZS' => 'сўм',
            'KZT' => '₸', 'EUR' => '€', 'GBP' => '£'
        ];
        $fmt = fn($v, $sym) => is_numeric($v) ? ($sym . ' ' . (int)$v) : '—';

        // === ресурсы/события ===
        foreach ($rooms as $room) {
            $roomResId = 'room_' . $room->id;
            $resources[] = [
                'id'    => $roomResId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $code = $meals[$rate->meal_id]->code ?? null;
                $rateResId = $roomResId . '_rate_' . $rate->id;

                // узел тарифа
                $resources[] = [
                    'id'       => $rateResId,
                    'title'    => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomResId,
                ];

                // дочерние узлы для 1/2/3/4 гостей
                $occNodes = [
                    ['id' => $rateResId . '_p1', 'title' => '1 гость'],
                    ['id' => $rateResId . '_p2', 'title' => '2 гостя'],
                    ['id' => $rateResId . '_p3', 'title' => '3 гостя'],
                    ['id' => $rateResId . '_p4', 'title' => '4 гостя'],
                ];
                foreach ($occNodes as $n) {
                    $resources[] = ['id' => $n['id'], 'title' => $n['title'], 'parentId' => $rateResId];
                }

                // записи цен календаря по тарифу за период
                $priceBooks = Book::where('rate_id', $rate->id)
                    ->where('api_type', 'calendar_price')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($qq) use ($startDate, $endDate) {
                                $qq->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get(['id','arrivalDate','departureDate','price','price2','price3','price4','currency']);

                // индексируем по дате: последняя запись «правит»
                $byDate = [];
                foreach ($priceBooks as $b) {
                    $a = Carbon::parse($b->arrivalDate)->startOfDay();
                    $d = Carbon::parse($b->departureDate)->startOfDay();
                    foreach ($a->daysUntil($d) as $day) {
                        $k = $day->toDateString();
                        $byDate[$k] = [
                            'p1' => $b->price,
                            'p2' => $b->price2,
                            'p3' => $b->price3,
                            'p4' => $b->price4,
                            'ccy'=> $b->currency ?? $rate->currency ?? 'USD',
                        ];
                    }
                }

                $green = '#39bb43'; $red = '#d95d5d';
                $color = (($rate->availability ?? 0) > 0) ? $green : $red;

                foreach ($startDate->daysUntil($endDate) as $date) {
                    $ds = $date->toDateString();
                    $row = $byDate[$ds] ?? null;

                    $ccy = strtoupper($row['ccy'] ?? ($rate->currency ?? 'USD'));
                    $sym = $symbolMap[$ccy] ?? $ccy;

                    // фоллбек к базовым ценам тарифа
                    $p1 = $row['p1'] ?? $rate->price  ?? null;
                    $p2 = $row['p2'] ?? $rate->price2 ?? null;
                    $p3 = $row['p3'] ?? $rate->price3 ?? null;
                    $p4 = $row['p4'] ?? $rate->price4 ?? null;

                    // 4 отдельных события-ячейки
                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p1",
                        'title'           => $fmt($p1, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p1',
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p1, 'occ' => 1, 'currencySymbol' => $sym],
                    ];
                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p2",
                        'title'           => $fmt($p2, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p2',
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p2, 'occ' => 2, 'currencySymbol' => $sym],
                    ];
                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p3",
                        'title'           => $fmt($p3, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p3',
                        'allDay'          => true,
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p3, 'occ' => 3, 'currencySymbol' => $sym],
                    ];
                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p4",
                        'title'           => $fmt($p4, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p4',
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p4, 'occ' => 4, 'currencySymbol' => $sym],
                    ];
                }
            }
        }

        Log::debug('Final resources and events', [
            'resources_count' => count($resources),
            'events_count'    => count($events)
        ]);

        $eventsCount = count($events);
        $warning = $eventsCount === 0 ? 'Нет доступных предложений на выбранные даты.' : null;

        return view('auth.books.calendarprice.index', [
            'resources'  => $resources,
            'hotelslist' => $hotelslist,
            'events'     => $events,
            'request'    => $request,
            'warning'    => $warning,
            'hotel'      => $hotelId,
        ]);
    }

    /**
     * AJAX-источник для FullCalendar Scheduler (возвращает JSON).
     */
    public function getEvents(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => true, 'message' => 'Unauthorized'], 401);
        }

        $hotelId   = (int)$request->get('hotel_id');
        $startDate = Carbon::now()->startOfDay();
        $endDate   = Carbon::now()->copy()->addDays(60)->endOfDay();

        $hotel     = Hotel::find($hotelId);
        $roomQuery = Room::with('rates');

        if ($hotel && $hotel->exely_id) {
            $roomQuery->where('hotel_id', $hotel->exely_id);
        } else {
            $roomQuery->where('hotel_id', $hotelId);
        }

        $meals = Meal::all()->keyBy('id');
        $rooms = $roomQuery->get();

        $resources = [];
        $events    = [];

        $symbolMap = [
            'USD' => '$', 'RUB' => '₽', 'KGS' => 'сом', 'UZS' => 'сўм',
            'KZT' => '₸', 'EUR' => '€', 'GBP' => '£'
        ];
        $fmt = fn($v, $sym) => is_numeric($v) ? ($sym . ' ' . (int)$v) : '—';

        foreach ($rooms as $room) {
            $roomResId = 'room_' . $room->id;
            $resources[] = ['id' => $roomResId, 'title' => $room->title];

            foreach ($room->rates as $rate) {
                $code = $meals[$rate->meal_id]->code ?? null;
                $rateResId = $roomResId . '_rate_' . $rate->id;

                $resources[] = [
                    'id'       => $rateResId,
                    'title'    => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomResId,
                ];

                // дочерние узлы p1..p4
                foreach ([
                             ['id' => $rateResId . '_p1', 'title' => '1 гость'],
                             ['id' => $rateResId . '_p2', 'title' => '2 гостя'],
                             ['id' => $rateResId . '_p3', 'title' => '3 гостя'],
                             ['id' => $rateResId . '_p4', 'title' => '4 гостя'],
                         ] as $n) {
                    $resources[] = ['id' => $n['id'], 'title' => $n['title'], 'parentId' => $rateResId];
                }

                // записи календаря цен по тарифу за период
                $priceBooks = Book::where('rate_id', $rate->id)
                    ->where('api_type', 'calendar_price')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($qq) use ($startDate, $endDate) {
                                $qq->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get(['id','arrivalDate','departureDate','price','price2','price3','price4','currency']);

                $byDate = [];
                foreach ($priceBooks as $b) {
                    $a = Carbon::parse($b->arrivalDate)->startOfDay();
                    $d = Carbon::parse($b->departureDate)->startOfDay();
                    foreach ($a->daysUntil($d) as $day) {
                        $k = $day->toDateString();
                        $byDate[$k] = [
                            'p1' => $b->price,
                            'p2' => $b->price2,
                            'p3' => $b->price3,
                            'p4' => $b->price4,
                            'ccy'=> $b->currency ?? $rate->currency ?? 'USD',
                        ];
                    }
                }

                $green = '#39bb43'; $red = '#d95d5d';
                $color = (($rate->availability ?? 0) > 0) ? $green : $red;

                foreach ($startDate->daysUntil($endDate) as $date) {
                    $ds = $date->toDateString();
                    $row = $byDate[$ds] ?? null;

                    $ccy = strtoupper($row['ccy'] ?? ($rate->currency ?? 'USD'));
                    $sym = $symbolMap[$ccy] ?? $ccy;

                    $p1 = $row['p1'] ?? $rate->price  ?? null;
                    $p2 = $row['p2'] ?? $rate->price2 ?? null;
                    $p3 = $row['p3'] ?? $rate->price3 ?? null;
                    $p4 = $row['p4'] ?? $rate->price4 ?? null;

                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p1",
                        'title'           => $fmt($p1, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p1',
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p1, 'occ' => 1, 'currencySymbol' => $sym],
                    ];
                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p2",
                        'title'           => $fmt($p2, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p2',
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p2, 'occ' => 2, 'currencySymbol' => $sym],
                    ];
                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p3",
                        'title'           => $fmt($p3, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p3',
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p3, 'occ' => 3, 'currencySymbol' => $sym],
                    ];
                    $events[] = [
                        'id'              => "r{$rate->id}_{$ds}_p4",
                        'title'           => $fmt($p4, $sym),
                        'start'           => $ds,
                        'end'             => Carbon::parse($ds)->addDay()->format('Y-m-d'),
                        'resourceId'      => $rateResId . '_p4',
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'extendedProps'   => ['price' => $p4, 'occ' => 4, 'currencySymbol' => $sym],
                    ];
                }
            }
        }

        Log::debug('Final resources and events', [
            'resources_count' => count($resources),
            'events_count'    => count($events)
        ]);

        $warning = count($events) === 0 ? 'Нет доступных предложений на выбранные даты.' : null;

        return response()->json([
            'resources' => $resources,
            'events'    => $events,
            'warning'   => $warning,
        ]);
    }

    /**
     * POST: сохранить цены календаря (price..price4) на интервал.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hotel_id' => 'required|exists:hotels,id',
                'room_id'  => 'required|exists:rooms,id',
                'rate_id'  => 'required',                // "5" или "5_p3"
                'start'    => 'required|date',           // ИНКЛЮЗИВНЫЙ
                'end'      => 'required|date',           // ИНКЛЮЗИВНЫЙ
            ]);

            // rate_id: "123" или "123_p4"
            $ridRaw = (string)$request->input('rate_id');
            if (!preg_match('/^(\d+)(?:_p([1-4]))?$/', $ridRaw, $m)) {
                return response()->json(['error' => true, 'message' => 'Неверный rate_id'], 422);
            }
            $rateId = (int)$m[1];

            $startInc = Carbon::parse($request->input('start'))->startOfDay();
            $endInc   = Carbon::parse($request->input('end'))->startOfDay();
            if ($endInc->lt($startInc)) {
                $endInc = $startInc;
            }

            // цены: обновляем только присланные поля
            $updates = [];
            foreach (['price','price2','price3','price4'] as $col) {
                if ($request->has($col)) {
                    $val = $request->input($col);
                    $updates[$col] = ($val === '' || $val === null) ? null : (float)$val;
                }
            }
            if (empty($updates)) {
                return response()->json(['error' => true, 'message' => 'Не указана цена'], 422);
            }

            $hotelId = (int)$request->hotel_id;
            $roomId  = (int)$request->room_id;

            // ИНКЛЮЗИВНЫЙ период: каждый день -> запись [arrival = день, departure = день+1]
            $period = CarbonPeriod::create($startInc, $endInc);

            foreach ($period as $day) {
                $arrival   = $day->copy()->format('Y-m-d');
                $departure = $day->copy()->addDay()->format('Y-m-d');

                $book = \App\Models\Book::firstOrNew([
                    'title' => '',
                    'phone' => '',
                    'email' => '',
                    'sum' => 0,
                    'user_id' => 1,
                    'api_type'    => 'calendar_price',
                    'hotel_id'      => $hotelId,
                    'room_id'       => $roomId,
                    'rate_id'       => $rateId,
                    'arrivalDate'   => $arrival,
                    'departureDate' => $departure,
                ]);

                foreach ($updates as $col => $val) {
                    $book->$col = $val;
                }

                // обязательные поля, чтобы не падало (title NOT NULL)
                if (!$book->title) {
                    $book->title = "calendar_price_$rateId";
                }
                if (!$book->currency) {
                    $book->currency = 'USD';
                }
                if (!$book->book_token) {
                    do { $token = Str::random(40); } while (\App\Models\Book::where('book_token',$token)->exists());
                    $book->book_token = $token;
                }
                $book->status = 'Pending';

                $book->save();
            }

            return response()->json(['success' => true]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => true, 'message' => implode("\n", $e->validator->errors()->all())], 422);
        } catch (\Throwable $e) {
            \Log::error('calendar_price store error', ['msg' => $e->getMessage()]);
            return response()->json(['error' => true, 'message' => 'Server error'], 500);
        }
    }

    private function getRoomTitleByRoomId($externalRoomId): string
    {
        $room = \App\Models\Room::where('exely_id', $externalRoomId)->first();
        return $room?->title ?? 'Exely Room #' . $externalRoomId;
    }
}