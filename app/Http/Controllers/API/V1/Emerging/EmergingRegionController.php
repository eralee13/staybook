<?php

namespace App\Http\Controllers\Api\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmergingRegionController extends Controller
{
    
    public $keyId, $apiKey, $url;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
    }

    public function fetchRegionStatic()
    {

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/region/dump/', [
                'inventory' => 'top',   // можно указать 'current' для актуального
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

            dd($res);
        }
    }
    
    public function downloadAndParseRegion($url)
    {
        // $url = 'https://partner-feedora.s3.eu-central-1.amazonaws.com/feed/partner_feed_en_v3.jsonl.zst';

        // Шаг 1: Скачиваем файл во временное хранилище
        $zstPath = storage_path('app/hotels.jsonl.zst');
        $jsonlPath = storage_path('app/regions.jsonl');
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

        $regions = [];
        $i = 0;

        while (($line = fgets($handle)) !== false && $i < 20) { // ограничим для примера 10 строками
            $data = json_decode($line, true);
            if ($data) {
                $regions[] = [
                    'id' => $data['id'],
                    'country_name' => $data['country_name']['en'],
                    'country_code' => $data['country_code'],
                    'center' => $data['center'],
                    'hids' => $data['hids'],
                    'hotels' => $data['hotels'],
                    'type' => $data['type'],
                    'type' => $data['type'],
                ];
            }
            $i++;
        }
        
        fclose($handle);

        echo '<pre>';
        dd($regions);
        echo '</pre>';
        // return response()->json($regions);
    }

}
