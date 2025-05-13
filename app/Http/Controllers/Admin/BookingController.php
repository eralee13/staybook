<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Meal;
use App\Models\Hotel;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public $checkin;
    public $checkout;
    public $adults;
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

    public function __construct() {

        $this->middleware('auth');
        $this->middleware('permission:create-book|edit-book|delete-book', ['only' => ['index','show']]);
        $this->middleware('permission:create-book', ['only' => ['create','store']]);
        $this->middleware('permission:edit-book', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-book', ['only' => ['destroy']]);

        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

    }

    // Страница календаря
    public function index(Request $request)
    {
        if( !Auth::check() ){
            return redirect()->route('index');
        }
        if( $request->get('hotel_id') ){
            $hotelId = $request->get('hotel_id');
        }else{
            $hotelId = 908;
        }


        $hotelslist = Hotel::select('id', 'title')
            ->orderBy('title', 'asc')
            ->get();

        $meals = Meal::all()->keyBy('id');

        $startDate = Carbon::now()->startOfMonth()->startOfDay();
        $endDate = Carbon::now()->endOfMonth()->endOfDay();

        // $startDate = Carbon::now()->addMonthNoOverflow()->startOfMonth()->startOfDay();
        // $endDate = Carbon::now()->addMonthNoOverflow()->endOfMonth()->endOfDay();

        // Все брони в выбранный диапазон
        $books = Book::with('room.rates')
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotelId))
            ->whereBetween('arrivalDate', [$startDate, $endDate])
            ->get();

        // Все номера и тарифы отеля
        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        // Собираем ресурсы
        $resources = [];
        foreach ($rooms as $room) {
            // Пропускаем комнаты без тарифов или с null-тарифами
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) {
                continue;
            }

            $parentId = 'room_' . $room->id;

            $resources[] = [
                'id' => $parentId,
                'title' => $room->title_en,
            ];

            foreach ($validRates as $rate) {
                $mealTitle = is_numeric($rate->meal_id) && isset($meals[$rate->meal_id])
                    ? $meals[$rate->meal_id]->title
                    : $rate->meal_id;

                $resources[] = [
                    'id' => $parentId . '_rate_' . $rate->id,
                    'title' => $rate->desc_en . ' - ' . $mealTitle,
                    'parentId' => $parentId,
                ];
            }
        }

        // Индексируем брони по тарифу и дате
        $bookingsMap = [];
        foreach ($books as $book) {
            $room = $book->room;
            $validRates = $room->rates->filter();
            foreach ($validRates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;
                $period = Carbon::parse($book->arrivalDate)->daysUntil(Carbon::parse($book->departureDate));

                foreach ($period as $date) {
                    $bookingsMap[$resourceId][$date->format('Y-m-d')] = [
                        'id' => $book->id,
                        'status' => $book->status,
                        'price' => $book->price,
                        'currency' => $book->currency,
                        'phone' => $book->phone,
                        'email' => $book->email,
                        'allotment' => $rate->allotment,
                    ];
                }
            }
        }

        // Создаём события
        $events = [];
        foreach ($rooms as $room) {
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) {
                continue;
            }

            foreach ($validRates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;
                $period = Carbon::parse($startDate)->daysUntil($endDate);

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');

                    // Завтра
                    $tomorrow = now()->addDay()->startOfDay();

                    if (isset($bookingsMap[$resourceId][$dateStr])) {
                        // 🔴 Бронь — отображаем всегда
                        $booking = $bookingsMap[$resourceId][$dateStr];

                        $color = match ($booking['status']) {
                            'CANCELLED' => '#e19d22',
                            'booked', 'Pending' => '#d95d5d',
                            default => '#39bb43',
                        };

                        $events[] = [
                            'id' => $booking['id'] . '_' . $dateStr,
                            'title' => $booking['allotment'] ?? '—',
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'color' => $color,
                            'extendedProps' => [
                                'status' => $color,
                                'rate_id' => $rate->id,
                                'description' => "{$booking['price']} {$booking['currency']}<br>{$booking['phone']}<br>{$booking['email']}"
                            ]
                        ];
                    } else {
                        // 🟢 Свободные квоты только с завтрашнего дня
                        if ($date->gte($tomorrow)) {
                            $events[] = [
                                'id' => 'free_' . $rate->id . '_' . $dateStr,
                                'title' => $rate->allotment ?? '—',
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
                        // Иначе пропускаем (не показываем ничего)
                    }
                }


            }

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
        if( !Auth::check() ){
            return redirect()->route('index');
        }

        $meals = Meal::all()->keyBy('id');

        // $startDate = request('start') ? Carbon::parse(request('start'))->startOfDay() : now()->startOfMonth()->startOfDay();
        // $endDate = request('end') ? Carbon::parse(request('end'))->endOfDay() : now()->endOfMonth()->endOfDay();
        $hotelId = $request->get('hotel_id') ?? 908;
        $startDate = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : now()->endOfMonth();


        // Все брони в выбранный диапазон
        $books = Book::with('room.rates')
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotelId))
            ->whereBetween('arrivalDate', [$startDate, $endDate])
            ->get();

        // Все номера и тарифы отеля
        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        // Собираем ресурсы
        $resources = [];
        foreach ($rooms as $room) {
            // Пропускаем комнаты без тарифов или с null-тарифами
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) {
                continue;
            }

            $parentId = 'room_' . $room->id;

            $resources[] = [
                'id' => $parentId,
                'title' => $room->title_en,
            ];

            foreach ($validRates as $rate) {
                $mealTitle = is_numeric($rate->meal_id) && isset($meals[$rate->meal_id])
                    ? $meals[$rate->meal_id]->title
                    : $rate->meal_id;

                $resources[] = [
                    'id' => $parentId . '_rate_' . $rate->id,
                    'title' => $rate->desc_en . ' - ' . $mealTitle,
                    'parentId' => $parentId,
                ];
            }
        }

        // Индексируем брони по тарифу и дате
        $bookingsMap = [];
        foreach ($books as $book) {
            $room = $book->room;
            $validRates = $room->rates->filter();
            foreach ($validRates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;
                $period = Carbon::parse($book->arrivalDate)->daysUntil(Carbon::parse($book->departureDate));

                foreach ($period as $date) {
                    $bookingsMap[$resourceId][$date->format('Y-m-d')] = [
                        'id' => $book->id,
                        'status' => $book->status,
                        'price' => $book->price,
                        'currency' => $book->currency,
                        'phone' => $book->phone,
                        'email' => $book->email,
                        'allotment' => $rate->allotment,
                    ];
                }
            }
        }

        // Создаём события
        $events = [];
        foreach ($rooms as $room) {
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) {
                continue;
            }

            foreach ($validRates as $rate) {
                $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;
                $period = Carbon::parse($startDate)->daysUntil($endDate);

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');

                    if (isset($bookingsMap[$resourceId][$dateStr])) {
                        $booking = $bookingsMap[$resourceId][$dateStr];

                        // Цвета статуса - как ты прислал:
                        $color = match ($booking['status']) {
                            'CANCELLED' => '#e19d22',
                            'booked'    => '#d95d5d',
                            'Pending' => '#d95d5d',
                            default     => '#39bb43',
                        };

                        $events[] = [
                            'id' => $booking['id'] . '_' . $dateStr,
                            'title' => $booking['allotment'] ?? '—',
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'color' => $color,
                            'extendedProps' => [
                                'description' => "{$booking['price']} {$booking['currency']}<br>{$booking['phone']}<br>{$booking['email']}"
                            ]
                        ];
                    } else {
                        $events[] = [
                            'id' => 'free_' . $rate->id . '_' . $dateStr,
                            'title' => $rate->allotment ?? '—',
                            'start' => $dateStr,
                            'end' => $dateStr,
                            'resourceId' => $resourceId,
                            'color' => '#39bb43', // Свободная квота
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
            'resources' => $resources
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

        $start = Carbon::parse($request->input('start'))->format('Y-m-d');
        $end = Carbon::parse($request->input('end'))->format('Y-m-d');
        $rateId = $request->input('rate_id');
        $roomId = $request->input('room_id');
        $hotelId = $request->input('hotel_id');
        $allotment = $request->input('allotment');

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

            // if ( $start == $end ) {
            //     $endDate = Carbon::parse($start)->addDay();
            // } else {
            //     $endDate = $end;
            // }

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
                    'adult' => 0,
                    'child' => 0,
                    'childages' => '',
                    'price' => 0,
                    'sum' => 0,
                    'currency' => '',
                    'arrivalDate' => $start,
                    'departureDate' => $end,
                    'status' => 'Pending',
                    'user_id' => Auth::id(),
                    'api_type' => 'calendar',
                ]
            );

            $rate = Rate::find($rateId);

            if ($rate->allotment < $allotment) {
                return response()->json(['error' => false, 'message' => 'Недостаточно квоты']);
            }
            elseif ( $book->id ){

                // Обновляем квоту
                $rate->allotment -= $allotment;
                $rate->save();

                return response()->json(['success' => true, 'message' => 'Бронь создан']);
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
                    'allotment' => $rate->allotment,
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
                "Adults" => (int)$this->adults,
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
