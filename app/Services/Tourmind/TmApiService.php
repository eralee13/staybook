<?php 

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;

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
