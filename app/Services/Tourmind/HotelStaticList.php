<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\Image;


class HotelStaticList
{
    protected $baseUrl = 'http://39.108.114.224:7080/v2';

    public function getHotelListForAllCountries()
    {
        // Получаем список стран (можно задать вручную или запросить API)
        $countryCodes = $this->getCountryCodes();

        foreach ($countryCodes as $countryCode) {
            $this->getHotelList($countryCode);
        }
    }

    public function getHotelList($countryCode)
    {
        $pageIndex = 1; // Начинаем с первой страницы
        $pageSize = 100; // Количество отелей на страницу
    
        do {
            $payload = [
                "CountryCode" => $countryCode,
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
    
                $localImagePath = null;
                if ($imageUrl) {
                    $localImagePath = $this->saveHotelImage($imageUrl, $hotelData['HotelId']);
                }
    
                $nameLower = str_replace(' ', '-', $hotelData['Name']);
                
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
                    'phone' => $hotelData['Phone'] ?? null,
                    'description_en' => $hotelData['Description']['Location'] ?? null,
                    'image' => $localImagePath ?? null,
                    'tourmind_id' => $hotelData['HotelId'],
                    'status' => 1,
                ];
    
                $data = array_diff_key($data, [
                    'description',
                    'checkin',
                    'checkout',
                    'email',
                    'count',
                    'type',
                    'address_en',
                    'early_in',
                    'early_out',
                    'top',
                    'user_id',
                ]);
    
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
                        'image' => $localImagePath,
                        'description_en' => $hotelData['Description']['Rooms'] ?? null
                    ]
                );
    
                // Сохраняем до 10 изображений
                if (!empty($hotelData['Images'])) {
                    $this->saveHotelImages($hotel->id, $hotelData['Images']);
                }
            }
    
            $pageIndex++; // Переход на следующую страницу
    
        } while ($pageIndex <= $pageCount); // Пока не загрузим все страницы
    
        return ['message' => 'Данные обновлены', 'count' => count($hotels)];
    }
    
    private function getCountryCodes()
    {
        return [
            "AF", "AL", "DZ", "AD", "AO", "AG", "AR", "AM", "AU", "AT", "AZ",
            "BS", "BH", "BD", "BB", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA",
            "BW", "BR", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF",
            "TD", "CL", "CN", "CO", "KM", "CG", "CD", "CR", "CI", "HR", "CU",
            "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "GQ", "ER",
            "EE", "SZ", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH",
            "GR", "GD", "GT", "GN", "GW", "GY", "HT", "HN", "HU", "IS", "IN",
            "ID", "IR", "IQ", "IE", "IL", "IT", "JM", "JP", "JO", "KZ", "KE",
            "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LR", "LY", "LI", "LT",
            "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MH", "MR", "MU", "MX",
            "FM", "MD", "MC", "MN", "ME", "MA", "MZ", "MM", "NA", "NR", "NP",
            "NL", "NZ", "NI", "NE", "NG", "KP", "NO", "OM", "PK", "PW", "PA",
            "PG", "PY", "PE", "PH", "PL", "PT", "QA", "RO", "RU", "RW", "KN",
            "LC", "VC", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG",
            "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SD", "SR", "SE",
            "CH", "SY", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR",
            "TM", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VA", "VE",
            "VN", "YE", "ZM", "ZW"
        ];
    }

    private function saveHotelImages($hotelId, $images)
    {
        collect($images)->take(10)->each(function ($img) use ($hotelId) {
            $imageUrl = $img['links']['1000px']['href'] ?? null;

            if ($imageUrl) {
                $localImagePath = $this->saveHotelImage($imageUrl);

                if ($localImagePath) {
                    Image::create([
                        'hotel_id' => $hotelId,
                        'image' => $localImagePath
                    ]);
                }
            }
        });
    }

    private function saveHotelImage($imageUrl)
    {
        try {
            // Получаем имя файла из ссылки
            $fileName = basename(parse_url($imageUrl, PHP_URL_PATH));

            if (!$fileName) {
                throw new \Exception("Не удалось определить имя файла из URL: $imageUrl");
            }

            // Определяем дату (год/месяц)
            $datePath = now()->format('Y/m');

            // Полный путь для сохранения
            $filePath = "/hotels/{$datePath}/{$fileName}";

            // Загружаем изображение
            $imageContent = Http::get($imageUrl)->body();

            // Сохраняем файл
            Storage::put($filePath, $imageContent);

            return "/hotels/{$datePath}/{$fileName}"; // Путь для хранения в БД
        } catch (\Exception $e) {
            \Log::error("Ошибка загрузки изображения: " . $e->getMessage());
            return null;
        }
    }

}
