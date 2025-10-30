<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class EmergingRegionStaticController extends Controller
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
                'inventory' => 'all',   // можно указать 'current' для актуального
            ]);

        if ( $response->successful() ) {
            set_time_limit(0);
            $res = (object) $response->json();
            echo $res->data['url'];
            // dd( (object) $response->json() );
            $this->downloadAndParseRegion($res->data['url']);

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
        $zstPath = storage_path('app/region.jsonl.zst');
        // $jsonlPath = storage_path('app/regions.jsonl');
       
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

         // Открываем поток на чтение через zstd (он распаковывает "на лету")
        $handle = popen("zstd -d --stdout " . escapeshellarg($zstPath), 'r');
        if (!$handle) {
            return response()->json(['error' => 'Не удалось открыть файл'], 500);
        }

        $regions = [];
        $i = 0;

        while (($line = fgets($handle)) !== false) { // ограничим для примера 10 строками

            $data = json_decode($line, true);
            $regions = $data;
            
                if ( isset($data['country_name']['en']) ) {

                    if ( $data['type'] == 'city' ) {

                        DB::table('cities')->updateOrInsert(
                        // City::firstOrCreate(
                                [
                                    'emerging_id' => (int)$data['id'],
                                ],
                                [
                                    'title' => $data['name']['en'] ?? '',
                                    'code' => strtolower($data['country_name']['en']),
                                    'name' => (string)$data['name']['en'],
                                    'country_code' => (string)$data['country_code'],
                                ]
                        );

                    }elseif ($data['type'] == 'country') {
                        
                        DB::table('countries')->updateOrInsert(
                        // Country::firstOrCreate(
                                [
                                    'emerging_id' => (int)$data['id'],
                                ],
                                [
                                    'name' => $data['country_name']['en'] ?? '',
                                    // 'code' => strtolower($data['country_name']['en']),
                                    // 'name' => (string)$data['name']['en'],
                                    'alpha2' => (string)$data['country_code'],
                                ]
                        );
                    }
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
