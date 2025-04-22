<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Rule;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Meal;


class BookCalendarController extends Controller
{   
    public $checkin;
    public $checkout;
    public $adults;
    public $child;
    public $childsage;
    public $roomCount;
    public $citizen;
    public $bookingSuccess;
    public $rooms;
    public $rates;
    public $rules;
    public $hotels;

    public function __construct(){

        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

    }

    // Страница календаря
    public function index()
    {
        if( !Auth::check() ){
            return redirect()->route('index');
        }

        // Подгружаем связи: у Book есть room, у room — rates

        $events = Book::with('room.rates')->get()->map(function ($book) {
            $room = $book->room;
            $rate = $room?->rates?->first(); // Можно выбрать нужный rate логикой или хранить в Book
        
            $resourceId = $rate
                ? 'room_' . $room->id . '_rate_' . $rate->id
                : 'room_' . $room->id . '_rate_none'; // fallback
            
            if ($book->status == 'CANCELLED') {
                $color = '#2563eb';
            }else if ($book->status == 'booked') {
                $color = '#d95d5d';
            }else{
                $color = '#39bb43';
            }

            return [
                'id' => $book->id,
                'title' => $rate->allotment ?? '',
                'extendedProps' => [
                    'price' => $book->price,
                    'currency' => $book->currency,
                    'description' => $book->price . ' ' . $book->currency . '<br>' . $book->phone . '<br>' . $book->email,
                ],
                'start' => Carbon::parse($book->arrivalDate)->format('Y-m-d'),
                'end' => Carbon::parse($book->departureDate)->format('Y-m-d 23:59:59'),
                'resourceId' => $resourceId,
                'color' => $color, 
            ];
        })->toArray();
        

        $resources = [];

        $rooms = Book::with('room.rates')->get()
            ->pluck('room')
            ->filter()
            ->unique('id');

        $meals = Meal::all()->keyBy('id'); // Создаем коллекцию, ключом которой будет meal_id

        foreach ($rooms as $room) {
            foreach ($room->rates as $rate) {

                if ( is_numeric($rate->meal_id) ) {
                    $mealTitle = $meals[$rate->meal_id]->title;
                }else {
                    $mealTitle = $rate->meal_id;
                }
                
                $resources[] = [
                    'id' => 'room_' . $room->id . '_rate_' . $rate->id,
                    'title' => "<b>".$room->title_en . "</b>\n&nbsp;&nbsp;&nbsp;" . $rate->desc_en ." - ".$mealTitle,
                ];
            }
        }




        return view('bookcalendar.index', [
            'resources' => $resources,
            'events' => $events,
        ]);
    }

    // Получение событий для FullCalendar (JSON)
    public function getEvents(Request $request)
    {   
        $date = Carbon::parse($request->get('date'));

        // заполняем данные из сессии
        if( isset($date) ){
            [$this->checkin, $this->checkout] = explode(' - ', $date);
        }

        // return Book::all()->map(function ($book) {
        //     return [
        //         'id' => $book->id,
        //         'title' => $book->title.' '.$book->title2,
        //         'start' => Carbon::parse($book->arrivalDate)->format('Y-m-d\TH:i:s'),
        //         'end' => Carbon::parse($book->departureDate)->format('Y-m-d\TH:i:s'),
        //     ];
        // });
        // $events = Book::select('id', 'title', 'arrivalDate', 'departureDate', 'status')->get();
        // return response()->json($events);

        return response()->json([
            'events' => $events,
            'resources' => $resources
        ]);
    }

    // Создание брони через календарь
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date',
            'quota' => 'nullable|integer',
        ]);

        $booking = Book::create($validated);
        return response()->json($booking);
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