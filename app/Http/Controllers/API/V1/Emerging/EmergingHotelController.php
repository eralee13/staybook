<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\Image;


class EmergingHotelController extends Controller
{       
    
    public $keyId, $apiKey, $url;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
    }

    public function fetchHotelStatic()
    {

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/info/incremental_dump/', [
                'inventory' => 'top', 
                'language' => 'en',
            ]);

        if ( $response->successful() ) {

            $res = (object) $response->json();
            echo $res->data['url'];
            // dd( (object) $response->json() );
            $this->downloadAndParse($res->data['url']);

        } else {

            $res = response()->json([
                'error' => 'Ошибка запроса',
                'status' => $response->status(),
                'details' => $response->json()
            ], $response->status());

            // dd($res);
        }
    }

    public function downloadAndParse($url)
    {
        // $url = 'https://partner-feedora.s3.eu-central-1.amazonaws.com/feed/partner_feed_en_v3.jsonl.zst';

        // Шаг 1: Скачиваем файл во временное хранилище
        $zstPath = storage_path('app/hotels.jsonl.zst');
        $jsonlPath = storage_path('app/hotels.jsonl');
        $zstdExe = 'D:\OSPanel\tools\zstd\zstd.exe';

        // file_put_contents($zstPath, file_get_contents($url));

        // Шаг 2: Распаковываем .zst → .jsonl
        // Убедись, что утилита zstd установлена на сервере
        
        // 2. Распаковка через exec (Windows)
        // $cmd = "\"{$zstdExe}\" -d -f \"{$zstPath}\" -o \"{$jsonlPath}\"";
        // exec($cmd, $output, $returnCode);

        // if ($returnCode !== 0) {
        //     return response()->json(['error' => 'Не удалось распаковать файл', 'exec_output' => $output], 500);
        // }

        // Шаг 3: Построчное чтение JSONL
        $handle = fopen($jsonlPath, 'r');
        if (!$handle) {
            return response()->json(['error' => 'Не удалось открыть файл'], 500);
        }

        $hotels = [];
        $i = 0;

        while (($line = fgets($handle)) !== false && $i < 2) { // ограничим для примера 10 строками
            $data = json_decode($line, true);
            if ($data) {
                $hotels[] = $data;

                $amenitiesHotel = collect($data['amenity_groups'])
                    ->firstWhere('group_name', 'Services and amenities')['amenities'] ?? [];

                    $amenitiesHotel = collect($amenitiesHotel)->implode(', ');
        
                $amenitiesRoom =  collect($data['amenity_groups'])
                    ->firstWhere('group_name', 'Rooms')['amenities'] ?? [];

                    $amenitiesRoom = collect($amenitiesRoom)->implode(', ');


                        $descriptionHotel = collect($data['description_struct'])
                            ->firstWhere('title', 'At the hotel')['paragraphs'] ?? [];

                            $descriptionHotel = collect($descriptionHotel)->implode('\n');
                        
                        $descriptionRoom = collect($data['description_struct'])
                            ->firstWhere('title', 'Room amenities')['paragraphs'] ?? [];

                            $descriptionRoom = collect($descriptionRoom)->implode('\n');


                    $hotel = Hotel::updateOrCreate(
                        ['emerging_id' => $data['hid']],
                        [
                            'code' => $data['id'] ?? '',
                            'title' => (string)$data['name'] ?? '',
                            'title_en' => $data['name'] ?? '',
                            'type' => $data['kind'] ?? '',
                            'rating' => (int) ($data['star_rating'] ?? 0),
                            'address_en' => $data['address'] ?? '',
                            // 'country_code' => $data['region']['country_code'] ?? '',
                            'city' => $data['region']['name'] ?? '',
                            'lat' => $data['latitude'] ?? '',
                            'lng' => $data['longitude'] ?? '',
                            'checkin' => $data['check_in_time'] ?? '',
                            'checkout' => $data['check_out_time'] ?? '',
                            'phone' => $data['phone'] ?? '',
                            'email' => $data['email'] ?? '',
                            'description_en' => $descriptionHotel ?? '',
                            'image' => '',
                            'emerging_id' => $data['hid'],
                            'status' => 1,
                            'user_id' => 1,
                        ]
                    );
                    
                    // // Обновляем удобства в таблице amenities
                    Amenity::updateOrCreate(
                        ['hotel_id' => $hotel->id],
                        ['title' => 'Services', 'services' => $amenitiesHotel ?? '']
                    );
        
                    $room = Room::updateOrCreate(
                        ['hotel_id' => $hotel->id],
                        [
                            'services' => $amenitiesRoom,
                            // 'image' => $localImagePath,
                            'description_en' => $descriptionRoom ?? ''
                        ]
                    );


                    $images = $data['images'];
                    $size = '1024x768';

                    $this->saveImagesLink($hotel->id, $images, 20, $size);

                    // dd($data);
            }
            $i++;
        }

        fclose($handle);

        echo '<pre>';
        dump($hotels[0]);
        echo '</pre>';
        // return response()->json($hotels);
    }


     public function saveImagesLink($hotelId, $images, $col, $size)
    {
        $i=0;
        collect($images)->take($col)->each(function ($url) use (&$i, $hotelId, $size) {
            $i++;
            $imageUrl = str_replace('{size}', $size, $url);

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

    public function saveHotelImage($imageUrl, int $hotelId)
    {
        try {

            // Получаем имя файла из ссылки
            $fileName = basename(parse_url($imageUrl, PHP_URL_PATH));

            if (!$fileName) {
                throw new \Exception("Не удалось определить имя файла из URL: $imageUrl");
            }

            // Полный путь для сохранения
            $filePath = "/hotels/emerging/{$hotelId}/{$fileName}";

            // Загружаем изображение
            $imageContent = Http::get($imageUrl)->body();

            // Сохраняем файл
            if (!Storage::exists($filePath)) {
                Storage::put($filePath, $imageContent);
            }

            return "/hotels/emerging/{$hotelId}/{$fileName}"; // Путь для хранения в БД

        } catch (\Exception $e) {
            Log::channel('Emerging')->error("Ошибка загрузки изображения saveHotelImage: " . $e->getMessage());
            return null;
        }
    }


}

