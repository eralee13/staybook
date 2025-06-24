<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Tourmind\TmApiService;
use Illuminate\Support\Str;

class RegionList
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

    public function getRegionList(){

        $countryCodes = $this->tmApiService->getCountryCodes();

        // foreach ($countryCodes as $countryCode) {
            
            $payload = [
                "CountryCode" => 'UA',
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
            ])->post("{$this->baseUrl}/RegionList", $payload);
    
            if ($response->failed()) {
                return ['error' => 'Services RegionList Ошибка при запросе к API', 'status' => $response->status()];
            }

            $data = $response->json();
            $regions = $data['RegionListResult']['Regions'] ?? [];
            
            foreach($regions as $region){

                try {
                    
                    DB::table('cities')->updateOrInsert(
                        ['country_id' => $region['RegionID']], // Условие проверки
                        [
                            'title' => $region['Name'],
                            'code' => Str::slug($region['Name']),
                            'country_id' => (int)$region['RegionID'],
                            'country_code' => (string)$region['CountryCode'],
                        ]
                    );
                    
                } catch (Exception $e) {

                    // Обработка исключения
                    Log::error('Ошибка Services Region List: ' . $e->getMessage(), ['exception' => $e]);

                }
            }

        // }
           
        return ['message' => 'Данные обновлены', 'count' => count($regions)];
    }
}