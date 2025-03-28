<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use App\Models\Room;

class HotelRooms extends Component
{   
    protected string $baseUrl;
    public $hotelId;
    public $tmid;
    public $rates;
    public $hotel;
    public $checkin;
    public $checkout;
    public $rooms;
    public $hotelLocal;

    public function mount()
    {
        $this->hotelId = (int)$_GET['hotelId'];
        $this->tmid = (int)$_GET['tmid'];
        $this->loadRooms();
    }

    public function loadRooms()
    {
        $this->baseUrl = config('app.tm_base_url');

        if( !Auth::check() ){
            return redirect()->route('index');
        }

        // Получаем данные из сессии
        $this->filters = session()->get('hotel_search', []);
        
        if(!$this->filters['dateRange']){
            return redirect()->route('index');
        }

        if($this->filters['dateRange']){
            [$this->checkin, $this->checkout] = explode(' - ', $this->filters['dateRange']);
        }
        
        try {

            $roomSort = $this->oneHotelDetail();
            $this->rooms = $this->minSort($roomSort);

            $this->hotelLocal = Hotel::where('tourmind_id', $this->tmid)->limit(1)
            ->with('amenity')
            ->get()
            ->mapWithKeys(fn($hotel) => [$hotel->tourmind_id => $hotel])
            ->toArray();

            //dd($this->rooms);
            // dd($roomSort);
            // dd($this->hotelLocal);

        } catch (\Throwable $th) {
            dd( $th);
        }
    }

    public function oneHotelDetail(){

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
            "HotelCodes" => [$this->tmid],
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
