<?php

namespace App\Livewire;

use App\Models\CancellationRule;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Meal;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Rule;

class BookingForm extends Component
{
    public $filters;
    public $hotel, $room, $hotelid, $tmid, $roomid, $book, $order;
    public $city, $adults, $child, $checkin, $checkout, $childdrenage, $childdrenage2, $guestsall;
    public $childdrenage3, $childsage, $citizen, $rating, $food, $early_in, $early_out;
    public $cancelled, $extra_place, $pricemin, $pricemax, $nationality, $roomCount = 1;
    public $hotelName, $hotelimg,  $hoteldesc, $hoteladdress, $hotelcity, $hotellat, $hotellng;
    public $roomName, $bedDesc, $allotment;
    public $hotelLocal;
    public $cancelDate;
    public $RoomTypeCode;
    public $ratecode;
    public $currency;
    public $totalPrice;
    public $totalSum;
    public $specdesc;
    public $meal, $mealid, $mealall;
    public $checkRoomPrice;
    public $token, $utc;
    public $paxfname = 'Don';
    public $paxlname = 'Joe';
    public $paxfname2 = 'dee';
    public $paxlname2 = 'see';
    public $paxfname3 = 'jon';
    public $paxlname3 = 'son';
    public $paxfname4 = 'djeki';
    public $paxlname4 = 'chan';
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
    public $bookingSuccess = null;

    protected $rules = [
        'paxfname' => 'required|string|min:3',
        'paxlname' => 'required|string|min:3',
        'lastname' => 'required|string|min:3',
        'lastname' => 'required|string|min:3',
        'email' => 'required|email',
        // 'phone' => ['required', 'string', 'regex:/^\+?[0-9\- ]{10,20}$/'],
    ];

    public function __construct(){

        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

    }

    public function mount()
    {

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
            $utc = $this->getUtcOffsetByCityName();
            $this->utc = $utc;

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
            $this->hotellat = $this->hotelLocal[$this->tmid]['lat'];
            $this->hotellng = $this->hotelLocal[$this->tmid]['lng'];

            $this->user = auth()->user();
            $this->firstname = $this->user->name;
            $this->lastname = $this->user->lastname;

            $this->checkRoomRate();

        } catch (\Throwable $th) {
            $this->bookingSuccess = "156 Ошибка получения данных - Book".$th->getMessage();
        }
    }

    public function confirmBooking()
    {

        $validatedData = $this->validate();

        if ($this->token) {
            $this->checkRoomRate();
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
        // dd(json_encode($payload, true));
        // dd($payload);

        if (!empty($data['Hotels'][0]['RoomTypes'][0])) {
            $roomType = $data['Hotels'][0]['RoomTypes'][0];

            $this->RoomTypeCode = $roomType['RoomTypeCode'] ?? null;

            $roomsFiltered = collect($this->hotelLocal[$this->tmid]['rooms'] ?? [])->filter(function ($room) {
                return isset($room['type_code']) && $room['type_code'] == $this->RoomTypeCode;
            });

            $this->roomid = $roomsFiltered->pluck('id')->first() ?? null;

            $this->roomName = $roomType['Name'] ?? '';
            $this->bedDesc = $roomType['BedTypeDesc'] ?? '';

            $rateInfo = $roomType['RateInfos'][0] ?? null;

            if (!empty($rateInfo)) {
                $this->allotment = $rateInfo['Allotment'] ?? null;

                if (!empty($rateInfo['TotalPrice'])) {
                    $total = (float)$rateInfo['TotalPrice'];
                    $nds = $total * 12 / 100;
                    $this->totalPrice = (float)$total;
                    $this->nds = $nds;
                    $this->totalSum = $total + $nds;
                }

                $this->currency = $rateInfo['CurrencyCode'] ?? '';

                if ($rateInfo['Refundable'] == true) {
                    $this->refundable = true;
                    $cancelPolicys = $rateInfo['CancelPolicyInfos'];

                    if ( !empty($cancelPolicys) ) {

                        $this->cancelPolicy = (float)$cancelPolicys[0]['Amount'] ?? null;
                        $this->start_date_time = $cancelPolicys[0]['From'] ?? null;
                        $this->end_date_time = $cancelPolicys[0]['To'] ?? null;

                        if( $cancelPolicys[0]['From'] ){
                            $this->cancelDate = $cancelPolicys[0]['From'];
                        }

                    }
                }

                // Используем data_get() для безопасного извлечения данных
                $this->mealid = data_get($rateInfo, 'MealInfo.MealType', '');

                if (!empty($this->mealid)) {

                    $meal = Meal::where('api_id', (int)$this->mealid)
                        ->where('api_name', 'tm')
                        ->select('title')
                        ->first();

                    $this->meal = $meal->title ?? '';
                }
            }
        } else {
            $this->bookingSuccess = $data['Error']['ErrorMessage'] ?? 'Ошибка получения тарифа номера';
        }


    }

    public function createOrder(){

        $userId = Auth::id();

        do {
            $agentid = "SWT-" . now()->format('Ymd') . '-' . Str::uuid();
        } while (Book::where('agent_ref_id', $agentid)->exists());

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
                "RoomCount" => $this->roomCount,
            ]
        ];

        if ( !empty($this->filters['child']) && !empty($this->filters['childrenage'] ) ) {
            $paxRooms[0]["Children"] = (int) $this->filters['child'];
            $paxRooms[0]["ChildrenAges"] = $this->childsage;
        }

        $paxList = [];

        for ($i = 0; $i < $this->roomCount; $i++) {
            $j = $i + 1;

            if($j > 1){

                $paxList[] = [
                    "FirstName" => $this->{'paxfname' . $j},
                    "LastName" => $this->{'paxlname' . $j},
                    "Type" => "ADU",
                ];

            }else{
                $paxList[] = [
                    "FirstName" => $this->paxfname,
                    "LastName" => $this->paxlname,
                    "Type" => "ADU",
                ];
            }

        }

        $paxRooms[0]["PaxNames"] = $paxList;

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
        // die;

        // local create order
        $existbook = Book::where('book_token', $this->token)->first();

        if ( $existbook ){
            return "Этот бронь уже существует!";
        }

        if ( empty($this->roomid) ){

            $thisroom = Room::updateOrCreate(
                [
                    'hotel_id' => (int)$this->hotelid,
                    'type_code' => $this->RoomTypeCode,
                ],
                [
                    'title_en' => $this->roomName,
                    'bed' => $this->bedDesc
                ],

            );

            if ( isset($thisroom->id) ){
                $this->roomid = $thisroom->id;
            }
        }

        if ( $this->roomCount == 1){
            $this->guestsall = [$this->paxfname .' '. $this->paxlname];
        }
        elseif ( $this->roomCount == 2 ){
            $this->guestsall = [$this->paxfname .' '. $this->paxlname, $this->paxfname2 .' '. $this->paxlname2];
        }
        elseif ( $this->roomCount == 3 ){
            $this->guestsall= [$this->paxfname .' '. $this->paxlname, $this->paxfname2 .' '. $this->paxlname2, $this->paxfname3 .' '. $this->paxlname3];
        }
        elseif ( $this->roomCount == 4 ){
            $this->guestsall = [$this->paxfname .' '. $this->paxlname, $this->paxfname2 .' '. $this->paxlname2, $this->paxfname3 .' '. $this->paxlname3, $this->paxfname4 .' '. $this->paxlname4];
        }


        $guests = implode(',', $this->guestsall ?? []);

        $childages = implode(',', $this->childsage ?? []);

        $offset = str_replace('UTC', '', $this->utc); // '+3'

        // Преобразуем в +03:00
        $formattedOffset = sprintf('%+03d:00', (int)$offset);

        // Получаем текущую дату/время в нужной зоне
        $utcdatetime = Carbon::now($formattedOffset);

        // Форматируем результат
        $utcdatetime = $utcdatetime->format('Y-m-d H:i:s');

        $book = Book::firstOrCreate(
            [
                'book_token' => $this->token,
            ],
            [
                'title' => $guests,
                'title2' => '',
                'hotel_id' => $this->hotelid,
                'room_id' => $this->roomid,
                'phone' => $this->phone,
                'email' => $this->email,
                'comment' => $this->specdesc,
                'adult' => $this->adults,
                'child' => $this->child,
                'childages' => $childages ?? '',
                'price' => $this->totalPrice,
                'sum' => $this->totalSum,
                'utc' => $offset,
                'currency' => $this->currency,
                'cancel_date' => $utcdatetime,
                'arrivalDate' => $this->checkin,
                'departureDate' => $this->checkout,
                // 'status' => $order['OrderInfo']['OrderStatus'],
                'user_id' => $userId,
                'api_type' => 'tourmind',
                'agent_ref_id' => $agentid,
            ]
        );


        $ruleid;
        if ($this->refundable == true){
            // создаем и привязываем к rate
            $rule = CancellationRule::create(
                [
                    "title" => 'Бесплатная отмена до указанной даты',
                    "title_en" => 'Free cancellation until the specified date',
                    "amount" => $this->cancelPolicy,
                    // "start_date_time" => $this->start_date_time,
                    "end_date_time" => $this->start_date_time
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
                'rule_id' => $ruleid ?? null,

            ]
        );


        if ( !isset($book->id) ){
            return "Ошибка заказ не создан на стейбук! Попробуйте через несколько секунд!";
        }

        // tourmind create order
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/CreateOrder", $payload);

        if ( $response->failed() ) {

            Log::channel('tourmind')->error('521 CreateOrder - ', $payload);
            Log::channel('tourmind')->error('522 CreateOrder - ', $response);

            return "522 Book order Ошибка при запросе к API Tourmind! Попробуйте через несколько секунд!";
        }


        $order = $response->json();

        Log::channel('tourmind')->info('CreateOrder 528 - ', $order);

        if( isset($order['OrderInfo']['ReservationID']) ){

            Book::where('book_token', $this->token)
                ->update([
                    'status' => $order['OrderInfo']['OrderStatus'],
                    'rezervation_id' => $order['OrderInfo']['ReservationID']
                ]);

            $this->orderCreated = true;
            return "Бронирование успешно создано!"; // Статус - {$order['OrderInfo']['OrderStatus']}";

            // session()->flash('success', "Бронирование успешно создано! Статус - {$order['OrderInfo']['OrderStatus']}");
            // return redirect()->route('booking.success');


        }else{

            return $order['Error']['ErrorMessage'];

        }

        // return $payload;

    }

    function getUtcOffsetByCityName()
    {
        $cityName = strtolower($this->city);
        $foundTimezone = null;

        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            if (strtolower(substr($timezone, strrpos($timezone, '/') + 1)) == $cityName) {
                $foundTimezone = $timezone;
                break;
            }
        }

        if ($foundTimezone) {
            $tz = new DateTimeZone($foundTimezone);
            $now = new DateTime('now', $tz);
            $offset = $tz->getOffset($now) / 3600;

            return 'UTC' . ($offset >= 0 ? '+' : '') . $offset;
        }

        return 'Timezone not found';
    }


    public function render()
    {
        return view('livewire.booking-form')->extends('layouts.master');
    }
}
