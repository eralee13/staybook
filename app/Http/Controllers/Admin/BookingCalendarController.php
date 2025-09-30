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

        $user = Auth::user();

        // Список отелей для селекта
        $hotelsQuery = Hotel::select('id', 'title')
            ->where('apiName', 'local')
            ->orderBy('title', 'asc');

        if (!$user->hasRole('Super Admin')) {
            $hotelsQuery->where('user_id', $user->id);
        }
        $hotelslist = $hotelsQuery->get();

        $requestedId = (int) $request->hotel;
        $hotelId = $requestedId && $hotelslist->contains('id', $requestedId)
            ? $requestedId
            : optional($hotelslist->first())->id;

        $startDate = Carbon::now()->startOfDay();
        $endDate   = Carbon::now()->copy()->addDays(60)->endOfDay();

        $meals      = Meal::all()->keyBy('id');
        $resources  = [];
        $events     = [];

        // Локальные комнаты/тарифы по выбранному отелю
        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        foreach ($rooms as $room) {
            $roomResId = 'room_' . $room->id;

            $resources[] = [
                'id'    => $roomResId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $code       = $meals[$rate->meal_id]->code ?? null;
                $resourceId = $roomResId . '_rate_' . $rate->id;

                $resources[] = [
                    'id'       => $resourceId,
                    'title'    => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomResId,
                ];

                // Квоты из календаря: api_type = calendar_allotment, поле adult
                $quotaByDate = [];
                $quotaRows = Book::where('rate_id', $rate->id)
                    ->where('api_type', 'calendar_allotment')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get();

                foreach ($quotaRows as $qrow) {
                    $arrival   = Carbon::parse($qrow->arrivalDate)->startOfDay();
                    $departure = Carbon::parse($qrow->departureDate)->startOfDay();

                    foreach ($arrival->daysUntil($departure) as $date) {
                        $d = $date->format('Y-m-d');
                        // если несколько записей на день — последняя перекроет предыдущую (как у вас и было)
                        $quotaByDate[$d] = (int) ($qrow->adult ?? 0);
                    }
                }

                // События по дням: отображаем adult или fallback на availability тарифа
                foreach ($startDate->daysUntil($endDate) as $date) {
                    $d     = $date->format('Y-m-d');
                    $adult = $quotaByDate[$d] ?? (int) $rate->availability;

                    $color = ($adult > 0) ? '#39bb43' : '#d95d5d';

                    $events[] = [
                        'id'              => 'local_' . $rate->id . '_' . $d,
                        'title'           => (string) $adult, // показываем именно adult (квоту)
                        'start'           => $d,
                        'end'             => $d,              // можно сделать эксклюзивный end = +1 день, если нужно
                        'resourceId'      => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                    ];
                }
            }
        }

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
            'warning'    => $warning,
            'hotel'      => $hotelId,
            'tmhotels'   => null, // в этой версии не используем
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

        $hotelId   = $request->hotel_id;
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

        foreach ($rooms as $room) {
            $roomId = 'room_' . $room->id;

            $resources[] = [
                'id'    => $roomId,
                'title' => $room->title
            ];

            foreach ($room->rates as $rate) {
                $code       = $meals[$rate->meal_id]->code ?? null;
                $resourceId = $roomId . '_rate_' . $rate->id;

                $resources[] = [
                    'id'       => $resourceId,
                    'title'    => $rate->title . ' - ' . ($code ? "({$code})" : ''),
                    'parentId' => $roomId,
                ];

                // Собираем adult из календаря квот
                $quotaByDate = [];
                $quotaRows = Book::where('rate_id', $rate->id)
                    ->where('api_type', 'calendar_allotment')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('arrivalDate', [$startDate, $endDate])
                            ->orWhereBetween('departureDate', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('arrivalDate', '<=', $startDate)
                                    ->where('departureDate', '>=', $endDate);
                            });
                    })
                    ->get();

                foreach ($quotaRows as $qrow) {
                    $arrival   = Carbon::parse($qrow->arrivalDate)->startOfDay();
                    $departure = Carbon::parse($qrow->departureDate)->startOfDay();
                    foreach ($arrival->daysUntil($departure) as $date) {
                        $d = $date->format('Y-m-d');
                        $quotaByDate[$d] = (int) ($qrow->adult ?? 0);
                    }
                }

                // Рисуем календарь: показываем adult (или availability, если нет записи)
                foreach ($startDate->daysUntil($endDate) as $date) {
                    $d     = $date->format('Y-m-d');
                    $adult = $quotaByDate[$d] ?? (int) $rate->availability;

                    $color = ($adult > 0) ? '#39bb43' : '#d95d5d';

                    $events[] = [
                        'id'              => 'local_' . $rate->id . '_' . $d,
                        'title'           => (string) $adult,
                        'start'           => $d,
                        'end'             => $d,
                        'resourceId'      => $resourceId,
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                    ];
                }
            }
        }

        Log::debug('Final resources and events', [
            'events_count' => count($events)
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
            // Валидация
            $validated = $request->validate([
                'start'    => 'required|date',
                'end'      => 'required|date|after_or_equal:start',
                'rate_id'  => 'required|exists:rates,id',
                'room_id'  => 'required|exists:rooms,id',
                'hotel_id' => 'required|exists:hotels,id',
                'allotment'=> 'required|integer|min:0', // сюда придёт ваша квота, пойдёт в adult
            ]);

            $start     = Carbon::parse($validated['start'])->format('Y-m-d');
            $end       = Carbon::parse($validated['end'])->format('Y-m-d');
            $rateId    = $validated['rate_id'];
            $roomId    = $validated['room_id'];
            $hotelId   = $validated['hotel_id'];
            $allotment = (int) $validated['allotment'];

            // Проверки на соответствие тарифа номеру и окна бронирования
            $rate = Rate::find($rateId);
            if ((int) $rate->room_id !== (int) $roomId) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Несоответствие тарифа и номера.'
                ]);
            }

            $now         = now()->setTimezone('Asia/Bishkek');
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

            // Генерация токена
            do {
                $token = Str::random(40);
            } while (Book::where('book_token', $token)->exists());

            // Создание записи-квоты: adult = ваша квота, цена не трогаем
            Book::create([
                'book_token'    => $token,
                'title'         => '',
                'hotel_id'      => $hotelId,
                'room_id'       => $roomId,
                'rate_id'       => $rateId,
                'phone'         => '',
                'email'         => '',
                'comment'       => '',
                'adult'         => $allotment, // квота
                'child'         => null,
                'price'         => null,       // не трогаем цену
                'sum'           => 0,
                'currency'      => null,
                'arrivalDate'   => $start,
                'departureDate' => $end,
                'status'        => 'Pending',
                'user_id'       => Auth::id(),
                'api_type'      => 'calendar_allotment',
            ]);

            return response()->json(['success' => true, 'message' => 'Квота сохранена.']);
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
