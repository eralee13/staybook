<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Tourmind\TmApiService;

class CheckRoomRate
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = $this->tmApiService->getBaseUrl();
    }

    public function getCheckRoomRate(){

        $countryCodes = $this->tmApiService->getCountryCodes();

        // foreach ($countryCodes as $countryCode) {
            
            $payload = [
                "CheckIn" => "2018-08-25",
                "CheckOut" => "2018-08-26",
                "HotelCodes" => [20783750],
                "Nationality" => "UA",
                "PaxRooms" => [
                    [
                    "Adults" => 1,
                    "Children" => 1,
                    "ChildrenAges" => [8],
                    "RoomCount" => 1
                    ]
                    ],
                "RateCode" => "13800206",
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
            ])->post("{$this->baseUrl}/CheckRoomRate", $payload);
    
            if ($response->failed()) {
                return ['error' => 'CheckRoomRate Ошибка при запросе к API', 'status' => $response->status()];
            }

            $data = $response->json();
            //$regions = $data['RegionListResult']['Regions'] ?? [];
            
            // foreach($regions as $region){

            //     try {
            //         DB::table('cities')->updateOrInsert(
            //             ['country_id' => $region['RegionID']], // Условие проверки
            //             [
            //                 'name' => $region['Name'],
            //                 'country_id' => (int)$region['RegionID'],
            //                 'country_code' => (string)$region['CountryCode'],
            //             ]
            //         );
                    
            //     } catch (Exception $e) {
            //         // Обработка исключения
            //         Log::error('Ошибка: ' . $e->getMessage(), ['exception' => $e]);

            //         // Возвращаем JSON с ошибкой
            //         // return response()->json([
            //         //     'error' => true,
            //         //     'message' => 'Произошла ошибка на сервере',
            //         //     'details' => $e->getMessage() // Можно скрыть в продакшене
            //         // ], 500);
            //     }
            // }

        // }
           
        //return ['message' => 'Данные обновлены', 'count' => count($regions)];
        return $data;
    }
}