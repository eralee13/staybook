<?php

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportCisCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cis-cities';
    protected $description = 'Импортирует города стран СНГ с GeoNames API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = 'timdjol'; // 🔁 замени на свой username с geonames.org
        $countries = ['RU', 'KZ', 'KG', 'UZ', 'BY', 'AM', 'AZ', 'TJ', 'TM', 'MD'];

        foreach ($countries as $country) {
            $this->info("Загружаю города: $country");

            $response = Http::get("http://api.geonames.org/searchJSON", [
                'country' => $country,
                'featureClass' => 'P',
                'maxRows' => 1000,
                'username' => $username,
            ]);

            if ($response->failed()) {
                $this->error("Ошибка загрузки для $country");
                continue;
            }

            $cities = $response->json()['geonames'] ?? [];

            foreach ($cities as $city) {
                City::updateOrCreate([
                    'title' => $city['name'],
                    'country_code' => $country,
                ]);
            }

            $this->info("Импортировано: " . count($cities));
        }

        $this->info('✅ Импорт завершён.');
    }
}
