<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meal;
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

        // если админ — показываем все, иначе только свои
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Manager')) {
            $hotelsQuery->where('user_id', $user->id);
        }


        $hotelslist = $hotelsQuery->get();

        $requestedId = (int) $request->hotel;
        $hotelId = $requestedId && $hotelslist->contains('id', $requestedId)
            ? $requestedId
            : optional($hotelslist->first())->id;

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

        // Карта символов валют
        $symbolMap = [
            'USD' => '$', 'RUB' => '₽', 'KGS' => 'сом', 'UZS' => 'сўм',
            'KZT' => '₸', 'EUR' => '€', 'GBP' => '£'
        ];

        // Локальные тарифы
        foreach ($rooms as $room) {
            $roomId = 'room_' . $room->id;
            $resources[] = [
                'id'    => $roomId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $code = $meals[$rate->meal_id]->code ?? null;
                $resourceId = $roomId . '_rate_' . $rate->id;

                $resources[] = [
                    'id'       => $resourceId,
                    'title'    => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomId,
                ];

                // Все брони по тарифу за нужный период (для отметок и возможного override цены)
                $bookings = Book::where('rate_id', $rate->id)
                    ->where('api_type', 'calendar_price') // ← только ценовые записи!
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get();

                $bookingsByDate = [];

                foreach ($bookings as $book) {
                    $arrival   = Carbon::parse($book->arrivalDate)->startOfDay();
                    $departure = Carbon::parse($book->departureDate)->startOfDay();

                    foreach ($arrival->daysUntil($departure) as $date) {
                        $dateStr = $date->format('Y-m-d');

                        // Использовать цену из брони только если она задана вручную (override)
                        $useBookPrice = !is_null($book->price);

                        $bookingsByDate[$dateStr] = [
                            'occupied' => true,
                            'price'    => $useBookPrice ? $book->price : null, // ключ всегда есть
                            'currency' => $useBookPrice ? ($book->currency ?? $rate->currency ?? '$') : null,
                            'id'       => $book->id,
                        ];
                    }
                }

                // Рендер событий на период
                foreach ($startDate->daysUntil($endDate) as $date) {
                    $dateStr = $date->format('Y-m-d');

                    // Безопасные фоллбеки: если нет брони с override — берем тариф
                    $entry = $bookingsByDate[$dateStr] ?? [];
                    $effectivePrice    = $entry['price']    ?? $rate->price;
                    $effectiveCurrency = $entry['currency'] ?? ($rate->currency ?? '$');

                    $symbol = $symbolMap[strtoupper($effectiveCurrency)] ?? $effectiveCurrency;

                    $color = (($rate->availability ?? 0) > 0) ? '#39bb43' : '#d95d5d';

                    $events[] = [
                        'id'              => 'local_' . $rate->id . '_' . $dateStr,
                        'title'           => trim($symbol . ' ' . $effectivePrice),
                        'start'           => $dateStr,
                        // эксклюзивный end для FullCalendar
                        'end'             => Carbon::parse($dateStr)->addDay()->format('Y-m-d'),
                        'resourceId'      => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
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

        Log::debug('⚠️ Warning message evaluation', [
            'events_count' => $eventsCount,
            'warning'      => $warning,
        ]);

        return view('auth.books.calendarprice.index', [
            'resources'  => $resources,
            'hotelslist' => $hotelslist,
            'events'     => $events,
            'request'    => $request,
            'warning'    => $warning,
            'hotel'      => $hotelId
        ]);
    }

    public function getEvents(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'error'   => true,
                'message' => 'Unauthorized'
            ], 401);
        }

        $hotelId   = $request->get('hotel_id');
        $startDate = Carbon::now()->startOfDay();
        $endDate   = Carbon::now()->copy()->addDays(60)->endOfDay();

        $hotel     = Hotel::find($hotelId);
        $resources = [];
        $events    = [];

        $roomQuery = Room::with('rates');

        if ($hotel && $hotel->exely_id) {
            $roomQuery->where('hotel_id', $hotel->exely_id);
        } else {
            $roomQuery->where('hotel_id', $hotelId);
        }

        $meals = Meal::all()->keyBy('id');
        $rooms = $roomQuery->get();

        $symbolMap = [
            'USD' => '$', 'RUB' => '₽', 'KGS' => 'сом', 'UZS' => 'сўм',
            'KZT' => '₸', 'EUR' => '€', 'GBP' => '£'
        ];

        // Локальные тарифы
        foreach ($rooms as $room) {
            $roomId = 'room_' . $room->id;
            $resources[] = ['id' => $roomId, 'title' => $room->title];

            foreach ($room->rates as $rate) {
                // брони тарифа за период
                $bookings = Book::where('rate_id', $rate->id)
                    ->where('api_type', 'calendar_price') // ← только ценовые записи!
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get();

                $code = $meals[$rate->meal_id]->code ?? null;
                $resourceId = $roomId . '_rate_' . $rate->id;
                $resources[] = [
                    'id'       => $resourceId,
                    'title'    => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomId,
                ];

                $bookingsByDate = [];

                foreach ($bookings as $book) {
                    $arrival   = Carbon::parse($book->arrivalDate)->startOfDay();
                    $departure = Carbon::parse($book->departureDate)->startOfDay();

                    foreach ($arrival->daysUntil($departure) as $date) {
                        $dateStr = $date->format('Y-m-d');

                        $useBookPrice = !is_null($book->price);

                        $bookingsByDate[$dateStr] = [
                            'price'    => $useBookPrice ? $book->price : null,
                            'currency' => $useBookPrice ? ($book->currency ?? $rate->currency ?? '$') : null,
                            'id'       => $book->id,
                        ];
                    }
                }

                foreach ($startDate->daysUntil($endDate) as $date) {
                    $dateStr = $date->format('Y-m-d');

                    $entry = $bookingsByDate[$dateStr] ?? [];
                    $effectivePrice    = $entry['price']    ?? $rate->price;
                    $effectiveCurrency = $entry['currency'] ?? ($rate->currency ?? '$');

                    $symbol = $symbolMap[strtoupper($effectiveCurrency)] ?? $effectiveCurrency;
                    $color  = (($rate->availability ?? 0) > 0) ? '#39bb43' : '#d95d5d';

                    $events[] = [
                        'id'              => 'local_' . $rate->id . '_' . $dateStr,
                        'title'           => trim($symbol . ' ' . $effectivePrice),
                        'start'           => $dateStr,
                        'end'             => Carbon::parse($dateStr)->addDay()->format('Y-m-d'),
                        'resourceId'      => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
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

    public function store(Request $request)
    {
        try {
            // ✅ Шаг 1: Валидация входных данных
            $validated = $request->validate([
                'start'     => 'required|date',
                'end'       => 'required|date|after_or_equal:start',
                'rate_id'   => 'required|exists:rates,id',
                'room_id'   => 'required|exists:rooms,id',
                'hotel_id'  => 'required|exists:hotels,id',
                'allotment' => 'required|integer|min:0',
                'currency'  => 'nullable|string|max:3',
            ]);

            $start     = Carbon::parse($validated['start'])->format('Y-m-d');
            $end       = Carbon::parse($validated['end'])->format('Y-m-d');
            $rateId    = $validated['rate_id'];
            $roomId    = $validated['room_id'];
            $hotelId   = $validated['hotel_id'];
            $allotment = $validated['allotment'];

            // ✅ Шаг 2: Найти тариф и проверить его принадлежность номеру
            $rate = Rate::find($rateId);
            if ((int) $rate->room_id !== (int) $roomId) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Несоответствие тарифа и номера.'
                ]);
            }

            $now = now()->setTimezone('Asia/Bishkek');
            $checkinDate = Carbon::parse($validated['start'])->startOfDay();

            if ($rate->booking_open_time) {
                $openAt = Carbon::parse($checkinDate->format('Y-m-d') . ' ' . $rate->booking_open_time);
                if ($now->lt($openAt)) {
                    return response()->json([
                        'error'   => true,
                        'message' => 'Бронирование ещё не открыто для этого тарифа.'
                    ]);
                }
            }

            if ($rate->booking_close_time) {
                $closeAt = Carbon::parse($checkinDate->format('Y-m-d') . ' ' . $rate->booking_close_time);
                if ($now->gt($closeAt)) {
                    return response()->json([
                        'error'   => true,
                        'message' => 'Бронирование закрыто для этого тарифа.'
                    ]);
                }
            }

            // ✅ Шаг 4: Генерация уникального токена брони
            do {
                $token = Str::random(40);
            } while (Book::where('book_token', $token)->exists());

            // ✅ Ручная цена (override) — если не задана, храним NULL, чтобы календарь брал тариф
            $raw = $request->input('alltoment', null);
            $bookPrice = ($raw === null || $raw === '') ? null : (float) $raw;
            $bookCurrency = $request->input('currency', $rate->currency ?? '$');

            // ✅ Шаг 5: Создание брони/блокировки
            Book::create([
                'book_token'    => $token,
                'title'         => '',
                'hotel_id'      => $hotelId,
                'room_id'       => $roomId,
                'rate_id'       => $rateId,
                'phone'         => '',
                'email'         => '',
                'comment'       => '',
                // 'adult'      => 1,
                'child'         => null,
                'price'         => $allotment,
                'sum'           => 0,
                'currency'      => $bookCurrency,
                'arrivalDate'   => $start,
                'departureDate' => $end,
                'status'        => 'Pending',
                'user_id'       => Auth::id(),
                'api_type'      => 'calendar_price',
                // при наличии отдельной колонки под квоту можно добавить 'allotment' => $allotment,
            ]);

            // ✅ Шаг 6: Управление квотой (по необходимости)
            // $rate->availability -= (int) $allotment;
            // $rate->save();

            return response()->json(['success' => true, 'message' => 'Бронь успешно создана.']);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => true,
                'message' => implode('<br>', $e->validator->errors()->all())
            ]);
        }
        catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Ошибка сервера: ' . $th->getMessage()
            ]);
        }
    }

    private function getRoomTitleByRoomId($externalRoomId): string
    {
        $room = \App\Models\Room::where('exely_id', $externalRoomId)->first();
        return $room?->title ?? 'Exely Room #' . $externalRoomId;
    }
}
