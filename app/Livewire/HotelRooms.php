<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Image;
use App\Models\Book;

class HotelRooms extends Component
{   
    protected string $baseUrl;
    public $hotelId, $tmid, $hotel, $rooms, $images, $hotelLocal;
    public $rates, $checkin, $checkout, $childsage;
    public $childdrenage;
    public $childdrenage2;
    public $childdrenage3;
    public $roomCount = 1;
    public $bookingSuccess = null;
    public $token;

    public function __construct(){

        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

    }

    public function mount()
    {
        $this->hotelId = (int)$_GET['hotelId'];
        $this->tmid = (int)$_GET['tmid'];

        do {
            $this->token = Str::random(40);
        } while (Book::where('book_token', $this->token)->exists());

        $this->loadRooms();
    }

    public function loadRooms()
    {

        if( !Auth::check() ){
            return redirect()->route('index');
        }

        // Получаем данные из сессии
        $this->filters = session()->get('hotel_search', []);
        
        if( !isset($this->filters['dateRange']) ){
            return redirect()->route('index');
        }

        if($this->filters['dateRange']){
            [$this->checkin, $this->checkout] = explode(' - ', $this->filters['dateRange']);

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

            $roomSort = $this->oneHotelDetail();
            $this->rooms = $this->minSort($roomSort);

            $this->hotelLocal = Hotel::where('tourmind_id', $this->tmid)
            ->limit(1)
            ->with(['amenity','rooms'])
            ->get()
            ->mapWithKeys(fn($hotel) => [$hotel->tourmind_id => $hotel])
            ->toArray();

            $images = Image::where('hotel_id', $this->hotelId)->get();
            $this->images = $images->pluck('image')->toArray();
            // dd($this->images);
            // dd($roomSort);
            // dd($this->hotelLocal);

        } catch (\Throwable $th) {
            $this->bookingSuccess = "Ошибка получения данных - Hotel Rooms";
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
            "Nationality" => $this->filters['citizen'] ?? "EN",
        ];

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
                [
                    "Adults" => $this->filters['adults'],
                    "RoomCount" => $this->roomCount,
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

    public function minSort($data){

        if (!isset($data['Hotels'][0]['RoomTypes'])) {
            return $data; // Если нет номеров — возвращаем как есть
        }
    
        // Сортируем тарифы (RateInfos) внутри каждой комнаты
        foreach ($data['Hotels'][0]['RoomTypes'] as &$roomType) {
            if (!empty($roomType['RateInfos'])) {
                usort($roomType['RateInfos'], function ($a, $b) {
                    return (float) $a['TotalPrice'] <=> (float) $b['TotalPrice'];
                });
            }
        }
        unset($roomType); // Убираем ссылку

        // Теперь сортируем RoomTypes по минимальному тарифу
        usort($data['Hotels'][0]['RoomTypes'], function ($a, $b) {
            $minPriceA = !empty($a['RateInfos']) ? (float) $a['RateInfos'][0]['TotalPrice'] : PHP_FLOAT_MAX;
            $minPriceB = !empty($b['RateInfos']) ? (float) $b['RateInfos'][0]['TotalPrice'] : PHP_FLOAT_MAX;

            return $minPriceA <=> $minPriceB;
        });     
        
        return $data;
    }

    public function render()
    {
        return view('livewire.hotel-rooms')->extends('layouts.master');
    }
}
