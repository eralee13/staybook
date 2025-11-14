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
        //dump( $response);

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

    public function downloadAndParse(string $url)
    {
        // ====== Пути ======
        $storageDir = storage_path('app');
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0775, true);
        }

        // Оригинальное имя по URL (может быть .zst или .jsonl)
        $baseName   = basename(parse_url($url, PHP_URL_PATH) ?? ('feed_'.time()));
        $remoteIsZst  = str_ends_with(strtolower($baseName), '.zst');
        $remoteIsJson = str_ends_with(strtolower($baseName), '.jsonl');

        // Файлы во временной папке
        $downloadPath = $storageDir . DIRECTORY_SEPARATOR . $baseName;
        $jsonlPath    = $remoteIsZst
            ? $storageDir . DIRECTORY_SEPARATOR . preg_replace('/\.zst$/i', '.jsonl', $baseName)
            : $downloadPath;

        // Чистим старые артефакты
        @unlink($downloadPath);
        if ($remoteIsZst) { @unlink($jsonlPath); }

        // ====== 1) СКАЧИВАЕМ ======
        $downloaded = false;

        // wget
        if (!$downloaded && trim(shell_exec('which wget 2>/dev/null')) !== '') {
            $cmd = sprintf('wget -q -O %s %s', escapeshellarg($downloadPath), escapeshellarg($url));
            exec($cmd, $out, $rc);
            $downloaded = ($rc === 0 && file_exists($downloadPath) && filesize($downloadPath) > 0);
        }

        // curl
        if (!$downloaded && trim(shell_exec('which curl 2>/dev/null')) !== '') {
            $cmd = sprintf('curl -s -L %s -o %s', escapeshellarg($url), escapeshellarg($downloadPath));
            exec($cmd, $out, $rc);
            $downloaded = ($rc === 0 && file_exists($downloadPath) && filesize($downloadPath) > 0);
        }

        // PHP stream
        if (!$downloaded) {
            $read  = @fopen($url, 'rb');
            $write = @fopen($downloadPath, 'wb');
            if ($read && $write) {
                while (!feof($read)) {
                    $buf = fread($read, 1024 * 1024);
                    if ($buf === false) break;
                    fwrite($write, $buf);
                }
                fclose($read);
                fclose($write);
            }
            $downloaded = (file_exists($downloadPath) && filesize($downloadPath) > 0);
        }

        if (!$downloaded) {
            return response()->json(['error' => 'Не удалось скачать файл'], 500);
        }

        // ====== 2) ПОДГОТОВКА К ЧТЕНИЮ ======
        // Получаем дескриптор построчного чтения JSONL
        $handle = null;
        $needCleanupTempJson = false;

        if ($remoteIsJson) {
            // обычный JSONL — сразу читаем
            $handle = @fopen($jsonlPath, 'r');
        } else if ($remoteIsZst) {
            // если установлен zstd — пробуем распаковать во временный .jsonl
            $hasZstd = (trim(shell_exec('which zstd 2>/dev/null')) !== '');
            if ($hasZstd) {
                // Вариант А: распаковать в файл и читать через fopen (надежнее для больших файлов)
                $cmd = sprintf('zstd -d -f %s -o %s 2>/dev/null',
                    escapeshellarg($downloadPath),
                    escapeshellarg($jsonlPath)
                );
                exec($cmd, $o, $rc);
                if ($rc === 0 && file_exists($jsonlPath) && filesize($jsonlPath) > 0) {
                    $handle = @fopen($jsonlPath, 'r');
                    $needCleanupTempJson = true; // удалим после
                } else {
                    // Вариант Б (fallback): стримить через stdout
                    // ВНИМАНИЕ: Broken pipe появляется, если читатель не успевает.
                    // Здесь PHP читает сразу, поэтому ок.
                    $cmd = sprintf('zstd -d --stdout %s 2>/dev/null', escapeshellarg($downloadPath));
                    $handle = @popen($cmd, 'r');
                }
            } else {
                return response()->json(['error' => 'Утилита zstd не установлена; не могу распаковать .zst'], 500);
            }
        } else {
            // неизвестный формат
            return response()->json(['error' => 'Неизвестный формат файла: ожидается .jsonl или .zst'], 400);
        }

        if (!$handle) {
            return response()->json(['error' => 'Не удалось открыть поток для чтения JSONL'], 500);
        }

        // ====== 3) ЧТЕНИЕ ПОСТРОЧНО (БЕЗ ЖЁСТКОГО ЛИМИТА) ======
        // Если нужно тестово ограничить — поставьте число; для «без лимита» — null.
        $maxLines = null; // например: 1000

        $i = 0;
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;

            $line = trim($line);
            if ($line === '') continue;

            $data = json_decode($line, true);
            if (!is_array($data)) continue;

            // --- Ваши проверки/фильтры на нужные записи ---
            if (!isset($data['kind']) || $data['kind'] !== 'hotel') continue;

            // Примеры извлечения и подготовки полей
            $hid        = $data['hid'] ?? null;
            $name       = $data['name'] ?? '';
            $regionName = $data['region']['name'] ?? '';
            $lat        = $data['latitude'] ?? null;
            $lng        = $data['longitude'] ?? null;

            // Сервис/комнаты/описания (как у тебя было)
            $amenitiesHotel = collect($data['amenity_groups'] ?? [])
                ->firstWhere('group_name', 'Services and amenities')['amenities'] ?? [];
            $amenitiesRoom  = collect($data['amenity_groups'] ?? [])
                ->firstWhere('group_name', 'Rooms')['amenities'] ?? [];
            $descriptionHotel = collect($data['description_struct'] ?? [])
                ->firstWhere('title', 'At the hotel')['paragraphs'] ?? [];
            $descriptionRoom = collect($data['description_struct'] ?? [])
                ->firstWhere('title', 'Room amenities')['paragraphs'] ?? [];

            $amenitiesHotel = collect($amenitiesHotel)->implode(', ');
            $amenitiesRoom  = collect($amenitiesRoom)->implode(', ');
            $descriptionHotel = collect($descriptionHotel)->implode('\n');
            $descriptionRoom  = collect($descriptionRoom)->implode('\n');

            // UTC по стране (как у тебя)
            $EmergingTools = new \App\Http\Controllers\API\V1\Emerging\EmergingTools();
            $utc = $EmergingTools->getUtcOffsetByCountryCode($data['region']['country_code'] ?? '');

            // Сохраняем/обновляем отель
            $hotel = \App\Models\Hotel::updateOrCreate(
                ['emerging_id' => $hid],
                [
                    'code'         => $data['id'] ?? '',
                    'title'        => (string)($name ?? ''),
                    'title_en'     => $name ?? '',
                    'type'         => $data['kind'] ?? '',
                    'rating'       => (int)($data['star_rating'] ?? 0),
                    'address_en'   => $data['address'] ?? '',
                    'city'         => $regionName ?? '',
                    'utc'          => $utc ?? '',
                    'lat'          => $lat,
                    'lng'          => $lng,
                    'checkin'      => $data['check_in_time'] ?? '',
                    'checkout'     => $data['check_out_time'] ?? '',
                    'phone'        => $data['phone'] ?? '',
                    'email'        => $data['email'] ?? '',
                    'description_en'=> $descriptionHotel ?? '',
                    'image'        => '',
                    'status'       => 1,
                    'user_id'      => 1,
                ]
            );

            \App\Models\Amenity::updateOrCreate(
                ['hotel_id' => $hotel->id],
                ['title' => 'Services', 'services' => $amenitiesHotel ?? '']
            );

            \App\Models\Room::updateOrCreate(
                ['hotel_id' => $hotel->id],
                [
                    'title'         => '',
                    'title_en'      => '',
                    'services'      => $amenitiesRoom,
                    'description_en'=> $descriptionRoom ?? ''
                ]
            );

            // Сохранение картинок (как у тебя)
            $images = $data['images_ext'] ?? [];
            $size   = '1024x768';
            $this->saveImagesLink($hotel->id, $images, 20, $size);

            $i++;
            if ($maxLines !== null && $i >= $maxLines) {
                break;
            }
        }

        // Закрываем поток
        if ($remoteIsZst && isset($needCleanupTempJson) && $needCleanupTempJson === true) {
            @fclose($handle);
            // Удалить временный распакованный .jsonl, если не нужен
            // @unlink($jsonlPath); // <- включи, если хочешь чистить
        } else {
            @fclose($handle);
        }

        // Можно удалить исходник после обработки, если не нужен
        // @unlink($downloadPath);

        return response()->json([
            'status'      => 'ok',
            'parsed_rows' => $i,
            'file'        => $baseName,
            'zst'         => $remoteIsZst,
        ]);
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

    public function importFromJsonl()
    {
        $path = storage_path('app/partner_feed_en_v3.jsonl'); // путь к твоему файлу
        if (!file_exists($path)) {
            return response()->json(['error' => 'Файл не найден: '.$path], 404);
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return response()->json(['error' => 'Не удалось открыть файл'], 500);
        }

        $i = 0;
        $imported = 0;

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') continue;

            $data = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) continue;

            // --- фильтруем только объекты hotel ---
            if (($data['kind'] ?? '') !== 'hotel') continue;

            try {
                $amenitiesHotel = collect($data['amenity_groups'] ?? [])
                    ->firstWhere('group_name', 'Services and amenities')['amenities'] ?? [];
                $amenitiesHotel = collect($amenitiesHotel)->implode(', ');

                $descriptionHotel = collect($data['description_struct'] ?? [])
                    ->firstWhere('title', 'At the hotel')['paragraphs'] ?? [];
                $descriptionHotel = collect($descriptionHotel)->implode("\n");

                $utc = app(\App\Http\Controllers\API\V1\Emerging\EmergingTools::class)
                    ->getUtcOffsetByCountryCode($data['region']['country_code'] ?? '');

                // --- сохраняем отель ---
                $hotel = \App\Models\Hotel::updateOrCreate(
                    ['emerging_id' => $data['hid']],
                    [
                        'code'        => $data['id'] ?? '',
                        'title'       => (string) ($data['name'] ?? ''),
                        'title_en'    => $data['name'] ?? '',
                        'type'        => $data['kind'] ?? '',
                        'rating'      => (int) ($data['star_rating'] ?? 0),
                        'address_en'  => $data['address'] ?? '',
                        'city'        => $data['region']['name'] ?? '',
                        'utc'         => $utc ?? '',
                        'lat'         => $data['latitude'] ?? '',
                        'lng'         => $data['longitude'] ?? '',
                        'checkin'     => $data['check_in_time'] ?? '',
                        'checkout'    => $data['check_out_time'] ?? '',
                        'phone'       => $data['phone'] ?? '',
                        'email'       => $data['email'] ?? '',
                        'description_en' => $descriptionHotel ?? '',
                        'image'       => '',
                        'status'      => 1,
                        'user_id'     => 1,
                    ]
                );

                // --- удобства ---
                \App\Models\Amenity::updateOrCreate(
                    ['hotel_id' => $hotel->id],
                    ['title' => 'Services', 'services' => $amenitiesHotel ?? '']
                );

                $imported++;
            } catch (\Throwable $e) {
                \Log::error('Import hotel failed', [
                    'id' => $data['hid'] ?? null,
                    'error' => $e->getMessage()
                ]);
            }

            $i++;
            if ($i % 100 == 0) {
                echo "✅ Imported {$i} rows...\n";
                ob_flush();
                flush();
            }
        }

        fclose($handle);

        return response()->json(['imported' => $imported]);
    }


}

