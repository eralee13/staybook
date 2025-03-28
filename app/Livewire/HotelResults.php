<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;

class HotelResults extends Component
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;

    public $filters;
    public $city;
    public $dateRange;
    public $checkin;
    public $checkout;
    public $adults = 1;
    public $child;
    public $childrenage;
    public $citizen;
    public $rating;
    public $hotels;
    public $accommodation_type;
    public $pricemin;
    public $pricemax;
    public $food;
    public $early_in;
    public $early_out;
    public $cancelled;
    public $extra_place;
    //public $hotels = [7717374,830200,15521517,9273845,15542971,16743376,15527733,7666450,15647622,739487,781812,9070368,19831432,776289];
    public $hotelDetail;
    public $hotelLocalData;
    public $rooms = [];

    public function mount(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');

        // Получаем данные из сессии
        $this->filters = session()->get('hotel_search', []);
        
        if(!$this->filters['dateRange']){
            return redirect()->route('index');
        }

        if($this->filters['dateRange']){
            [$this->checkin, $this->checkout] = explode(' - ', $this->filters['dateRange']);
        }
        
        $this->hotelLocalData = Hotel::where('city', $this->filters['city'])
            ->where('tourmind_id', '!=', '')
            ->with('amenity')
            ->get()
            ->mapWithKeys(fn($hotel) => [$hotel->tourmind_id => $hotel])
            ->toArray();

        $this->hotels = Hotel::where('city', $this->filters['city'])->pluck('tourmind_id')->toArray();
       
        $this->hotelDetail = $this->getHotelDetail();
        // dd($this->hotelDetail);
        // dd($this->hotelLocalData);
    }

    public function getHotelDetail(){

        // RequestHeader (заголовки запроса)
        $requestHeader = [
                "AgentCode" => "tms_test",
                "Password" => "tms_test",
                "UserName" => "tms_test",
                "RequestTime" => now()->format('Y-m-d H:i:s')
            ];

        // Основные параметры запроса (без заголовков и PaxRooms)
        $mainParams = [
            "CheckIn" => $this->checkin,
            "CheckOut" => $this->checkout,
            "HotelCodes" => $this->hotels,
            "IsDailyPrice" => false,
            "Nationality" => $this->filters['citizen'] ?? "EN",
        ];

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
                [
                    "Adults" => $this->filters['adults'],
                    "RoomCount" => 1
                ]
            ];

            if ( !empty($this->filters['child']) && !empty($this->filters['childrenage'] ) ) {
                $paxRooms[0]["Children"] = (int) $this->filters['child'];
                $paxRooms[0]["ChildrenAges"] = $this->filters['childrenage'];
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
        // return $payload;
        
    }

    public function updated()
    {
        $this->filterHotels();
    }

    public function filterHotels()
    {
        $query = Hotel::query()->where('city', $this->city);

        if ($this->accommodation_type) {
            $query->where('type', $this->accommodation_type);
        }

        if ($this->pricemin && $this->pricemax) {
            $query->whereBetween('price', [$this->pricemin, $this->pricemax]);
        }

        if ($this->rating) {
            $query->where('rating', '>=', $this->rating);
        }

        if ($this->food) {
            $query->where('food', $this->food);
        }

        if ($this->early_in) {
            $query->where('early_checkin', $this->early_in);
        }

        if ($this->early_out) {
            $query->where('late_checkout', $this->early_out);
        }

        if ($this->cancelled) {
            $query->where('is_cancellable', true);
        }

        if ($this->extra_place) {
            $query->where('extra_bed_available', true);
        }

        $this->hotels = $query->get()->toArray();
    }

    public function checkRoomRate(){
            $ratecode;

            // RequestHeader (заголовки запроса)
            $requestHeader = [
                    "AgentCode" => "tms_test",
                    "Password" => "tms_test",
                    "UserName" => "tms_test",
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ];

            // Основные параметры запроса (без заголовков и PaxRooms)
            $mainParams = [
                "CheckIn" => $this->checkin,
                "CheckOut" => $this->checkout,
                "HotelCodes" => $this->hotels,
                "RateCode" => $ratecode,
                "Nationality" => $this->citizen ?? "EN",
            ];

            // PaxRooms (информация о размещении гостей)
            $paxRooms = [
                    [
                        "Adults" => $this->adults,
                        "RoomCount" => 1
                    ]
                ];

                if ( !empty($this->child) && !empty($this->childrenage ) ) {
                    $paxRooms[0]["Children"] = (int) $this->child;
                    $paxRooms[0]["ChildrenAges"] = $this->childrenage;
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

            return $response->json();
            // return $payload;
            
    }
    public function createOrder(){

        $userId = Auth::id();
        $checkin = $this->checkin;
        $checkout = $this->checkout;
        $adult = $this->checkout;
        $child = $this->checkout;
        $childrenage = $this->checkout;
        $room = 1;
        $front_hotelid;
        $nationality;
        $ratecode;
        $agentid = "swt" . $userId;
        $email = 'xxx@google.com';
        $firstname = 'era';
        $lastname = 'era';
        $phone = '1521777777';
        $currency;
        $specdesc;
        
        $checkRoomPrice = checkRoomRate($checkin,$checkout,$front_hotelid,$nationality,$adult,$child,$childrenage,$room,    $ratecode);

        // RequestHeader (заголовки запроса)
        $requestHeader = [
                "AgentCode" => "tms_test",
                "Password" => "tms_test",
                "UserName" => "tms_test",
                "RequestTime" => now()->format('Y-m-d H:i:s')
            ];

        // Основные параметры запроса (без заголовков и PaxRooms)
        $mainParams = [
            "AgentRefID" => $agentid,
            "CheckIn" => $checkin,
            "CheckOut" => $checkout,
            "HotelCodes" => [$front_hotelid],
            "RateCode" => $ratecode,
            "SpecialRequest" => $specdesc,
            "CurrencyCode" => $currency, // CNY
            "TotalPrice" => $checkRoomPrice,
        ];
        // "Nationality" => $nationality,

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
                [
                    "Adults" => $this->adults,
                    "RoomCount" => 1
                ]
            ];

            if ( !empty($this->child) && !empty($this->childrenage ) ) {
                $paxRooms[0]["Children"] = (int) $this->child;
                $paxRooms[0]["ChildrenAges"] = $this->childrenage;
            }
        
        $ContactInfo = [
                "Email" => $email,
                "FirstName" => $firstname,
                "LastName" => $lastname,
                "PhoneNo" => $phone
        ];

        // Объединение всех частей в один массив
        $payload = array_merge($mainParams, [
            "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
            "RequestHeader" => $requestHeader,  // Просто вставляем массив RequestHeader
            "ContactInfo" => $ContactInfo
        ]);

        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/HotelDetail", $payload);

        // if ($response->failed()) {
        //     return ['Error' => ["ErrorMessage" => 'HotelDetail Ошибка при запросе к API']];
        // }

        $order = $response->json();

        Log::channel('tourmind')->info('CreateOrder - ', $order);

        if( !empty($order['OrderInfo']['ReservationID']) ){

            $Book = Book::updateOrCreate(
                [
                    'title' => $firstName.' '.$lastName,
                    'hotel_id' => $hotelid,
                    'phone' => $phone,
                    'email' => $email,
                    'comment' => $comment,
                    'adult' => $adult,
                    'child' => $children,
                    'arrivalDate' => $order['ResponseHeader']['ResponseTime'],
                    'departureDate' => '',
                    'book_token' => $order['ResponseHeader']['TransactionID'],
                    'status' => $order['OrderInfo']['OrderStatus'],
                    'uesr_id' => $userId,
                ]
            );

            return ['message' => 'Заказ создан', 'status' => $data['OrderInfo']['OrderStatus']];

        }else{

            return ['Error' => $data['Error']['ErrorMessage']];

        }
        // return $payload;
            
    }

    public function loadRooms($hotelId)
    {
        //$this->rooms = Room::where('hotel_id', $hotelId)->get()->toArray();
    }

    public function render()
    {
        return view('livewire.hotel-results')->extends('layouts.master');
    }
}

