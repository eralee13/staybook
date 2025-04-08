<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Meal;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Rule;

class BookingForm extends Component
{
    public $hotel;
    public $room;
    public $hotelid;
    public $tmid;
    public $roomid;
    public $book;
    public $order;
    public $city;
    public $adults;
    public $child;
    public $checkin;
    public $checkout;
    public $childdrenage;
    public $childdrenage2;
    public $childdrenage3;
    public $childsage;
    public $citizen;
    public $rating;
    public $food;
    public $early_in;
    public $early_out;
    public $cancelled;
    public $extra_place;
    public $pricemin;
    public $pricemax;
    public $nationality;
    public $roomCount = 1;
    public $hotelName;
    public $hotelimg;
    public $hoteldesc;
    public $hoteladdress;
    public $hotelcity;
    public $hotellat;
    public $hotellng;
    public $roomName;
    public $bedDesc;
    public $allotment;
    public $bookingSuccess = null;
    public $hotelLocal;
    public $cancelDate;
    public $RoomTypeCode;
    public $ratecode;
    public $currency;
    public $totalPrice;
    public $totalSum;
    public $specdesc;
    public $meal;
    public $mealid;
    public $mealall;
    public $checkRoomPrice;
    public $token;
    public $paxfname = 'Don';
    public $paxlname = 'Joe';
    public $email = 'jdon@gmail.com';
    public $phone = '+996700555222';

    public $firstname; 
    public $lastname;
    public $user;
    public $nds;
    public $orderCreated = false;
    // rule
    public $start_date_time, $end_date_time;
    public $refundable;
    public $cancelPolicy;

    protected $rules = [
        'paxfname' => 'required|string|min:3',
        'paxlname' => 'required|string|min:3',
        'lastname' => 'required|string|min:3',
        'lastname' => 'required|string|min:3',
        'email' => 'required|email',
        // 'phone' => ['required', 'string', 'regex:/^\+?[0-9\- ]{10,20}$/'],
    ];

    public function mount()
    {   
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

        $this->hotelid = (int)$_GET['hotelId'];
        $this->tmid = (int)$_GET['tmid'];
        $this->hotelName = $_GET['hotelName'];
        $this->roomName = $_GET['roomName'];
        $this->bedDesc = $_GET['bedDesc'];
        $this->allotment = $_GET['allotment'];
        $this->refundable = $_GET['Refundable'];
        $this->cancelPolicy = $_GET['cancelPolicy'];
        $this->cancelDate = $_GET['cancelDate'];
        $this->ratecode = $_GET['ratecode'];
        $this->totalPrice = $_GET['totalPrice'];
        $this->currency = $_GET['currency'];
        $this->token = $_GET['token'];
        $this->nds = $this->totalPrice * 12 / 100;
        
        $this->loadBook();
        
    }

    public function loadBook(){

        if( !Auth::check() ){
            return redirect()->route('index');
        }

        // Получаем данные из сессии
        $this->filters = session()->get('hotel_search', []);
        
        if( !isset($this->filters['dateRange']) ){
            return redirect()->route('index');
        }

        if( $this->filters['dateRange'] ){
            [$this->checkin, $this->checkout] = explode(' - ', $this->filters['dateRange']);
            $this->adults = $this->filters['adults'];
            $this->child = $this->filters['child'];
            $this->citizen = $this->filters['citizen'];
            $this->city = $this->filters['city'];
            $this->roomCount = $this->filters['roomCount'];

            $this->childrenage = $this->filters['childrenage'];
            $this->childrenage2 = $this->filters['childrenage2'];
            $this->childrenage3 = $this->filters['childrenage3'];

            if ( $this->filters['child'] == 1 ){
                $this->childsage = [(int)$this->childrenage];
            }
            if ( $this->filters['child'] == 2 ){
                $this->childsage = [(int)$this->childrenage, (int)$this->childrenage2];
            }
            if ( $this->filters['child'] == 3 ){
                $this->childsage = [(int)$this->childrenage, (int)$this->childrenage2, (int)$this->childrenage3];
            }
        }
        
        try {

            $this->hotelLocal = Hotel::where('tourmind_id', $this->tmid)
            ->limit(1)
            ->with(['amenity','rooms'])
            ->get()
            ->mapWithKeys(fn($hotel) => [$hotel->tourmind_id => $hotel])
            ->toArray();
            // dd($this->hotelLocal);

            $this->hotelimg = $this->hotelLocal[$this->tmid]['image'] ?? '';
            $this->hoteldesc = $this->hotelLocal[$this->tmid]['description_en'] ?? '';
            $this->hoteladdress = $this->hotelLocal[$this->tmid]['address_en'] ?? '';
            $this->hotelcity = $this->hotelLocal[$this->tmid]['city'] ?? '';
            $this->hotellat = $this->hotelLocal[$this->tmid]['lat'] ?? '';
            $this->hotellng = $this->hotelLocal[$this->tmid]['lng'] ?? '';

            $this->user = auth()->user();
            $this->firstname = $this->user->name;
            $this->lastname = $this->user->lastname;

            $this->checkRoomRate();

        } catch (\Throwable $th) {
            $this->bookingSuccess = "Ошибка получения данных - Book";
        }
    }

    public function confirmBooking()
    {   
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

        $validatedData = $this->validate();

        if ($this->token) {
            $response = $this->createOrder();
            $this->bookingSuccess = $response;
            //session()->forget('booking'); // Очистка сессии
        } else {
            $this->bookingSuccess = "Ошибка при бронировании!";
        }
    }

    public function oneHotelDetail(){

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
            "HotelCodes" => [$this->tmid],
            "IsDailyPrice" => false,
            "Nationality" => $this->citizen ?? "EN",
        ];

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
                [
                    "Adults" => $this->filters['adults'],
                    "RoomCount" => $this->roomCount
                ]
            ];

            if ( !empty($this->filters['child']) && !empty($this->filters['childrenage'] ) ) {
                $paxRooms[0]["Children"] = (int) $this->filters['child'];
                $paxRooms[0]["ChildrenAges"] = $this->childsage;
            }
            

        // Объединение всех частей в один массив
        $payload = array_merge($mainParams, [
            "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
            "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
        ]);

        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/HotelDetail", $payload);

        // if ($response->failed()) {
        //     return ['Error' => ["ErrorMessage" => 'HotelDetail Ошибка при запросе к API']];
        // }

        return $response->json();
        // return json_encode($payload);
        
    }

    public function checkRoomRate(){

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
            "HotelCodes" => [$this->tmid],
            "RateCode" => $this->ratecode,
            "Nationality" => $this->citizen ?? "EN",
            "IsDailyPrice" => false,
        ];

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
                [
                    "Adults" => $this->adults,
                    "RoomCount" => $this->roomCount
                ]
            ];

            if ( !empty($this->filters['child']) && !empty($this->filters['childrenage'] ) ) {
                $paxRooms[0]["Children"] = (int) $this->filters['child'];
                $paxRooms[0]["ChildrenAges"] = $this->childsage;
            }
            

        // Объединение всех частей в один массив
        $payload = array_merge($mainParams, [
            "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
            "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
        ]);

        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/CheckRoomRate", $payload);

        // if ($response->failed()) {
        //     return ['Error' => ["ErrorMessage" => 'HotelDetail Ошибка при запросе к API']];
        // }

        $data = $response->json();
        // return $payload;
            // dd($payload);

        if (!empty($data['Hotels'][0]['RoomTypes'][0])) {
            $roomType = $data['Hotels'][0]['RoomTypes'][0]; 
        
            $this->RoomTypeCode = $roomType['RoomTypeCode'] ?? null;
        
            $roomsFiltered = collect($this->hotelLocal[$this->tmid]['rooms'] ?? [])->filter(function ($room) {
                return isset($room['type_code']) && $room['type_code'] == $this->RoomTypeCode;
            });
        
            $this->roomid = $roomsFiltered->pluck('id')->first() ?? '';
        
            // Исправлено: корректное обращение к Name и BedTypeDesc
            $this->roomName = $roomType['Name'] ?? '';
            $this->bedDesc = $roomType['BedTypeDesc'] ?? '';
        
            $rateInfo = $roomType['RateInfos'][0] ?? null;
        
            if (!empty($rateInfo)) {
                $this->allotment = $rateInfo['Allotment'] ?? null;
                
                if (!empty($rateInfo['TotalPrice'])) {
                    $total = $rateInfo['TotalPrice'];
                    $nds = $total * 12 / 100;
                    $this->totalPrice = $total;
                    $this->nds = $nds;
                    $this->totalSum = $total + $nds;
                }
        
                $this->currency = $rateInfo['CurrencyCode'] ?? '';
        
                if (!empty($rateInfo['Refundable'])) {
                    $this->refundable = true;
                    $cancelPolicy = $rateInfo['CancelPolicyInfos'] ?? null;
        
                    if (!empty($cancelPolicy)) {
                        $this->cancelPolicy = $cancelPolicy['Amount'] ?? null;
                        $this->start_date_time = $cancelPolicy['From'] ?? null;
                        $this->end_date_time = $cancelPolicy['To'] ?? null;
                    }
                }
        
                // Используем data_get() для безопасного извлечения данных
                $this->mealid = data_get($rateInfo, 'MealInfo.MealType', '');
        
                if (!empty($this->mealid)) {
                    $this->mealall = Meal::all(['id', 'title', 'title_en']);
                    $mealVal = collect($this->mealall)->firstWhere('id', (int)$this->mealid)['title'] ?? '';
                    $this->meal = $mealVal;
                }
            }
        } else {
            $this->bookingSuccess = $data['Error']['ErrorMessage'] ?? 'Ошибка получения тарифа номера';
        }
            
        
    }
    
    public function createOrder(){

        $userId = Auth::id();
        $agentid = "swt-" . $userId;

        // RequestHeader (заголовки запроса)
        $requestHeader = [
                "AgentCode" => $this->tm_agent_code,
                "Password" => $this->tm_password,
                "UserName" => $this->tm_user_name,
                "TransactionID" => $this->token,
                "RequestTime" => now()->format('Y-m-d H:i:s')
            ];

        // Основные параметры запроса (без заголовков и PaxRooms)
        $mainParams = [
            "AgentRefID" => $agentid,
            "CheckIn" => $this->checkin,
            "CheckOut" => $this->checkout,
            "HotelCode" => $this->tmid,
            "RateCode" => $this->ratecode,
            "SpecialRequest" => $this->specdesc,
            "CurrencyCode" => $this->currency, // CNY
            "TotalPrice" => $this->totalPrice,
        ];
        // "Nationality" => $citizen,

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
                [
                    "Adults" => $this->adults,
                    "RoomCount" => $this->roomCount
                ]
            ];

            if ( !empty($this->filters['child']) && !empty($this->filters['childrenage'] ) ) {
                $paxRooms[0]["Children"] = (int) $this->filters['child'];
                $paxRooms[0]["ChildrenAges"] = $this->childsage;
            }
        
            $paxRooms[0] = array_merge($paxRooms[0], [
                "PaxNames" => [
                    [
                        "FirstName" => $this->paxfname,
                        "LastName" => $this->paxlname,
                        "Type" => "ADU",
                    ]
                ]
            ]);

        $ContactInfo = [
                "Email" => $this->user->email,
                "FirstName" => $this->user->name,
                "LastName" => $this->user->lastname,
                "PhoneNo" => $this->user->phone
        ];

        // Объединение всех частей в один массив
        $payload = array_merge($mainParams, [
            "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
            "RequestHeader" => $requestHeader,  // Просто вставляем массив RequestHeader
            "ContactInfo" => $ContactInfo
        ]);

            // dd($payload);

        if ( !empty($this->token) ){

            $childages = implode(',', $this->childsage ?? []);

            $book = Book::firstOrCreate(
                [
                    'book_token' => $this->token,
                ],
                [
                    'title' => $this->paxfname,
                    'title2' => $this->paxlname,
                    'hotel_id' => $this->hotelid,
                    'room_id' => $this->roomid,
                    'phone' => $this->phone,
                    'email' => $this->email,
                    'comment' => $this->specdesc,
                    'adult' => $this->adults,
                    'child' => $this->child,
                    'childages' => $childages,
                    'price' => $this->totalPrice,
                    'sum' => $this->totalSum,
                    'currency' => $this->currency,
                    'arrivalDate' => $this->checkin,
                    'departureDate' => $this->checkout,
                    // 'status' => $order['OrderInfo']['OrderStatus'],
                    'user_id' => $userId,
                    'api_type' => 'tourmind',
                ]
            );

                    
            $ruleid;
            if ($this->refundable == true){
                // создаем и привязываем к rate
                $rule = Rule::create(
                    [   
                        "title" => 'Бесплатная отмена до указанной даты',
                        "title_en" => 'Free cancellation until the specified date',
                        "amount" => $this->cancelPolicy,
                        "start_date_time" => $this->start_date_time,
                        "end_date_time" => $this->end_date_time
                    ]
                );

                $ruleid = $rule->id ?? '';
            }

            $rate = Rate::UpdateOrCreate(
                [
                    'rate_code' => $this->ratecode,
                    'hotel_id' => $this->hotelid,
                    'room_id' => $this->roomid,
                ],
                [
                    'title_en' => $this->roomName,
                    'desc_en' => $this->bedDesc,
                    'meal_id' => $this->mealid,
                    'allotment' => $this->allotment,
                    'currency' => $this->currency,
                    'refundable' => $this->refundable ? 1 : 0,
                    'rule_id' => $ruleid,
                    
                ]
            );


            

            if ( !isset($book->id) ){
                return "Ошибка заказ не создан на стейбук! Попробуйте через несколько секунд!";
            }

        }else{
            return "Ошибка токен не найден или не создан!";
        }


        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/CreateOrder", $payload);
            
        if ($response->failed()) {

            Log::channel('tourmind')->error('CreateOrder 433 - ', $response);

            return "HotelDetail Ошибка при запросе к API Tourmind! Попробуйте через несколько секунд!";
        }


        $order = $response->json();
        
        Log::channel('tourmind')->info('CreateOrder 441 - ', $order);
        
        if( isset($order['OrderInfo']['ReservationID']) ){

            Book::where('book_token', $this->token)
                ->update([
                    'status' => $order['OrderInfo']['OrderStatus'],
                    'rezervation_id' => $order['OrderInfo']['ReservationID']
                ]);

            // $this->orderCreated = true;

            return "Заказ создан! Статус - {$order['OrderInfo']['OrderStatus']}";

        }else{

            return $order['Error']['ErrorMessage'];

        }

        // return $payload;
            
    }

    public function render()
    {
        return view('livewire.booking-form')->extends('layouts.master');
    }
}

