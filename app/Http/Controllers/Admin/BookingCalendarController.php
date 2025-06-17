<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Hotel;
use App\Models\Room;

class BookingCalendarController extends Controller
{
    public $checkin;
    public $checkout;
    public $adult;
    public $child;
    public $childsage;
    public $roomCount;
    public $citizen;
    public $rooms;
    public $rates;
    public $rules;
    public $hotels;
    public $hotelslist;
    public $token;

    public function __construct(){

        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

    }

    // Страница календаря
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
                            'CANCELLED' => '#e19d22',
                            'booked', 'Pending' => '#d95d5d',
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
            'events' => $events,
            'hotelslist' => $hotelslist,
        ]);
    }


    // Получение событий для FullCalendar (JSON)
    public function getEvents(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $hotelId = $request->get('hotel_id') ?? 14;

        // Получаем даты
        $start = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : now()->startOfMonth();
        $end = $request->input('end') ? Carbon::parse($request->input('end'))->startOfDay() : now()->endOfMonth();

        // ⛔ Защита: если диапазон более 62 дней — игнорируем
        if ($end->diffInDays($start) > 62) {
            return response()->json([
                'events' => [],
                'resources' => [],
                'message' => 'Диапазон слишком большой'
            ]);
        }

        // ⛔ Защита: если перепутаны даты
        if ($end->lessThanOrEqualTo($start)) {
            return response()->json([
                'events' => [],
                'resources' => [],
                'message' => 'Неверный диапазон дат'
            ]);
        }

        // Получаем данные
        $books = Book::with('room.rates')
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotelId))
            ->whereBetween('arrivalDate', [$start, $end])
            ->get();

        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        // 🔧 resources
        $resources = [];
        foreach ($rooms as $room) {
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) continue;

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

        // 🔧 bookings
        $bookingsMap = [];
        foreach ($books as $book) {
            $room = $book->room;
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
                        'adult' => $book->adult,
                    ];
                }
            }
        }

        // 🔧 events
        $events = [];
        $period = $start->daysUntil($end);
        $tomorrow = now()->addDay()->startOfDay();

        foreach ($rooms as $room) {
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) continue;

            foreach ($validRates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');

                    if (isset($bookingsMap[$resourceId][$dateStr])) {
                        $booking = $bookingsMap[$resourceId][$dateStr];

                        $color = match ($booking['status']) {
                            'CANCELLED' => '#e19d22',
                            'booked', 'Pending' => '#d95d5d',
                            default => '#39bb43',
                        };

                        $events[] = [
                            'id' => $booking['id'] . '_' . $dateStr,
                            'title' => $booking['adult'] ?? '—',
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'color' => $color,
                            'extendedProps' => [
                                'description' => "{$booking['price']} {$booking['currency']}<br>{$booking['phone']}<br>{$booking['email']}",
                                'price' => $booking['price'],
                                'currency' => $booking['currency'],
                            ]
                        ];
                    } elseif ($rate->availability > 0) {
                        $events[] = [
                            'id' => 'free_' . $rate->id . '_' . $dateStr,
                            'title' => (string) $rate->availability,
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

        return response()->json([
            'events' => $events,
            'resources' => $resources,
        ]);
    }



    // Создание брони через календарь
    public function store(Request $request)
    {
        // $validated = $request->validate([
        //     'title' => 'required|string|max:255',
        //     'start' => 'required|date',
        //     'end' => 'nullable|date',
        //     'quota' => 'nullable|integer',
        // ]);

        //dd($request->all());
        $rateId = $request->input('rate_id');
        $roomId = $request->input('room_id');
        $hotelId = $request->input('hotel_id');
        $allotment = (int) $request->input('allotment');

        if ( empty($rateId) ) {
            return response()->json(['error' => true, 'message' => 'Тариф не найден']);
        }

        if ( empty($roomId) ) {
            return response()->json(['error' => true, 'message' => 'Номер не найден']);
        }

        try {
            do {
                $this->token = Str::random(40);
            } while (Book::where('book_token', $this->token)->exists());

            $book = Book::firstOrCreate(
                [
                    'book_token' => $this->token,
                ],
                [
                    'title' => '',
                    'title2' => '',
                    'hotel_id' => $hotelId,
                    'room_id' => $roomId,
                    'phone' => '',
                    'email' => '',
                    'comment' => '',
                    'adult' => $allotment,
                    'child' => null,
                    'price' => null,
                    'sum' => 0,
                    'currency' => '',
                    'arrivalDate' => $request->arrivalDate,
                    'departureDate' => $request->departureDate,
                    'status' => 'Pending',
                    'user_id' => Auth::id(),
                    'api_type' => 'calendar',
                ]
            );

            $rate = Rate::find($rateId);

            if ($rate->availability < $allotment) {
                return response()->json(['error' => false, 'message' => 'Недостаточно квоты']);
            }
            elseif ( $book->id ){

                // Обновляем квоту
                $rate->availability -= $allotment;
                $rate->save();

                $chotel = Hotel::all();
                $hotels = Hotel::paginate(20);

                return view('auth.hotels.index', compact('chotel', 'hotels'));
            }else{
                return response()->json(['error' => true, 'message' => 'Ошибка создания брони! Попробуйте позже!']);
            }

        } catch (\Throwable $th) {
            return response()->json(['error' => true, 'message' => $th->getMessage()]);
        }

    }

    // Обновление брони (перетаскивание в календаре)
    public function update(Request $request, $id)
    {
        $booking = Book::findOrFail($id);
        $booking->update($request->only(['start', 'end']));
        return response()->json(['message' => 'Event updated']);
    }

    // Удаление брони
    public function destroy($id)
    {
        Book::destroy($id);
        return response()->json(['message' => 'Event deleted']);
    }

    public function showCalendar()
    {
        $rooms = Room::select('id', 'title as title')->get()->map(function ($room) {
            return [
                'id' => $room->id,
                'title' => $room->title,
            ];
        });

        $rates = Rate::with('rule')
            ->get()
            ->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'resourceId' => $rate->room_id,
                    'start' => optional($rate->rule)->start_date_time ?? now()->startOfWeek(),
                    'end' => optional($rate->rule)->end_date_time ?? now()->endOfWeek(),
                    'title' => $rate->title,
                    'price' => $rate->price,
                    'currency' => $rate->currency ?? 'USD',
                    'allotment' => $rate->availability,
                ];
            });

        return view('admin.calendar', compact('rooms', 'rates'));
    }

    public function getHotelDetail(){

        // RequestHeader (заголовки запроса)
        $requestHeader = [
            "AgentCode" => $this->tm_agent_code,
            "Password" => $this->tm_password,
            "UserName" => $this->tm_user_name,
            "RequestTime" => now()->format('Y-m-d H:i:s')
        ];

        // Основные параметры запроса (без заголовков и PaxRooms)
        $mainParams = [
            "CheckIn" => $this->checkin,
            "CheckOut" => $this->checkout,
            "HotelCodes" => $this->hotels,
            "IsDailyPrice" => false,
            "Nationality" => $this->citizen ?? "EN",
        ];

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
            [
                "Adults" => (int)$this->adult,
                "RoomCount" => (int)$this->roomCount,
            ]
        ];


        if ( !empty($this->child) && !empty($this->childrenage ) ) {
            $paxRooms[0]["Children"] = (int) $this->child;
            $paxRooms[0]["ChildrenAges"] = $this->childsage;
        }


        // Объединение всех частей в один массив
        $payload = array_merge($mainParams, [
            "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
            "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/HotelDetail", $payload);

            if ($response->failed()) {
                $this->bookingSuccess = 'Result HotelDetail Ошибка при запросе к API';
            }

            if ( isset($response['Error']['ErrorMessage']) ){
                $this->bookingSuccess = $response['Error']['ErrorMessage'];
            }
            // dd($payload);
            // dd($response);

            // $this->bookingSuccess .= print_r($payload, 1);
            return $response->json();
            // return $payload;

        } catch (\Throwable $th) {
            $this->bookingSuccess = "Ошибка при запросе к API или недоступен Hotel result hotelDetail";
        }
    }
}