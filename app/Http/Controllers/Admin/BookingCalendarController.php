<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Meal;

class BookingCalendarController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $hotelId = $request->get('hotel_id') ?? 14;

        $hotelslist = Hotel::select('id', 'title')
            ->orderBy('title', 'asc')
            ->get();

        $startDate = Carbon::now()->startOfMonth()->startOfDay();
        $endDate = Carbon::now()->endOfMonth()->endOfDay();

        $books = Book::with('room.rates')
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotelId))
            ->whereBetween('arrivalDate', [$startDate, $endDate])
            ->get();

        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        $meals = Meal::all()->keyBy('id'); 

        // 🧱 Resources
        $resources = [];
        foreach ($rooms as $room) {
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) {
                continue;
            }

            $parentId = 'room_' . $room->id;

            $resources[] = [
                'id' => $parentId,
                'title' => $room->title,
            ];
            
            foreach ($validRates as $rate) {
                $code = $meals[$rate->meal_id]->code ?? null;
                $resources[] = [
                    'id' => $parentId . '_rate_' . $rate->id,
                    'title' => $rate->title .' - '. ($code ? "({$code})" : ''),
                    'parentId' => $parentId,
                ];
            }
        }

        // 📆 Bookings map
        $bookingsMap = [];
        foreach ($books as $book) {
            $room = $book->room; // ✅ исправлено
            if (!$room) continue;

            $validRates = $room->rates->filter();
            foreach ($validRates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;
                $period = Carbon::parse($book->arrivalDate)->daysUntil(Carbon::parse($book->departureDate));

                foreach ($period as $date) {
                    $bookingsMap[$resourceId][$date->format('Y-m-d')] = [
                        'id' => $book->id,
                        'status' => $book->status,
                        'price' => $book->sum,
                        'currency' => $book->currency,
                        'phone' => $book->phone,
                        'email' => $book->email,
                        'allotment' => $rate->availability,
                        'adult' => $book->adult,
                    ];
                }
            }
        }

        // 📌 Events
        $events = [];
        foreach ($rooms as $room) {
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) {
                continue;
            }

            foreach ($validRates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->startOfDay();

                if ($end->lessThanOrEqualTo($start)) {
                    return response()->json(['events' => [], 'resources' => []]); // или throw, если надо
                }

                $period = $start->daysUntil($end);


                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $tomorrow = now()->addDay()->startOfDay();

                    if (isset($bookingsMap[$resourceId][$dateStr])) {
                        $booking = $bookingsMap[$resourceId][$dateStr];
                        $color = match ($booking['status']) {
                            'Cancelled' => '#e19d22',
                            'Reserved', 'Pending' => '#d95d5d',
                            default => '#39bb43',
                        };

                        $quota = $booking['adult'] ?? '';

                        $events[] = [
                            'id' => $booking['id'] . '_' . $dateStr,
                            'title' => $quota,
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'color' => $color,
                            'extendedProps' => [
                                'status' => $color,
                                'rate_id' => $rate->id,
                                'description' => "{$booking['price']} {$booking['currency']}<br>{$booking['phone']}<br>{$booking['email']}",
                                'currency' => $booking['currency'],
                                'price' => $booking['price'],
                            ]
                        ];
                    } elseif ($date->gte($tomorrow)) {
                        $events[] = [
                            'id' => 'free_' . $rate->id . '_' . $dateStr,
                            'title' => $rate->availability ?? '—',
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'color' => '#39bb43',
                            'extendedProps' => [
                                'room_id' => $room->id,
                                'rate_id' => $rate->id,
                            ]
                        ];
                    }
                }
            }
        }

        // 👀 Отладка при пустом выводе
        if (empty($resources)) {
            Log::warning('Resources пусты для отеля: ' . $hotelId);
        }

        if (empty($events)) {
            Log::warning('Events пусты для отеля: ' . $hotelId);
        }

        return view('auth.books.index', [
            'resources' => $resources,
            'hotelslist' => $hotelslist,
            'events' => $events
        ]);
    }

    public function getEvents(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $hotelId = $request->get('hotel_id') ?? 14;

        $startDate = $request->input('start')
            ? Carbon::parse($request->input('start'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->input('end')
            ? Carbon::parse($request->input('end'))->endOfDay()
            : now()->endOfMonth();

        $books = Book::with(['room', 'rate'])
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotelId))
            ->whereBetween('arrivalDate', [$startDate, $endDate])
            ->get();

        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        $meals = Meal::all()->keyBy('id');

        $resources = [];
        foreach ($rooms as $room) {
            $parentId = 'room_' . $room->id;

            $resources[] = [
                'id' => $parentId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $code = $meals[$rate->meal_id]->code ?? null;
                $resources[] = [
                    'id' => $parentId . '_rate_' . $rate->id,
                    'title' => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $parentId,
                ];
            }
        }

        $bookingsMap = [];
        foreach ($books as $book) {
            $room = $book->room;
            $rate = $book->rate;
            if (!$room || !$rate) continue;

            $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;

            $period = Carbon::parse($book->arrivalDate)->daysUntil(Carbon::parse($book->departureDate));

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');

                $bookingsMap[$resourceId][$dateStr] = [
                    'id' => $book->id,
                    'status' => $book->status,
                    'price' => $book->sum,
                    'currency' => $book->currency,
                    'phone' => $book->phone,
                    'email' => $book->email,
                    'adult' => $book->adult,
                ];
            }
        }

        $events = [];
        $period = $startDate->daysUntil($endDate);

        foreach ($rooms as $room) {
            foreach ($room->rates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');

                    if (isset($bookingsMap[$resourceId][$dateStr])) {
                        $booking = $bookingsMap[$resourceId][$dateStr];

                        $color = $booking['adult'] > 0
                            ? '#39bb43'
                            : ($booking['status'] === 'CANCELLED' ? '#e19d22' : '#d95d5d');

                        $events[] = [
                            'id' => $booking['id'] . '_' . $dateStr,
                            'title' => (string) $booking['adult'],
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'backgroundColor' => $color,
                            'borderColor' => $color,
                            'extendedProps' => [
                                'description' => "{$booking['price']} {$booking['currency']}<br>{$booking['phone']}<br>{$booking['email']}",
                                'price' => $booking['price'],
                                'currency' => $booking['currency'],
                            ]
                        ];
                    } else {
                        $availability = (int) $rate->availability;

                        $events[] = [
                            'id' => 'free_' . $rate->id . '_' . $dateStr,
                            'title' => (string) $availability,
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'backgroundColor' => $availability === 0 ? '#d95d5d' : '#39bb43',
                            'borderColor' => $availability === 0 ? '#d95d5d' : '#39bb43',
                            'extendedProps' => [
                                'room_id' => $room->id,
                                'rate_id' => $rate->id,
                                'open_time' => $rate->open_time,
                                'close_time' => $rate->close_time,
                            ]
                        ];
                    }
                }
            }
        }

        return response()->json([
            'events' => $events,
            'resources' => $resources,
        ]);
    }

    public function store(Request $request)
    {
        try {
            // ✅ Шаг 1: Валидация входных данных
            $validated = $request->validate([
                'start' => 'required|date',
                'end' => 'required|date|after_or_equal:start',
                'rate_id' => 'required|exists:rates,id',
                'room_id' => 'required|exists:rooms,id',
                'hotel_id' => 'required|exists:hotels,id',
                'allotment' => 'required|integer|min:0',
            ]);

            $start = Carbon::parse($validated['start'])->format('Y-m-d');
            $end = Carbon::parse($validated['end'])->format('Y-m-d');
            $rateId = $validated['rate_id'];
            $roomId = $validated['room_id'];
            $hotelId = $validated['hotel_id'];
            $allotment = $validated['allotment'];

            // ✅ Шаг 2: Найти тариф и проверить его принадлежность номеру
            $rate = Rate::find($rateId);
            if ((int) $rate->room_id !== (int) $roomId) {
                return response()->json([
                    'error' => true,
                    'message' => 'Несоответствие тарифа и номера.'
                ]);
            }

            // ✅ Шаг 3: Проверка квоты
            if ($rate->availability < $allotment) {
                return response()->json([
                    'error' => true,
                    'message' => 'Недостаточно квоты на выбранные даты.'
                ]);
            }

            $now = now()->setTimezone('Asia/Bishkek');
            $checkinDate = Carbon::parse($validated['start'])->startOfDay();

            if ($rate->booking_open_time) {
                $openAt = Carbon::parse($checkinDate->format('Y-m-d') . ' ' . $rate->booking_open_time);
                if ($now->lt($openAt)) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Бронирование ещё не открыто для этого тарифа.'
                    ]);
                }
            }

            if ($rate->booking_close_time) {
                $closeAt = Carbon::parse($checkinDate->format('Y-m-d') . ' ' . $rate->booking_close_time);
                if ($now->gt($closeAt)) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Бронирование закрыто для этого тарифа.'
                    ]);
                }
            }


            // ✅ Шаг 4: Генерация уникального токена брони
            do {
                $token = Str::random(40);
            } while (Book::where('book_token', $token)->exists());

            // ✅ Шаг 5: Создание брони
            $book = Book::create([
                'book_token' => $token,
                'title' => '',
                'title2' => '',
                'hotel_id' => $hotelId,
                'room_id' => $roomId,
                'rate_id' => $rateId,
                'phone' => '',
                'email' => '',
                'comment' => '',
                'adult' => $allotment,
                'child' => null,
                'price' => null,
                'sum' => 0,
                'currency' => '',
                'arrivalDate' => $start,
                'departureDate' => $end,
                'status' => 'Pending',
                'user_id' => Auth::id(),
                'api_type' => 'calendar',
            ]);

            // ✅ Шаг 6: Уменьшение квоты
//            $rate->availability -= $allotment;
//            $rate->save();

            return response()->json(['success' => true, 'message' => 'Бронь успешно создана.']);
        }

            // Обработка ошибок валидации (Laravel automatically throws ValidationException)
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => implode('<br>', $e->validator->errors()->all())
            ]);
        }

            // Общая защита
        catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => 'Ошибка сервера: ' . $th->getMessage()
            ]);
        }
    }

}
