<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Tourmind\TmApiService;

class SearchOrder
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = $this->tmApiService->getBaseUrl();
    }

    public function getSearchOrder(){

        //$countryCodes = $this->tmApiService->getCountryCodes();

        // foreach ($countryCodes as $countryCode) {
            
            $payload = [
                "AgentRefID" => "21345555",
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
            ])->post("{$this->baseUrl}/SearchOrder", $payload);
    
            if ($response->failed()) {
                return ['error' => 'SearchOrder Ошибка при запросе к API', 'status' => $response->status()];
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
           
        // return ['message' => 'Данные обновлены', 'count' => count($regions)];
        return $data;
    }
}