<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchExelyAvailabilityJob;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        $hotelId = $request->get('hotel_id');
        $hotelslist = Hotel::select('id', 'title')->orderBy('title', 'asc')->get();

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->copy()->addDays(5)->endOfDay();

        Book::with('room.rates')
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotelId))
            ->whereBetween('arrivalDate', [$startDate, $endDate])
            ->get();

        $meals = Meal::all()->keyBy('id');

        $hotel = Hotel::find($hotelId);
        $roomHotelId = $hotel?->exely_id ?: $hotelId;
        $rooms = Room::with('rates')->where('hotel_id', $roomHotelId)->get();

        $resources = [];
        $events = [];


        //local
        foreach ($rooms as $room) {
            $roomId = 'room_' . $room->id;
            $resources[] = [
                'id' => $roomId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $code = $meals[$rate->meal_id]->code ?? null;
                $resourceId = $roomId . '_rate_' . $rate->id;
                $resources[] = [
                    'id' => $resourceId,
                    'title' => $rate->title .' - '. ($code ? "({$code})" : ''),
                    'parentId' => $roomId,
                ];


                $bookings = Book::where('rate_id', $rate->id)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get();

                $adultByDate = [];

                foreach ($bookings as $book) {
                    $arrival = Carbon::parse($book->arrivalDate)->startOfDay();
                    $departure = Carbon::parse($book->departureDate)->startOfDay();

                    foreach ($arrival->daysUntil($departure) as $date) {
                        $dateStr = $date->format('Y-m-d');
                        $adultByDate[$dateStr] = ($adultByDate[$dateStr] ?? $rate->avaibility) + $book->adult;
                    }
                }

                $period = $startDate->daysUntil($endDate);
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $color = $rate->availability > 0 ? '#39bb43' : '#d95d5d';
                    $events[] = [
                        'id' => 'local_' . $rate->id . '_' . $dateStr,
                        'title' => $book->adult ?? $rate->availability,
                        'start' => $dateStr,
                        'end' => $dateStr,
                        'resourceId' => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                    ];
                }
            }
        }


        //Exely
        if ($hotel && $hotel->exely_id) {
            $params = [
                'arrivalDate' => $startDate->format('Y-m-d'),
                'departureDate' => $endDate->format('Y-m-d'),
                'adults' => 1,
                'includeExtraStays' => 'false',
                'includeExtraServices' => 'false',
            ];
            $url = rtrim(config('services.exely.base_url'), '/') . "/search/v1/properties/{$hotel->exely_id}/room-stays?" . http_build_query($params);

            $response = Http::withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept' => 'application/json',
            ])->get($url);

            // Лог статуса и URL
            Log::debug('📤 Exely API call', [
                'url' => $url,
                'status' => $response->status(),
            ]);

// Проверка тела ответа
            if (!$response->successful()) {
                Log::error('❌ Ошибка Exely API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();

            if (empty($data['roomStays'])) {
                Log::warning('⚠️ Exely вернул пустой roomStays', ['response' => $data]);
                $exelyEmpty = true;
            } else {
                foreach ($data['roomStays'] as $stay) {
                    $roomId = $stay['roomType']['id'] ?? null;
                    $rateName = $stay['ratePlan']['name'] ?? 'Unknown';
                    $availability = $stay['availability'] ?? 0;

                    $room = Room::where('exely_id', $roomId)->first();

                    if (!$room) {
                        Log::warning('⛔ Комната из Exely не найдена в базе', [
                            'exely_id' => $roomId,
                            'rate_name' => $rateName,
                            'availability' => $availability,
                        ]);
                    } else {
                        Log::info('✅ Найден тариф Exely', [
                            'room_id' => $roomId,
                            'rate_name' => $rateName,
                            'availability' => $availability,
                            'room_local_id' => $room->id,
                        ]);
                    }
                }
            }


            if ($response->successful() && isset($response['roomStays'])) {
                foreach ($response['roomStays'] as $rateItem) {
                    $room = Room::where('exely_id', $rateItem['roomType']['id'])->first();
                    if (!$room) continue;

                    $roomId = 'room_' . $room->id;
                    $rateId = $rateItem['ratePlan']['id'] ?? $rateItem['checksum'] ?? Str::random(6);
                    $rateName = $rateItem['ratePlan']['name'] ?? 'API Rate';
                    $resourceId = $roomId . '_rate_' . $rateId;

                    $resources[] = [
                        'id' => $resourceId,
                        'title' => $rateName,
                        'parentId' => $roomId,
                    ];


                    $period = $startDate->daysUntil($endDate);
                    foreach ($period as $date) {
                        $dateStr = $date->format('Y-m-d');
                        $color = $rateItem['availability'] > 0 ? '#39bb43' : '#d95d5d';

                        $events[] = [
                            'id' => $resourceId . '_' . $dateStr,
                            'title' => $rateItem['availability'],
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'backgroundColor' => $color,
                            'borderColor' => $color,
                        ];
                    }
                }
            }
        }

        Log::debug('Final resources and events', [
            'resources_count' => count($resources),
            'events_count' => count($events),
        ]);

        $eventsCount = count($events);
        $warning = $eventsCount === 0 ? 'Нет доступных предложений на выбранные даты.' : null;

        Log::debug('⚠️ Warning message evaluation', [
            'events_count' => $eventsCount,
            'warning' => $warning,
        ]);

        return view('auth.books.index', [
            'resources' => $resources,
            'hotelslist' => $hotelslist,
            'events' => $events,
            'request' => $request,
            'exelyEmpty' => $exelyEmpty ?? false,
            'warning' => $warning,
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
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->copy()->addDays(5)->endOfDay();

        $hotel = Hotel::find($hotelId);
        $resources = [];
        $events = [];

        $roomQuery = Room::with('rates');

        if ($hotel && $hotel->exely_id) {
            $roomQuery->where('hotel_id', $hotel->exely_id);
        } else {
            $roomQuery->where('hotel_id', $hotelId);
        }

        $meals = Meal::all()->keyBy('id');
        $rooms = Room::with('rates')->where('hotel_id', $hotelId)->get();

        // Локальные тарифы
        foreach ($rooms as $room) {
            $roomId = 'room_' . $room->id;
            $resources[] = ['id' => $roomId, 'title' => $room->title];

            foreach ($room->rates as $rate) {
                $bookings = Book::where('rate_id', $rate->id)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })->get();

                $adultByDate = [];
                foreach ($bookings as $book) {
                    $arrival = Carbon::parse($book->arrivalDate)->startOfDay();
                    $departure = Carbon::parse($book->departureDate)->startOfDay();

                    foreach ($arrival->daysUntil($departure) as $date) {
                        $dateStr = $date->format('Y-m-d');
                        $adultByDate[$dateStr] = $book->adult ?? $rate->avaibility;
                    }
                }

                $code = $meals[$rate->meal_id]->code ?? null;
                $resourceId = $roomId . '_rate_' . $rate->id;
                $resources[] = [
                    'id' => $resourceId,
                    'title' => $rate->title .' - '. ($code ? "({$code})" : ''),
                    'parentId' => $roomId,
                ];

                foreach ($startDate->daysUntil($endDate) as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $adultCount = $adultByDate[$dateStr] ?? $rate->availability;
                    $color = $rate->availability > 0 ? '#39bb43' : '#d95d5d';
                    $events[] = [
                        'id' => 'local_' . $rate->id . '_' . $dateStr,
                        'title' => (string) $adultCount,
                        'start' => $dateStr,
                        'end' => $dateStr,
                        'resourceId' => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                    ];
                }
            }
        }

        // Exely API тарифы
        $rooms = $roomQuery->get()->keyBy('exely_id');
        if ($hotel && $hotel->exely_id) {
            $params = [
                'arrivalDate' => $startDate->format('Y-m-d'),
                'departureDate' => $endDate->copy()->addDay()->format('Y-m-d'),
                'adults' => 1,
                'includeExtraStays' => 'false',
                'includeExtraServices' => 'false',
            ];

            $url = rtrim(config('services.exely.base_url'), '/') . "/search/v1/properties/{$hotel->exely_id}/room-stays?" . http_build_query($params);
            $response = Http::withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept' => 'application/json',
            ])->get($url);

            Log::debug('📤 Exely API call', ['url' => $url, 'status' => $response->status()]);

            if (!$response->successful()) {
                Log::error('❌ Ошибка Exely API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'resources' => [],
                    'events' => [],
                    'warning' => 'Exely API вернул ошибку при получении данных.'
                ]);
            }

            $data = $response->json();

            if (empty($data['roomStays'])) {
                Log::warning('⚠️ Exely вернул пустой roomStays', ['response' => $data]);

                return response()->json([
                    'resources' => [],
                    'events' => [],
                    'warning' => 'Exely не вернул доступных тарифов на выбранные даты.'
                ]);
            }

            foreach ($data['roomStays'] ?? [] as $stay) {
                $roomExelyId = $stay['roomType']['id'] ?? null;
                $room = $rooms->get($roomExelyId);
                if (!$room || !isset($stay['availability'])) continue;

                $roomId = 'room_' . $room->id;
                $rateId = $stay['ratePlan']['id'] ?? $stay['checksum'] ?? Str::uuid();
                $resourceId = $roomId . '_rate_' . $rateId;
                $rateName = $stay['fullPlacementsName'] ?? $stay['ratePlan']['name'] ?? 'Rate';

                if (!collect($resources)->contains('id', $roomId)) {
                    $resources[] = ['id' => $roomId, 'title' => $room->title];
                }

                $resources[] = [
                    'id' => $resourceId,
                    'title' => $rateName,
                    'parentId' => $roomId,
                ];

                $availability = $stay['availability'];

                foreach ($startDate->daysUntil($endDate->copy()->addDay()) as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $color = $availability > 0 ? '#39bb43' : '#d95d5d';

                    $events[] = [
                        'id' => $resourceId . '_' . $dateStr,
                        'title' => (string) $availability,
                        'start' => $dateStr,
                        'end' => $dateStr,
                        'resourceId' => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                    ];
                }
            }
        }

        Log::debug('Final resources and events', [
            'events_count' => count($events)
        ]);

        $warning = count($events) === 0 ? 'Нет доступных предложений на выбранные даты.' : null;

        // Если есть предупреждение (Exely warning), оно уже возвращено выше. Здесь только обычный ответ.
        return response()->json([
            'resources' => $resources,
            'events' => $events,
            'warning' => $warning
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

    private function getRoomTitleByRoomId($externalRoomId): string
    {
        $room = \App\Models\Room::where('exely_id', $externalRoomId)->first();
        return $room?->title ?? 'Exely Room #' . $externalRoomId;
    }

}