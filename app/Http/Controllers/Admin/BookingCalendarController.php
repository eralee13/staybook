<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\FetchExelyAvailabilityJob;
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
use App\Models\Meal;

class BookingCalendarController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $user = Auth::user();

        // 1) Список отелей для селекта
        $hotelsQuery = Hotel::select('id', 'title')->orderBy('title', 'asc');

        // если админ — показываем все, иначе только свои
        if (!$user->hasRole('Admin')) {
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

        $resources   = [];
        $events      = [];
        $exelyEmpty  = false;
        $tmhotelsRaw = null;

        // ---------- 1) ЛОКАЛЬНЫЕ КОМНАТЫ/ТАРИФЫ ----------
        // (раньше $rooms был не определён — исправлено)
        $rooms = Room::with('rates')->where('hotel_id', $hotelId)->get();

        foreach ($rooms as $room) {
            $roomResId = 'room_' . $room->id;

            // ресурс-комната
            $resources[] = [
                'id'    => $roomResId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $code       = $meals[$rate->meal_id]->code ?? null;
                $resourceId = $roomResId . '_rate_' . $rate->id;

                // ресурс-тариф
                $resources[] = [
                    'id'       => $resourceId,
                    'title'    => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomResId,
                ];

                // брони на период
                $bookings = Book::where('rate_id', $rate->id)
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate',   [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get();

                // суммируем занятость по дням
                $usedByDate = []; // кол-во взрослых/мест, занятых по датам
                foreach ($bookings as $book) {
                    $arrival   = Carbon::parse($book->arrivalDate)->startOfDay();
                    $departure = Carbon::parse($book->departureDate)->startOfDay();

                    foreach ($arrival->daysUntil($departure) as $date) {
                        $d = $date->format('Y-m-d');
                        $usedByDate[$d] = ($usedByDate[$d] ?? 0) + (int)($book->adult ?? 0);
                    }
                }

                // генерим события-клетки доступности
                foreach ($startDate->daysUntil($endDate) as $date) {
                    $d          = $date->format('Y-m-d');
                    $booked     = $usedByDate[$d] ?? 0;
                    $available  = max(0, (int)$rate->availability - $booked);
                    $color      = ($available === 0) ? '#d95d5d' : '#39bb43';

                    $events[] = [
                        'id'               => 'local_' . $rate->id . '_' . $d,
                        'title'            => (string)$available,
                        'start'            => $d,
                        'end'              => $d,
                        'resourceId'       => $resourceId,
                        'backgroundColor'  => $color,
                        'borderColor'      => $color,
                    ];
                }
            }
        }

        // ---------- 2) TOURMIND (если у отеля есть tourmind_id) ----------
//        if ($hotel && !empty($hotel->tourmind_id)) {
//            try {
//                $hotelService = new \App\Services\Tourmind\HotelServices();
//                $tmhotels     = $hotelService->getOneDetailForCalendar($request, $hotel); // ОЖИДАЕТСЯ объект со структурой Hotels[0].RoomTypes[]
//                $tmhotelsRaw  = $tmhotels;
//
//                if (isset($tmhotels->Hotels[0]->RoomTypes) && is_array($tmhotels->Hotels[0]->RoomTypes)) {
//                    foreach ($tmhotels->Hotels[0]->RoomTypes as $tmRoom) {
//
//                        $tmRoomResId = 'tm_room_' . ($tmRoom->RoomTypeCode ?? Str::random(6));
//                        $tmRoomName  = $tmRoom->Name ?? 'TM Room';
//
//                        // ресурс-комната TM
//                        $resources[] = [
//                            'id'    => $tmRoomResId,
//                            'title' => $tmRoomName,
//                        ];
//
//                        if (!empty($tmRoom->RateInfos) && is_array($tmRoom->RateInfos)) {
//                            foreach ($tmRoom->RateInfos as $tmRate) {
//                                $tmRateCode = $tmRate->RateCode ?? Str::random(6);
//                                $tmMealId   = $tmRate->MealInfo->MealType ?? null;
//                                $tmBedDesc  = $tmRate->bedTypeDesc ?? 'TM Rate';
//                                $tmCode     = $meals[$tmMealId]->code ?? null;
//
//                                $tmResId = $tmRoomResId . '_rate_' . $tmRateCode;
//
//                                // ресурс-тариф TM
//                                $resources[] = [
//                                    'id'       => $tmResId,
//                                    'title'    => $tmBedDesc . ' - ' . ($tmCode ? "({$tmCode})" : ''),
//                                    'parentId' => $tmRoomResId,
//                                ];
//
//                                // В TM часто доступность — это Allotment/Availability на период
//                                $tmAvail = null;
//                                if (isset($tmRate->Availability)) $tmAvail = (int)$tmRate->Availability;
//                                elseif (isset($tmRate->Allotment)) $tmAvail = (int)$tmRate->Allotment;
//                                else $tmAvail = 0;
//
//                                foreach ($startDate->daysUntil($endDate) as $date) {
//                                    $d     = $date->format('Y-m-d');
//                                    $color = $tmAvail > 0 ? '#39bb43' : '#d95d5d';
//
//                                    $events[] = [
//                                        'id'               => 'tm_' . $tmRateCode . '_' . $d,
//                                        'title'            => (string)$tmAvail,
//                                        'start'            => $d,
//                                        'end'              => $d,
//                                        'resourceId'       => $tmResId,
//                                        'backgroundColor'  => $color,
//                                        'borderColor'      => $color,
//                                    ];
//                                }
//                            }
//                        }
//                    }
//                }
//            } catch (\Throwable $e) {
//                Log::error('Tourmind API error (calendar): ' . $e->getMessage());
//            }
//        }

        // ---------- 3) EXELY (если у отеля есть exely_id) ----------
//        if ($hotel && $hotel->exely_id) {
//            $params = [
//                'arrivalDate'         => $startDate->format('Y-m-d'),
//                'departureDate'       => $endDate->format('Y-m-d'),
//                'adults'              => 1,
//                'includeExtraStays'   => 'false',
//                'includeExtraServices'=> 'false',
//            ];
//            $url = rtrim(config('services.exely.base_url'), '/') .
//                "/search/v1/properties/{$hotel->exely_id}/room-stays?" . http_build_query($params);
//
//            $response = Http::withHeaders([
//                'x-api-key' => config('services.exely.key'),
//                'accept'    => 'application/json',
//            ])->get($url);
//
//            Log::debug('📤 Exely API call', ['url' => $url, 'status' => $response->status()]);
//
//            if (!$response->successful()) {
//                Log::error('❌ Ошибка Exely API', [
//                    'status' => $response->status(),
//                    'body'   => $response->body(),
//                ]);
//                // не прерываем вывод локальных/TM, просто отметим отсутствие EXELY
//                $exelyEmpty = true;
//            } else {
//                $data = $response->json();
//                if (empty($data['roomStays'])) {
//                    Log::warning('⚠️ Exely вернул пустой roomStays', ['response' => $data]);
//                    $exelyEmpty = true;
//                } else {
//                    // Карта exely_id комнат -> локальные id (для связи ресурсов)
//                    $localRoomsByExely = Room::where('hotel_id', $hotelId)->get()->keyBy('exely_id');
//
//                    foreach ($data['roomStays'] as $stay) {
//                        $roomExelyId  = $stay['roomType']['id'] ?? null;
//                        $ratePlanName = $stay['ratePlan']['name'] ?? 'API Rate';
//                        $availability = (int)($stay['availability'] ?? 0);
//
//                        // Привязываем к локальной комнате (если есть)
//                        $localRoom = $roomExelyId ? $localRoomsByExely->get($roomExelyId) : null;
//
//                        // Если локальная не найдена — всё равно заводим ресурс с exely_
//                        $roomResId = $localRoom
//                            ? ('room_' . $localRoom->id)
//                            : ('exely_room_' . ($roomExelyId ?? Str::random(6)));
//
//                        // добавить ресурс-комнату, если его ещё нет
//                        if (!collect($resources)->contains(fn($r) => $r['id'] === $roomResId)) {
//                            $resources[] = [
//                                'id'    => $roomResId,
//                                'title' => $localRoom?->title ?? ('Exely Room #' . ($roomExelyId ?? '')),
//                            ];
//                        }
//
//                        $rateId    = $stay['ratePlan']['id'] ?? ($stay['checksum'] ?? Str::random(6));
//                        $resourceId= $roomResId . '_rate_exely_' . $rateId;
//
//                        // ресурс-тариф EXELY
//                        $resources[] = [
//                            'id'       => $resourceId,
//                            'title'    => $stay['fullPlacementsName'] ?? $ratePlanName,
//                            'parentId' => $roomResId,
//                        ];
//
//                        // Проставляем одинаковую availability на каждую дату диапазона (если Exely не дал поминутно)
//                        foreach ($startDate->daysUntil($endDate) as $date) {
//                            $d     = $date->format('Y-m-d');
//                            $color = $availability > 0 ? '#39bb43' : '#d95d5d';
//
//                            $events[] = [
//                                'id'               => $resourceId . '_' . $d,
//                                'title'            => (string)$availability,
//                                'start'            => $d,
//                                'end'              => $d,
//                                'resourceId'       => $resourceId,
//                                'backgroundColor'  => $color,
//                                'borderColor'      => $color,
//                            ];
//                        }
//                    }
//                }
//            }
//        }

        Log::debug('Final resources and events', [
            'resources_count' => count($resources),
            'events_count'    => count($events),
        ]);

        $warning = count($events) === 0 ? 'Нет доступных предложений на выбранные даты.' : null;

        return view('auth.books.index', [
            'resources'  => $resources,
            'hotelslist' => $hotelslist,
            'events'     => $events,
            'request'    => $request,
            'exelyEmpty' => $exelyEmpty,
            'warning'    => $warning,
            'hotel'      => $hotelId,
            'tmhotels'   => $tmhotelsRaw,
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

        $hotelId = $request->hotel_id;
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->copy()->addDays(60)->endOfDay();

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
                    'title' => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomId,
                ];

                foreach ($startDate->daysUntil($endDate) as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $usedAdults = $adultByDate[$dateStr] ?? 0;
                    $available = max(0, $rate->availability - $usedAdults);

                    $color = ($available == 0) ? '#d95d5d' : '#39bb43';

                    $events[] = [
                        'id' => 'local_' . $rate->id . '_' . $dateStr,
                        'title' => (string)$available,
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
//        $rooms = $roomQuery->get()->keyBy('exely_id');
//        if ($hotel && $hotel->exely_id) {
//            $params = [
//                'arrivalDate' => $startDate->format('Y-m-d'),
//                'departureDate' => $endDate->copy()->addDay()->format('Y-m-d'),
//                'adults' => 1,
//                'includeExtraStays' => 'false',
//                'includeExtraServices' => 'false',
//            ];
//
//            $url = rtrim(config('services.exely.base_url'), '/') . "/search/v1/properties/{$hotel->exely_id}/room-stays?" . http_build_query($params);
//            $response = Http::withHeaders([
//                'x-api-key' => config('services.exely.key'),
//                'accept' => 'application/json',
//            ])->get($url);
//
//            Log::debug('📤 Exely API call', ['url' => $url, 'status' => $response->status()]);
//
//            if (!$response->successful()) {
//                Log::error('❌ Ошибка Exely API', [
//                    'status' => $response->status(),
//                    'body' => $response->body(),
//                ]);
//                return response()->json([
//                    'resources' => [],
//                    'events' => [],
//                    'warning' => 'Exely API вернул ошибку при получении данных.'
//                ]);
//            }
//
//            $data = $response->json();
//
//            if (empty($data['roomStays'])) {
//                Log::warning('⚠️ Exely вернул пустой roomStays', ['response' => $data]);
//
//                return response()->json([
//                    'resources' => [],
//                    'events' => [],
//                    'warning' => 'Exely не вернул доступных тарифов на выбранные даты.'
//                ]);
//            }
//
//            foreach ($data['roomStays'] ?? [] as $stay) {
//                $roomExelyId = $stay['roomType']['id'] ?? null;
//                $room = $rooms->get($roomExelyId);
//                if (!$room || !isset($stay['availability'])) continue;
//
//                $roomId = 'room_' . $room->id;
//                $rateId = $stay['ratePlan']['id'] ?? $stay['checksum'] ?? Str::uuid();
//                $resourceId = $roomId . '_rate_' . $rateId;
//                $rateName = $stay['fullPlacementsName'] ?? $stay['ratePlan']['name'] ?? 'Rate';
//
//                if (!collect($resources)->contains('id', $roomId)) {
//                    $resources[] = ['id' => $roomId, 'title' => $room->title];
//                }
//
//                $resources[] = [
//                    'id' => $resourceId,
//                    'title' => $rateName,
//                    'parentId' => $roomId,
//                ];
//
//                $availability = $stay['availability'];
//
//                foreach ($startDate->daysUntil($endDate->copy()->addDay()) as $date) {
//                    $dateStr = $date->format('Y-m-d');
//                    $color = $availability > 0 ? '#39bb43' : '#d95d5d';
//
//                    $events[] = [
//                        'id' => $resourceId . '_' . $dateStr,
//                        'title' => (string)$availability,
//                        'start' => $dateStr,
//                        'end' => $dateStr,
//                        'resourceId' => $resourceId,
//                        'backgroundColor' => $color,
//                        'borderColor' => $color,
//                    ];
//                }
//            }
//        }

        Log::debug('Final resources and events', [
            'events_count' => count($events)
        ]);

        $warning = count($events) === 0 ? 'Нет доступных предложений на выбранные даты.' : null;

        // Если есть предупреждение (Exely warning), оно уже возвращено выше. Здесь только обычный ответ.
        return response()->json([
            'resources' => $resources,
            'events' => $events,
            'tmhotels' => $hotelId,
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
            if ((int)$rate->room_id !== (int)$roomId) {
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
        } // Обработка ошибок валидации (Laravel automatically throws ValidationException)
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => implode('<br>', $e->validator->errors()->all())
            ]);
        } // Общая защита
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