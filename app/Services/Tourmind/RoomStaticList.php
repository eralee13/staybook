<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;
use App\Models\CategoryRoom;


class RoomStaticList
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = $this->tmApiService->getBaseUrl();
    }

    public function getRoomList(){

        $hotels = Hotel::pluck('tourmind_id'); // Получаем только одно поле как коллекцию

        foreach ($hotels as $tourmindId) {
            
            $payload = [
                "HotelCode" => $tourmindId,
                "RequestHeader" => [
                    "AgentCode" => "tms_test",
                    "Password" => "tms_test",
                    "UserName" => "tms_test",
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
                    CategoryRoom::updateOrCreate(

                        [
                            'tourmind_id' => $tourmindId,
                            'type_code' => (int)$type['RoomTypeCode'],
                        ],
                        [
                            'type_code' => (int)$type['RoomTypeCode'],
                            'title_en' => (string)$type['RoomTypeName'],
                            'description_en' => (string)$type['BedTypeDesc']
                        ],
                        
                    );
                } catch (Exception $e) {
                    // Обработка исключения
                    Log::error('Ошибка CategoryRoom: ' . $e->getMessage(), ['exception' => $e]);

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
    }
}