<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;

class HotelStaticList
{
    protected $baseUrl = 'http://39.108.114.224:7080/v2';

    public function getHotelList()
    {
        $payload = [
            "CountryCode" => "UA",
            "Pagination" => [
                "PageIndex" => 1,
                "PageSize" => 2
            ],
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
        ])->post("{$this->baseUrl}/HotelStaticList", $payload);

        if ($response->failed()) {
            return ['error' => 'Ошибка при запросе к API', 'status' => $response->status()];
        }

        return $response->json();
    }
}
