<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;
use App\Models\Room;


class RoomStaticList
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;
    protected string $tm_agent_code;
    protected string $tm_user_name;
    protected string $tm_password;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');
    }

    public function getRoomList(){

        $hotels = Hotel::whereNotNull('tourmind_id')
            ->where('tourmind_id', '!=', '')
            ->get(['id', 'tourmind_id']) // Извлекаем только нужные колонки
            ->toArray();

            // dd($hotels);

        foreach ($hotels as $hotel) {
            $tourmindId = $hotel['tourmind_id'];
            $hId = $hotel['id'];

            $payload = [
                "HotelCode" => $tourmindId,
                "RequestHeader" => [
                    "AgentCode" => $this->tm_agent_code,
                    "Password" => $this->tm_password,
                    "UserName" => $this->tm_user_name,
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ]
            ];
    
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/RoomStaticList", $payload);
    
            if ($response->failed()) {
                return ['error' => 'Ошибка при запросе к API', 'status' => $response->status()];
            }

            $data = $response->json();
            $types = $data['RoomTypes'] ?? [];
            
            foreach($types as $type){

                try {
                    Room::updateOrCreate(

                        [
                            'hotel_id' => (int)$hId,
                            'type_code' => (int)$type['RoomTypeCode'],
                        ],
                        [
                            'title_en' => (string)$type['RoomTypeName'],
                            'bed' => (string)$type['BedTypeDesc']
                        ],
                        
                    );
                } catch (Exception $e) {
                    // Обработка исключения
                    Log::error('Ошибка Room: ' . $e->getMessage(), ['exception' => $e]);

                    // Возвращаем JSON с ошибкой
                    // return response()->json([
                    //     'error' => true,
                    //     'message' => 'Произошла ошибка на сервере',
                    //     'details' => $e->getMessage() // Можно скрыть в продакшене
                    // ], 500);
                }
            }

        }

        return ['message' => 'Данные обновлены', 'count' => count($types)];
        //return $hotels;
    }
}