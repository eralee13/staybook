<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;

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
                $resources[] = [
                    'id' => $parentId . '_rate_' . $rate->id,
                    'title' => $rate->title,
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
        if (!auth()->check()) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthorized'
            ], 401);
        }

        $hotelId = $request->get('hotel_id');
        $startDate = Carbon::parse($request->input('start'))->startOfDay();
        $endDate = Carbon::parse($request->input('end'))->endOfDay();

        $hotel = \App\Models\Hotel::find($hotelId);

        $resources = [];
        $events = [];

        // Local DB data
        $rooms = Room::with('rates')->where('hotel_id', $hotelId)->get();
        foreach ($rooms as $room) {
            $roomId = 'room_' . $room->id;
            $resources[] = [
                'id' => $roomId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $resourceId = $roomId . '_rate_' . $rate->id;
                $resources[] = [
                    'id' => $resourceId,
                    'title' => $rate->title,
                    'parentId' => $roomId,
                ];

                $period = $startDate->daysUntil($endDate);
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $color = $rate->availability > 0 ? '#39bb43' : '#d95d5d';
                    $events[] = [
                        'id' => 'local_' . $rate->id . '_' . $dateStr,
                        'title' => (string) $rate->availability,
                        'start' => $dateStr,
                        'end' => $dateStr,
                        'resourceId' => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                    ];
                }
            }
        }

        // Exely API data
        if ($hotel && $hotel->exely_id) {
            $availabilityData = $this->fetchExelyAvailability($hotel->exely_id, $startDate, $endDate);
            Log::debug('Parsed Exely availability', ['items' => $availabilityData]);

            // Добавляем виртуального родителя для Exely тарифов
            $resources[] = [
                'id' => 'exely_virtual_parent',
                'title' => 'Exely тарифы',
            ];

            $grouped = collect($availabilityData)->groupBy(function ($item) {
                return $item['room_id'] ?? 'virtual_' . $item['rate_id'];
            });

            foreach ($grouped as $roomId => $rates) {
                $virtualRoomId = 'exely_room_' . $roomId;
                // Используем корректное название комнаты
                $roomTitle = $rates->first()['room_name'] ?? 'Exely Room #' . $roomId;
                $resources[] = [
                    'id' => $virtualRoomId,
                    'title' => $roomTitle,
                    'parentId' => 'exely_virtual_parent',
                ];

                $ratesById = collect($rates)->groupBy('rate_id');

                foreach ($ratesById as $rateId => $rateEntries) {
                    $virtualRateId = 'exely_room_' . $roomId . '_rate_' . $rateId;
                    $resources[] = [
                        'id' => $virtualRateId,
                        'title' => $rateEntries->first()['rate_name'] ?? 'Rate #' . $rateId,
                        'parentId' => $virtualRoomId,
                    ];

                    $period = $startDate->daysUntil($endDate);
                    foreach ($period as $date) {
                        $dateStr = $date->format('Y-m-d');
                        $matched = $rateEntries->firstWhere('date', $dateStr);

                        if ($matched) {
                            $color = $matched['availability'] > 0 ? '#39bb43' : '#d95d5d';
                            $events[] = [
                                'id' => $virtualRateId . '_' . $dateStr,
                                'title' => (string) $matched['availability'],
                                'start' => $dateStr,
                                'end' => $dateStr,
                                'resourceId' => $virtualRateId,
                                'backgroundColor' => $color,
                                'borderColor' => $color,
                                'extendedProps' => [
                                    'availability' => $matched['availability'],
                                    'rate_id' => $rateId,
                                    'room_id' => $roomId,
                                    'rate_name' => $rateEntries->first()['rate_name'] ?? '',
                                    'source' => 'exely'
                                ]
                            ];
                        }
                    }
                }
            }
        }

        return response()->json([
            'resources' => $resources,
            'events' => $events,
        ]);
    }

    private function fetchExelyAvailability($exelyId, $startDate, $endDate): array
    {
        $results = [];

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $today = Carbon::today();

        if ($startDate->lessThan($today)) {
            $startDate = $today;
        }

        $period = $startDate->daysUntil($endDate);

        foreach ($period as $date) {
            $params = [
                'arrivalDate' => $date->format('Y-m-d'),
                'departureDate' => $date->copy()->addDay()->format('Y-m-d'),
                'adults' => 1,
                'includeExtraStays' => 'false',
                'includeExtraServices' => 'false',
            ];

            $query = http_build_query($params);
            $url = rtrim(config('services.exely.base_url'), '/') . "/search/v1/properties/{$exelyId}/room-stays?{$query}";

            $response = Http::withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['roomStays']) && is_array($data['roomStays'])) {
                    foreach ($data['roomStays'] as $roomStay) {
                        if (!isset($roomStay['id'])) continue;

                        $results[] = [
                            'rate_id' => $roomStay['id'],
                            'room_id' => $roomStay['roomId'] ?? ('rate_' . $roomStay['id']),
                            'rate_name' => $roomStay['roomName'] ?? '',
                            'availability' => isset($roomStay['availability']) ? (int) $roomStay['availability'] : 0,
                            'date' => $date->format('Y-m-d'),
                        ];
                    }
                }
            } else {
                Log::warning('Exely API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);
            }
        }

        return $results;
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
