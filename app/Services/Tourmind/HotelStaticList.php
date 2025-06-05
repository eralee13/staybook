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
use App\Models\Image;
use GeoIp2\Database\Reader;


class HotelStaticList
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
        $pageCount = 1;
        $hotel = [];
        $room = [];
        // do {
            $payload = [
                "CountryCode" => 'UA',
                "Pagination" => [
                    "PageIndex" => $pageIndex,
                    "PageSize" => $pageSize
                ],
                "RequestHeader" => [
                    "AgentCode" => $this->tm_agent_code,
                    "Password" => $this->tm_password,
                    "UserName" => $this->tm_user_name,
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ]
            ];
            
            try {

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->timeout(60)->post("{$this->baseUrl}/HotelStaticList", $payload);
        
                if ($response->failed()) {
                    return ['error' => 'Ошибка при запросе к API', 'status' => $response->status()];
                }
        
                $data = $response->json();
                $hotels = $data['HotelStaticListResult']['Hotels'] ?? [];
                $pageCount = $data['HotelStaticListResult']['Pagination']['PageCount'] ?? 1;
                
                // Log::channel('tourmind')->info('Hotel Static List - ', $data);

                
                foreach ($hotels as $hotelData) {
                    $AmenitiesHotel = collect($hotelData['AmenitiesHotel'] ?? [])->pluck('name')
                        ->unique()
                        ->implode(', ');
        
                    $AmenitiesRoom = collect($hotelData['AmenitiesRoom'] ?? [])->pluck('name')
                        ->unique()
                        ->implode(', ');
        
                    // Получаем первую картинку
                    $imageUrl = collect($hotelData['Images'] ?? [])->pluck('links.1000px.href')->filter()->first();
                    $imageUrl2 = collect($hotelData['Images'] ?? [])->pluck('links.1000px.href')->filter()->values()->get(1);
                    $imageUrl3 = collect($hotelData['Images'] ?? [])->pluck('links.1000px.href')->filter()->values()->get(2);
                    $category = collect($hotelData['Images'] ?? [])->pluck('category')->filter()->first();
                    $category2 = collect($hotelData['Images'] ?? [])->pluck('category')->filter()->values()->get(1);
                    $category3 = collect($hotelData['Images'] ?? [])->pluck('category')->filter()->values()->get(2);
        
        
                        $nameLower = str_replace(' ', '-', $hotelData['Name']);

                        if ( isset($hotelData['Phone']) ) {
                            $phone = preg_replace('/[^+\d]/', '', $hotelData['Phone']);

                            if (!Str::startsWith($phone, '+')) {
                                $phone = '+' . $phone;
                            }
                        }else{
                            $phone = '';
                        }
                    

                    $hotelService = new HotelServices();
                    $utc = $hotelService->getUtcOffsetByCountryCode($hotelData['CountryCode']);
                    
                    $hotelDataInsert = [
                        'code' => strtolower($nameLower) ?? '',
                        'title' => (string)$hotelData['Name'] ?? '',
                        'title_en' => $hotelData['Name'] ?? '',
                        'rating' => (int) ($hotelData['StarRating'] ?? 0),
                        'address_en' => $hotelData['Address'] ?? '',
                        'country_code' => $hotelData['CountryCode'] ?? '',
                        'city' => $hotelData['CityName'] ?? '',
                        'utc' => $utc ?? '',
                        'lat' => $hotelData['Latitude'] ?? '',
                        'lng' => $hotelData['Longitude'] ?? '',
                        'phone' => $phone ?? '',
                        'description_en' => $hotelData['Description']['Location'] ?? '',
                        'image' => '',
                        'tourmind_id' => $hotelData['HotelId'],
                        'status' => 1,
                    ];
                    
        
                    $hotel = Hotel::updateOrCreate(
                        ['tourmind_id' => $hotelData['HotelId']],
                        $hotelDataInsert
                    );
                    
                        if ($hotel) {

                            // Обновляем удобства в таблице amenities
                            Amenity::updateOrCreate(
                                ['hotel_id' => $hotel->id],
                                ['services' => $AmenitiesHotel]
                            );
                    
                                $room = Room::updateOrCreate(
                                    ['hotel_id' => $hotel->id],
                                    [
                                        'title' => '',
                                        'amenities' => $AmenitiesRoom,
                                        // 'image' => $localImagePath,
                                        'description_en' => $hotelData['Description']['Rooms'] ?? null
                                    ]
                                );
                        
                    
                            // return $hotel;
                            // die;

                            // Сохраняем изображение отеля
                            if ($imageUrl) {
                                $localImagePath = $this->tmApiService->saveHotelImage($imageUrl, $hotel->id);

                                $image = Image::updateOrCreate(
                                    [
                                        'hotel_id' => $hotel->id,
                                        'category' => $category,
                                    ],
                                    [
                                        'image' => $localImagePath ?? '',
                                        'caption' => 'Primary',
                                    ]
                                );
                            
                            }
                            if ($imageUrl2) {
                                $localImagePath2 = $this->tmApiService->saveHotelImage($imageUrl2, $hotel->id);

                                $image = Image::updateOrCreate(
                                    [
                                        'hotel_id' => $hotel->id,
                                        'category' => $category2,
                                    ],
                                    [
                                        'image' => $localImagePath2 ?? '',
                                        'caption' => 'Reception',
                                    ]
                                );
                                
                            }
                            if ($imageUrl3) {
                                $localImagePath3 = $this->tmApiService->saveHotelImage($imageUrl3, $hotel->id);

                                $image = Image::updateOrCreate(
                                    [
                                        'hotel_id' => $hotel->id,
                                        'category' => $category3,
                                    ],
                                    [
                                        'image' => $localImagePath3 ?? '',
                                        'caption' => 'Reception',
                                    ]
                                );
                                
                            }

                            // Сохраняем  изображений номеров
                            if ( isset($hotelData['Images'])  && is_array($hotelData['Images']) ) {
                                Log::channel('tourmind')->error('Hotel static list Сохраняем  изображений передача roomid '.$room->id);
                                $this->tmApiService->saveRoomImages($hotel->id,  $hotelData['Images'], $room->id, $col = 9);
                            }
                        }
                    
                }

            } catch (\Throwable $th) {
                Log::channel('tourmind')->error('Hotel Static List - Ошибка при получении данных - ' . $th->getMessage());
            }
    
        //     $pageIndex++; // Переход на следующую страницу
    
        // } while ($pageIndex <= $pageCount); // Пока не загрузим все страницы
    
        //return ['message' => 'Данные обновлены', 'count' => count($hotels)];
            return $room;
    }

}
