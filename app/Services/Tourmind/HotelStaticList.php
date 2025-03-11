<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\Room;


class HotelStaticList
{
    protected $baseUrl = 'http://39.108.114.224:7080/v2';

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
            $filePath = "public/hotels/{$datePath}/{$fileName}";

            // Загружаем изображение
            $imageContent = Http::get($imageUrl)->body();

            // Сохраняем файл
            Storage::put($filePath, $imageContent);

            return "storage/hotels/{$datePath}/{$fileName}"; // Путь для хранения в БД
        } catch (\Exception $e) {
            \Log::error("Ошибка загрузки изображения: " . $e->getMessage());
            return null;
        }
    }

    public function getHotelList()
    {
        $payload = [
            "CountryCode" => "UA",
            "Pagination" => [
                "PageIndex" => 1,
                "PageSize" => 1
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
                
        foreach ($hotels as $hotelData) {

            $AmenitiesHotel = collect($hotelData['AmenitiesHotel'] ?? [])->pluck('name')
                ->unique() // Убираем дубли
                ->implode(', ');

            $AmenitiesRoom = collect($hotelData['AmenitiesRoom'] ?? [])->pluck('name')
                ->unique() // Убираем дубли
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
                'tourmind_id' => $hotelData['HotelId'] ?? null,
                'status' => 1,
            ];
            
            // Исключаем поле, которое не нужно заполнять
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
            ]); // Пример исключения поля 'title'
            
            $hotel = Hotel::updateOrCreate(
                ['tourmind_id' => $hotelData['HotelId']],
                $data
            );

            // Обновляем удобства в таблице amenities
            Amenity::updateOrCreate(
                ['title' => 'Услуги'],
                ['hotel_id' => $hotel->id],
                ['services' => $AmenitiesHotel]
            );

            Room::updateOrCreate(
                ['hotel_id' => $hotel->id],
                ['services' => $AmenitiesRoom],
                ['image' => $localImagePath]
            );
        }
        

        return ['message' => 'Данные обновлены', 'count' => count($hotels)];
    }
}
