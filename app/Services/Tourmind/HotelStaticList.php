<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\Room;


class HotelStaticList
{
    
    protected TmApiService $tmApiService;
    protected string $baseUrl;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = $this->tmApiService->getBaseUrl();
    }
    
    public function getHotelListForAllCountries()
    {
        // Получаем список стран (можно задать вручную или запросить API)
        $countryCodes = $this->tmApiService->getCountryCodes();

        foreach ($countryCodes as $countryCode) {
            $this->getHotelList($countryCode);
        }
    }

    public function getHotelList($countryCode)
    {
        $pageIndex = 1; // Начинаем с первой страницы
        $pageSize = 100; // Количество отелей на страницу
    
        //do {
            $payload = [
                "CountryCode" => 'CN',
                "Pagination" => [
                    "PageIndex" => $pageIndex,
                    "PageSize" => $pageSize
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
    
            $data = $response->json();
            $hotels = $data['HotelStaticListResult']['Hotels'] ?? [];
            $pageCount = $data['HotelStaticListResult']['Pagination']['PageCount'] ?? 1;
    
            foreach ($hotels as $hotelData) {
                $AmenitiesHotel = collect($hotelData['AmenitiesHotel'] ?? [])->pluck('name')
                    ->unique()
                    ->implode(', ');
    
                $AmenitiesRoom = collect($hotelData['AmenitiesRoom'] ?? [])->pluck('name')
                    ->unique()
                    ->implode(', ');
    
                // Получаем первую картинку
                $imageUrl = collect($hotelData['Images'] ?? [])->pluck('links.1000px.href')->filter()->first();
    
                // $localImagePath = null;
                // if ($imageUrl) {
                //     $localImagePath = $this->saveHotelImage($imageUrl, $hotelData['HotelId']);
                // }
    
                $nameLower = str_replace(' ', '-', $hotelData['Name']);

                $phone = preg_replace('/[^+\d]/', '', $hotelData['Phone']);

                if (!Str::startsWith($phone, '+')) {
                    $phone = '+' . $phone;
                }
                
                $data = [
                    'code' => strtolower($nameLower) ?? '',
                    'title' => (string)$hotelData['Name'] ?? '',
                    'title_en' => $hotelData['Name'] ?? '',
                    'rating' => (int) ($hotelData['StarRating'] ?? 0),
                    'address_en' => $hotelData['Address'] ?? null,
                    'country_code' => $hotelData['CountryCode'] ?? null,
                    'city' => $hotelData['CityName'] ?? null,
                    'lat' => $hotelData['Latitude'] ?? null,
                    'lng' => $hotelData['Longitude'] ?? null,
                    'phone' => $phone ?? null,
                    'description_en' => $hotelData['Description']['Location'] ?? null,
                    'image' => $imageUrl ?? null,
                    'tourmind_id' => $hotelData['HotelId'],
                    'status' => 1,
                ];
                
    
                $hotel = Hotel::updateOrCreate(
                    ['tourmind_id' => $hotelData['HotelId']],
                    $data
                );
    
                // Обновляем удобства в таблице amenities
                Amenity::updateOrCreate(
                    ['hotel_id' => $hotel->id],
                    ['services' => $AmenitiesHotel]
                );
    
                Room::updateOrCreate(
                    ['hotel_id' => $hotel->id],
                    [
                        'services' => $AmenitiesRoom,
                        'image' => $imageUrl,
                        'description_en' => $hotelData['Description']['Rooms'] ?? null
                    ]
                );
    
                // Сохраняем до 10 изображений
                if (!empty($hotelData['Images'])) {
                    $this->tmApiService->saveImagesLink($hotel->id, $hotelData['Images'], 10);
                }
            }
    
            $pageIndex++; // Переход на следующую страницу
    
        //} while ($pageIndex <= $pageCount); // Пока не загрузим все страницы
    
        return ['message' => 'Данные обновлены', 'count' => count($hotels)];
    }
    

}
