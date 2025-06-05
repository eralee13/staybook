<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
// use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;

class HotelResults extends Component
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;
    protected string $tm_agent_code;
    protected string $tm_user_name;
    protected string $tm_password;

    public $filters;
    public $city;
    public $dateRange;
    public $checkin;
    public $checkout;
    public $adult;
    public $child;
    public $childrenage1;
    public $childrenage2;
    public $childrenage3;
    public $roomCount = 1;
    //public $citizen;
    public $rating;
    public $meal;
    public $early_in;
    public $late_out;
    //public $cancelled;
    //public $extra_place;
    public $pricemin;
    public $pricemax;
    public $hotels;
    //public $accommodation_type;
    public $childsage;
    
    //public $hotels = [7717374,830200,15521517,9273845,15542971,16743376,15527733,7666450,15647622,739487,781812,9070368,19831432,776289];
    public $hotelDetail;
    public $hotelLocalData;
    public $rooms = [];
    public $bookingSuccess = null;

    public function __construct(){

        
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

    }

    public function mount()
    {
        // $this->tmApiService = $tmApiService;

        // Получаем данные из сессии
        $this->filters = session()->get('hotel_search', []);
        
        if( !isset($this->filters['dateRange']) ){
            return redirect()->route('index');
        }

        // заполняем данные из сессии
        if( isset($this->filters['dateRange']) ){
            [$this->checkin, $this->checkout] = explode(' - ', $this->filters['dateRange']);
        }
        
        $this->city = $this->filters['city'];
        $this->dateRange = $this->filters['dateRange'];
        $this->adult = (int)$this->filters['adult'];
        $this->child = (int)$this->filters['child'];
        $this->childrenage1 = (int)$this->filters['childrenage1'];
        $this->childrenage2 = (int)$this->filters['childrenage2'];
        $this->childrenage3 = (int)$this->filters['childrenage3'];
        $this->roomCount = (int)$this->filters['roomCount'];
        //$this->citizen = $this->filters['citizen'];
        //$this->accommodation_type = $this->filters['accommodation_type'];
        $this->pricemin = $this->filters['pricemin'];
        $this->pricemax = $this->filters['pricemax'];
        $this->rating = (int)$this->filters['rating'];
        $this->meal = $this->filters['meal'];
        $this->early_in = $this->filters['early_in'];
        $this->late_out = $this->filters['late_out'];
        //$this->cancelled = $this->filters['cancelled'];
        //$this->extra_place = $this->filters['extra_place'];

        if ( $this->child == 1 ){
            $this->childsage1 = [(int)$this->childrenage1];
        }
        if ( $this->child == 2 ){
            $this->childsage = [(int)$this->childrenage1, (int)$this->childrenage2];
        }
        if ( $this->child == 3 ){
            $this->childsage = [(int)$this->childrenage1, (int)$this->childrenage2, (int)$this->childrenage3];
        }

        $this->tmGetHotels();
        
        
    }

    public function updated()
    {   
        // Сохраняем данные в сессии
        session()->put('hotel_search', [
            'city' => $this->city,
            'dateRange' => $this->dateRange,
            'adult' => (int)$this->adult,
            'child' => (int)$this->child,
            'childrenage1' => (int)$this->childrenage1,
            'childrenage2' => (int)$this->childrenage2,
            'childrenage3' => (int)$this->childrenage3,
            'roomCount' => (int)$this->roomCount,
            //'accommodation_type' => $this->accommodation_type,
            //'citizen' => $this->citizen,
            'rating' => (int)$this->rating,
            'meal' => $this->meal,
            'early_in' => $this->early_in,
            'late_out' => $this->late_out,
            //'cancelled' => (bool)$this->cancelled,
            //'extra_place' => (bool)$this->extra_place,
            'pricemin' => $this->pricemin,
            'pricemax' => $this->pricemax,
        ]);
        
        // $this->filterHotels();
    }

    public function filterHotels()
    {
        // start tourmind
        $this->tmGetHotels();

    }

    public function tmGetHotels(){
        // tourmind get data hotels

        if ( $this->child == 1 ){
            $this->childsage = [(int)$this->childrenage1];
        }
        if ( $this->child == 2 ){
            $this->childsage = [(int)$this->childrenage1, (int)$this->childrenage2];
        }
        if ( $this->child == 3 ){
            $this->childsage = [(int)$this->childrenage1, (int)$this->childrenage2, (int)$this->childrenage3];
        }
        
        // get local hotels filtereble
        $query = Hotel::where('city', $this->city);
        $query->where('tourmind_id', '!=', '');

        if ($this->rating){
            $query->where('rating', '=', (int)$this->rating);   
        }
        // if ($this->early_in){
        //     $query->where('early_in', $this->early_in);   
        // }
        // if ($this->early_out){
        //     $query->where('early_out', '>=', $this->early_out);   
        // }
            
        $query->with('amenity');
        $this->hotelLocalData = $query->get()
            ->mapWithKeys(fn($hotel) => [$hotel->tourmind_id => $hotel])
            ->toArray();



            // get tm local ids
        $hoteles = Hotel::where('city', $this->city);

        if ($this->rating){
            $hoteles->where('rating', '=', (int)$this->rating);   
        }
        // if ($this->early_in){
        //     $hoteles->where('early_in', $this->early_in);   
        // }
        // if ($this->early_out){
        //     $hoteles->where('early_out', '>=', $this->early_out);   
        // }

        $this->hotels = $hoteles->pluck('tourmind_id')->toArray();
        
        $this->reset('hotelDetail'); 
        $this->hotelDetail = $this->getHotelDetail();

        // dd($this->hotelDetail);

        if( isset($this->hotelDetail['Hotels']) ){

            // merge array local to api 
            foreach ($this->hotelDetail['Hotels'] as &$hotele) {
                $hotelCode = $hotele['HotelCode'];
            
                if (isset($this->hotelLocalData[$hotelCode])) {
                    // Объединяем данные
                    $hotele['localData'] = $this->hotelLocalData[$hotelCode];
                } else {
                    // Если нет локальных данных, добавляем null
                    $hotele['localData'] = null;
                }
            }
            unset($hotele); // Разрываем ссылку, чтобы избежать проблем
            
            

            // orderby prices, food, cancelled
            $this->hotelDetail['Hotels'] = array_map(function ($hoteli) {
                if (empty($hoteli['RoomTypes'])) {
                    return null; // Убираем отель, если у него нет номеров
                }
            
                $hotelHasValidRoomType = false;
                $lowestRate = null;
                $lowestRateRoomType = null;
            
                foreach ($hoteli['RoomTypes'] as &$roomType) {
                    // Фильтруем тарифы по цене, отмене и питанию
                    $roomType['RateInfos'] = array_filter($roomType['RateInfos'], function ($rateInfo) {
                        $price = (float) $rateInfo['TotalPrice'];
                        $minPrice = $this->pricemin != null ? (float) $this->pricemin : null;
                        $maxPrice = $this->pricemax != null ? (float) $this->pricemax : null;
            
                        // Фильтр по цене
                        if ($minPrice != null && $price < $minPrice) {
                            return false;
                        }
                        if ($maxPrice != null && $price > $maxPrice) {
                            return false;
                        }

                        // Фильтр по отмене (если $this->cancelled == true, оставляем только Refundable == true)
                    //    if ($this->cancelled == true) {
                    //        if (!isset($rateInfo['Refundable']) || $rateInfo['Refundable'] != true) {
                    //            return false;
                    //        }
                    //    }
            
                        // Фильтр по питанию (если $this->food == true, оставляем только MealInfo['MealType'] == "1")
                        if ( !empty($this->meal) ) {
                            if (!isset($rateInfo['MealInfo']['MealType']) || $rateInfo['MealInfo']['MealType'] != $this->meal) {
                                return false;
                            }
                        }
            
                        return true;
                    });
            
                    // Если после фильтрации остались тарифы
                    if (!empty($roomType['RateInfos'])) {
                        $hotelHasValidRoomType = true;
            
                        // Находим минимальную цену в этом номере
                        $lowestRateInRoom = min(array_column($roomType['RateInfos'], 'TotalPrice'));
            
                        // Сохраняем номер с самым дешевым тарифом
                        if ($lowestRate == null || $lowestRateInRoom < $lowestRate) {
                            $lowestRate = $lowestRateInRoom;
                            $lowestRateRoomType = $roomType;
                        }
                    }
                }
                unset($roomType);
            
                // Если после фильтрации у отеля нет номеров, удаляем его
                if (!$hotelHasValidRoomType || $lowestRateRoomType == null) {
                    return null;
                }
            
                // Оставляем только один номер с минимальным тарифом
                $hoteli['RoomTypes'] = [$lowestRateRoomType];
            
                return $hoteli;
            }, $this->hotelDetail['Hotels']);
            
            // Фильтруем массив отелей, удаляя пустые элементы
            $this->hotelDetail['Hotels'] = array_values(array_filter($this->hotelDetail['Hotels']));
            
            // Log::channel('tourmind')->info('DEbUG - ', $this->hotelDetail);
            // dd($this->hotelDetail);
            
            

            // Оставляем только самый дешевый тариф в каждом отеле
            foreach ($this->hotelDetail['Hotels'] as &$hotel) {
                foreach ($hotel['RoomTypes'] as &$roomType) {
                    if (!empty($roomType['RateInfos'])) {
                        // Сортируем тарифы внутри номера по цене и оставляем только один (самый дешевый)
                        usort($roomType['RateInfos'], fn($a, $b) => (float) $a['TotalPrice'] <=> (float) $b['TotalPrice']);
                        $roomType['RateInfos'] = [reset($roomType['RateInfos'])]; // Берём первый элемент (самый дешевый)
                    }
                }
            }
            unset($hotel, $roomType); // Чистим ссылки для избежания багов



            // Сортируем отели по самому низкому тарифу
            usort($this->hotelDetail['Hotels'], function ($a, $b) {
                // Получаем минимальный тариф для каждого отеля
                $minPriceA = null;
                foreach ($a['RoomTypes'] as $roomType) {
                    if (!empty($roomType['RateInfos'])) {
                        $minPriceA = min(array_column($roomType['RateInfos'], 'TotalPrice'));
                        break; // Останавливаемся, как только нашли хотя бы один тариф
                    }
                }

                $minPriceB = null;
                foreach ($b['RoomTypes'] as $roomType) {
                    if (!empty($roomType['RateInfos'])) {
                        $minPriceB = min(array_column($roomType['RateInfos'], 'TotalPrice'));
                        break;
                    }
                }

                // Если у одного из отелей нет тарифов, он будет позже в списке
                if ($minPriceA === null) return 1;
                if ($minPriceB === null) return -1;

                return $minPriceA <=> $minPriceB; // Сортировка по возрастанию
            });

        }
        
        // dd($this->hotelDetail);
        // dd($this->hotelLocalData);
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
                //"Nationality" => $this->citizen ?? "EN",
            ];

            // PaxRooms (информация о размещении гостей)
            $paxRooms = [
                    [
                        "Adults" => (int)$this->adult,
                        "RoomCount" => (int)$this->roomCount,
                    ]
                ];


                if ( !empty($this->child) && !empty($this->childrenage1 ) ) {
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

    public function render()
    {
        return view('livewire.hotel-results')->extends('layouts.master');
    }
}

