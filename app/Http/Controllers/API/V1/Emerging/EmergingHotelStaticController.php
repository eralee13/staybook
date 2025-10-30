<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\Image;

class EmergingHotelStaticController extends Controller
{       
    
    protected $keyId, $apiKey, $url;

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
            ->post($this->url . '/hotel/info/dump/', [
                'inventory' => 'all', 
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

        // Пути под Linux
        $zstPath = storage_path('app/partner_feed_en_v3.jsonl.zst');
        $jsonlPath = storage_path('app/partner_hotels_en.jsonl');

        // Удаляем старые файлы, если есть
        @unlink($zstPath);
        //@unlink($jsonlPath);
        
        // ==============================
        // 1. СКАЧИВАНИЕ ФАЙЛА
        // ==============================
        
        $downloaded = false;
    
        // Попробовать wget
        if (shell_exec('which wget')) {
            $cmd = "wget -q -O \"$zstPath\" \"$url\"";
            exec($cmd, $output, $returnCode);
            $downloaded = ($returnCode === 0);
        }
    
        // Если wget недоступен — пробуем curl
        if (!$downloaded && shell_exec('which curl')) {
            $cmd = "curl -s -L \"$url\" -o \"$zstPath\"";
            exec($cmd, $output, $returnCode);
            $downloaded = ($returnCode === 0);
        }
    
        // Если нет ни wget, ни curl — fallback на PHP-поток
        if (!$downloaded) {
            $read = fopen($url, 'rb');
            $write = fopen($zstPath, 'wb');
            if ($read && $write) {
                while (!feof($read)) {
                    fwrite($write, fread($read, 8192));
                }
                fclose($read);
                fclose($write);
                $downloaded = true;
            }
        }
    
        if (!$downloaded || !file_exists($zstPath)) {
            return response()->json(['error' => 'Не удалось скачать файл'], 500);
        }
    
        // ==============================
        // 2. РАСПАКОВКА .zst → .jsonl
        // ==============================
        /*if (!shell_exec('which zstd')) {
            return response()->json(['error' => 'Утилита zstd не установлена на сервере'], 500);
        }*/
        
        
       /* $cmd = "zstd -d -f \"$zstPath\" -o \"$jsonlPath\"";
        exec($cmd, $output, $returnCode);*/

        // if ($returnCode !== 0) {
        //     return response()->json(['error' => 'Не удалось распаковать файл', 'exec_output' => $output], 500);
        // }

        // Шаг 3: Построчное чтение JSONL
        //$handle = fopen($jsonlPath, 'r');
        
        // Открываем поток на чтение через zstd (он распаковывает "на лету")
        $handle = popen("zstd -d --stdout " . escapeshellarg($zstPath), 'r');
        if (!$handle) {
            return response()->json(['error' => 'Не удалось открыть файл'], 500);
        }

        $hotels = [];
        $i = 0;

        while (($line = fgets($handle)) !== false && $i < 1) { // ограничим для примера 10 строками
            
            //dump($line);
            
            $data = json_decode($line, true);
            
    
            if (
                isset($data['region']['name'], $data['kind'])
               // && $data['region']['name'] == 'China' && $data['kind'] == 'hotel'
                ) {
                //if ($data['hid'] == 8473727) {

                // file_put_contents(storage_path('app\testov.jsonl'), json_encode($data, JSON_PRETTY_PRINT));

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

                            $EmergingTools = new EmergingTools();
                            $utc = $EmergingTools->getUtcOffsetByCountryCode($data['region']['country_code']);
                            

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
                            'utc' => $utc ?? '',
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
                        [
                            'title' => 'Services', 
                            'services' => $data['amenity_groups'] ? json_encode($data['amenity_groups']) : ''
                        ]
                    );
        
                    $room = Room::updateOrCreate(
                        ['hotel_id' => $hotel->id],
                        [
                            'title' => '',
                            'title_en' => '',
                            'services' => $amenitiesRoom,
                            // 'image' => $localImagePath,
                            'description_en' => $descriptionRoom ?? ''
                        ]
                    );


                    $images = $data['images_ext'];
                    $size = '1024x768';

                    $this->saveImagesLink($hotel->id, $images, 20, $size);

                    // Импорт типы комнат
                    if( !empty($data['room_groups']) ){

                        foreach( $data['room_groups'] as $roomGroup ){

                            $roomType = Room::updateOrCreate(
                                [
                                    'hotel_id' => $hotel->id,
                                    'emerging_id' => $roomGroup['room_group_id']
                                ],
                                [
                                    'title' => $roomGroup['name'] ?? '',
                                    'title_en' => $roomGroup['name'] ?? '',
                                    'amenities' => $roomGroup['room_amenities'] ? collect($roomGroup['room_amenities'])->implode(', ') : '',
                                    'rg_ext' => json_encode($roomGroup['rg_ext'] ?? []),
                                ]
                            );

                            // Сохраняем изображения комнат
                            if( !empty($roomGroup['images']) ){
                                $this->saveRoomImagesLink($hotel->id, $roomType->id, $roomGroup, 30, $size);
                            }

                        }
                    }
                    

                    echo "\n № {$i} Hotel: ".$data['name']. ' - ID '.$data['hid'];
            }
            $i++;
        }

        fclose($handle);

        // return response()->json($hotels);
    }


    public function saveImagesLink($hotelId, $images, $col, $size)
    {
        $i=0;
        collect($images)->take($col)->each(function ($url) use (&$i, $hotelId, $size) {
            $i++;
            $imageUrl = str_replace('{size}', $size, $url['url']);

            if ($i > 1 && !empty($imageUrl)) {

                    // Сохраняем изображение локально
                    $localImagePath = $this->saveHotelImage($imageUrl, $hotelId);

                    Image::updateOrCreate(
                        [
                            'hotel_id' => $hotelId,
                            'image' => $localImagePath,
                            'caption' => $url['category_slug'],
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
            $filePath = "hotels/emerging/{$hotelId}/{$fileName}";

            // Загружаем изображение
            $imageContent = Http::get($imageUrl)->body();

            // Сохраняем файл
            if (!Storage::exists($filePath)) {
                Storage::put($filePath, $imageContent);
            }

            return "hotels/emerging/{$hotelId}/{$fileName}"; // Путь для хранения в БД

        } catch (\Exception $e) {
            Log::channel('Emerging')->error("Ошибка загрузки изображения saveHotelImage: " . $e->getMessage());
            return null;
        }
    }
    
    public function saveRoomImagesLink($hotelId, $roomId, $roomGroup, $col, $size)
    {
        if (empty($roomGroup['images'])) {
            return;
        }

        $roomName = $roomGroup['name'] ?? '';
        
        collect($roomGroup['images'])
            ->take($col)
                ->each(function ($img, $index) use (&$i, $hotelId, $roomId, $roomName, $size) {
           
            $imageUrl = str_replace('{size}', $size, $img);

            if (!empty($imageUrl)) {

                    // Сохраняем изображение локально
                    $localImagePath = $this->saveRoomImage($imageUrl, $roomId);

                    Image::updateOrCreate(
                        [
                            'hotel_id' => $hotelId,
                            'room_id' => $roomId,
                            'caption' => $roomName,
                            'category' => $index + 1,
                        ],
                        [
                            'image' => $localImagePath,
                        ]
                    );
                
            }
        });
    }

    public function saveRoomImage($imageUrl, int $roomId)
    {
        try {

            // Получаем имя файла из ссылки
            $fileName = basename(parse_url($imageUrl, PHP_URL_PATH));

            if (!$fileName) {
                throw new \Exception("Не удалось определить имя файла из URL: $imageUrl");
            }

            // Полный путь для сохранения
            $filePath = "hotels/emerging/rooms/{$roomId}/{$fileName}";

            // Загружаем изображение
            $imageContent = Http::get($imageUrl)->body();

            // Сохраняем файл
            if (!Storage::exists($filePath)) {
                Storage::put($filePath, $imageContent);
            }

            return "hotels/emerging/rooms/{$roomId}/{$fileName}"; // Путь для хранения в БД

        } catch (\Exception $e) {
            Log::channel('Emerging')->error("Ошибка загрузки изображения saveRoomImage: " . $e->getMessage());
            return null;
        }
    }


}

