<?php

namespace App\Console\Commands;

use App\Services\Tourmind\HotelStaticList;
use Illuminate\Console\Command;

class UpdateHotelStaticList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tm-hotel-static-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление списка отелей из HotelStaticListController';

    /**
     * Execute the console command.
     */
    public function handle(HotelStaticList $service)
    {
        $country = 'UA'; // или возьмите из опций команды
        $service->getHotelList($country);
        $this->info('Импорт завершен');
    }
}
