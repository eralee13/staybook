<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class EmergingFetchHotelDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:emerging-fetch-hotel-dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all hotel static content';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching hotel info dump...');

        $url = config('app.emerging_api_url').'/api/b2b/v3/hotel/info/dump/';
        $keyId = config('app.emerging_key_id');
        $apiKey = config('app.emerging_api_key');

        $response = Http::withBasicAuth($keyId, $apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'inventory' => 'all',
                'language' => 'en',
            ]);

        if ($response->failed()) {
            $this->error("Failed to fetch hotel data: " . $response->body());
            return 1;
        }

        // можно сохранить в файл или базу
        $data = $response->json();

        // Пример: сохраним сырые данные во временный файл
        $filePath = storage_path('app/hotel_dump.json');
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("Hotel dump saved to: $filePath");

        return 0;
    }
}
