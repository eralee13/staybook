<?php 

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Image;

class TmApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.tm_base_url');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getCountryCodes()
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

    public function saveImagesLink($hotelId, $images, $col)
    {
        $i=0;
        collect($images)->take($col)->each(function ($img) use ($hotelId) {
            $i++;
            $imageUrl = $img['links']['1000px']['href'] ?? null;

            if ($i > 1 && !empty($imageUrl)) {
                    // Сохраняем изображение локально
                    $localImagePath = $this->saveHotelImage($imageUrl, $hotelId);

                    Image::create([
                        'hotel_id' => $hotelId,
                        'image' => $localImagePath
                    ]);
                
            }
        });
    }
    
    public function saveRoomImages(int $hotelId, $images, int $roomId)
    {
        // Log::channel('tourmind')->info("saveRoomImages: hotel_id={$hotelId}, room_id={$roomId}");
    
        // Фильтруем только изображения с caption = 'Room'
        $roomImages = collect($images)->filter(function ($img) {
            return isset($img['caption']) && strtolower($img['caption']) === 'room';
        });
    
        if ($roomImages->isEmpty()) {
            Log::channel('tourmind')->warning("Нет изображений с caption='Room' для отеля ID: $hotelId, room ID: $roomId");
            return;
        }
    
        // Ограничиваем, сколько сохранить — например, до 5
        $roomImages->take(5)->each(function ($img) use ($hotelId, $roomId) {
            $imageUrl = $img['links']['1000px']['href'] ?? null;
            $category = $img['category'] ?? null;
    
            if ($imageUrl) {
                $localImagePath = $this->saveRoomImage($imageUrl, $hotelId);
    
                if ($localImagePath) {
                    Image::create([
                        'hotel_id' => $hotelId,
                        'room_id' => $roomId,
                        'category' => $category,
                        'caption' => $img['caption'],
                        'image' => $localImagePath
                    ]);
                }
            }
        });
    }
    



    public function saveRoomImage($imageUrl, int $hotelId)
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
            $filePath = "/rooms/tourmind/{$hotelId}/{$fileName}";

            // Загружаем изображение
            $imageContent = Http::get($imageUrl)->body();

            // Сохраняем файл
            if (!Storage::exists($filePath)) {
                Storage::put($filePath, $imageContent);
            }

            return "/rooms/tourmind/{$hotelId}/{$fileName}"; // Путь для хранения в БД
        } catch (\Exception $e) {
            Log::channel('tourmind')->error("Ошибка загрузки изображения saveRoomImage: " . $e->getMessage());
            return null;
        }
    }

    public function saveHotelImage($imageUrl, int $hotelId)
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
            $filePath = "/hotels/tourmind/{$hotelId}/{$fileName}";

            // Загружаем изображение
            $imageContent = Http::get($imageUrl)->body();

            // Сохраняем файл
            if (!Storage::exists($filePath)) {
                Storage::put($filePath, $imageContent);
            }

            return "/hotels/tourmind/{$hotelId}/{$fileName}"; // Путь для хранения в БД

        } catch (\Exception $e) {
            Log::channel('tourmind')->error("Ошибка загрузки изображения saveHotelImage: " . $e->getMessage());
            return null;
        }
    }


}
